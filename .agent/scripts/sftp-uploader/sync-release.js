const Client = require('ssh2-sftp-client');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

function sha256(data) {
  if (Array.isArray(data)) {
    data = Buffer.from(data);
  } else if (typeof data === 'string') {
    data = Buffer.from(data, 'utf8');
  } else if (!Buffer.isBuffer(data)) {
    data = Buffer.from([]);
  }
  return crypto.createHash('sha256').update(data).digest('hex');
}

async function syncRelease() {
  const projectRoot = path.resolve(__dirname, '../../../');
  const sftpJsonPath = path.join(projectRoot, '.vscode', 'sftp.json');

  if (!fs.existsSync(sftpJsonPath)) {
    console.error('ERROR: .vscode/sftp.json not found');
    process.exit(1);
  }

  const sftpConfig = JSON.parse(fs.readFileSync(sftpJsonPath, 'utf8'));
  
  if (sftpConfig.username !== 'redil2024' || !sftpConfig.remotePath.includes('/home/redil2024/public_html')) {
    console.error('ERROR: Unauthorized destination profile or path:', sftpConfig.username, sftpConfig.remotePath);
    process.exit(1);
  }

  const config = {
    host: sftpConfig.host,
    port: sftpConfig.port || 22,
    username: sftpConfig.username,
    password: sftpConfig.password,
    readyTimeout: 30000,
    retries: 2
  };

  const remotePathBase = sftpConfig.remotePath; // /home/redil2024/public_html
  const releaseId = 'restaurar-auth-views-' + new Date().toISOString().replace(/[-:T.]/g, '').slice(0, 14);
  const backupBaseDir = `/home/redil2024/redil-releases/${releaseId}`;

  // Archivos elegibles de la tarea
  const files = [
    'resources/views/contenido/authentications/forgot-password.blade.php',
    'resources/views/contenido/authentications/reset-password.blade.php',
    'resources/views/contenido/authentications/reactivar-solicitud.blade.php',
    'resources/views/contenido/authentications/auth-login-basic.blade.php',
    'resources/views/contenido/authentications/auth-register-basic.blade.php'
  ];

  console.log(`Iniciando sincronización asistida SFTP (${files.length} archivos)...`);
  console.log(`Destino remoto: ${remotePathBase}`);
  console.log(`Directorio de respaldo remoto: ${backupBaseDir}`);

  const sftp = new Client();
  const manifest = {
    releaseId,
    timestamp: new Date().toISOString(),
    files: []
  };

  try {
    await sftp.connect(config);
    console.log('Conexión SSH/SFTP establecida.');

    // Crear directorio de respaldo remoto
    await sftp.mkdir(backupBaseDir, true);

    for (const relPath of files) {
      const localPath = path.join(projectRoot, relPath);
      if (!fs.existsSync(localPath)) {
        throw new Error(`Archivo local no encontrado: ${localPath}`);
      }

      const localBuffer = fs.readFileSync(localPath);
      const localHash = sha256(localBuffer);
      const remotePath = path.posix.join(remotePathBase, relPath.split(path.sep).join('/'));
      const remoteDir = path.posix.dirname(remotePath);

      console.log(`\nProcesando: ${relPath}`);

      // 1. Verificar si existe en el servidor remoto
      const remoteExists = await sftp.exists(remotePath);
      let previousHash = null;

      if (remoteExists) {
        let remoteBuffer = await sftp.get(remotePath);
        if (!Buffer.isBuffer(remoteBuffer)) {
          remoteBuffer = Buffer.from(remoteBuffer || []);
        }
        previousHash = sha256(remoteBuffer);

        const backupFilePath = path.posix.join(backupBaseDir, relPath.split(path.sep).join('/'));
        await sftp.mkdir(path.posix.dirname(backupFilePath), true);
        await sftp.put(remoteBuffer, backupFilePath);
        console.log(`  -> Respaldo remoto creado (SHA-256 previo: ${previousHash.slice(0, 12)}...)`);
      } else {
        console.log(`  -> Archivo nuevo en remoto.`);
      }

      // 2. Asegurar que existe el directorio de destino
      await sftp.mkdir(remoteDir, true);

      // 3. Subir a temporal y reemplazar atómicamente
      const tmpPath = remotePath + '.tmp.' + Date.now();
      await sftp.put(localBuffer, tmpPath);

      if (remoteExists) {
        await sftp.delete(remotePath);
      }
      await sftp.rename(tmpPath, remotePath);

      // 4. Verificar integridad remota tras subida
      const uploadedBuffer = await sftp.get(remotePath);
      const uploadedHash = sha256(uploadedBuffer);

      if (uploadedHash !== localHash) {
        throw new Error(`Error de integridad en ${relPath}: hash local (${localHash}) != hash remoto (${uploadedHash})`);
      }

      console.log(`  -> Subido y verificado por SHA-256: ${uploadedHash.slice(0, 12)}... [OK]`);

      manifest.files.push({
        file: relPath,
        status: remoteExists ? 'updated' : 'created',
        localSha256: localHash,
        previousRemoteSha256: previousHash,
        verified: true
      });
    }

    // Guardar manifiesto en el respaldo remoto
    const manifestBuffer = Buffer.from(JSON.stringify(manifest, null, 2), 'utf8');
    await sftp.put(manifestBuffer, path.posix.join(backupBaseDir, 'manifest.json'));
    console.log(`\nManifiesto guardado en ${backupBaseDir}/manifest.json`);
    console.log('¡Sincronización completada con éxito!');

  } catch (err) {
    console.error('\nERROR durante la sincronización SFTP:', err);
    process.exit(1);
  } finally {
    await sftp.end();
  }
}

syncRelease();
