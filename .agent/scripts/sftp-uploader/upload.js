const Client = require('ssh2-sftp-client');
const fs = require('fs');
const path = require('path');

async function uploadFiles() {
  const sftpJsonPath = path.resolve(__dirname, '../../../.vscode/sftp.json');
  if (!fs.existsSync(sftpJsonPath)) {
    console.error('sftp.json not found at', sftpJsonPath);
    process.exit(1);
  }

  const sftpConfig = JSON.parse(fs.readFileSync(sftpJsonPath, 'utf8'));
  const config = {
    host: sftpConfig.host,
    port: sftpConfig.port || 22,
    username: sftpConfig.username,
    password: sftpConfig.password
  };

  const remotePathBase = sftpConfig.remotePath;
  const filesToUpload = process.argv.slice(2);

  if (filesToUpload.length === 0) {
    console.log('No files to upload. Usage: node upload.js <file1> <file2> ...');
    process.exit(0);
  }

  const sftp = new Client();
  try {
    await sftp.connect(config);
    console.log(`Connected to ${config.host}`);

    for (const file of filesToUpload) {
      const localFilePath = path.resolve(process.cwd(), file);
      if (!fs.existsSync(localFilePath)) {
        console.error(`File not found: ${localFilePath}`);
        continue;
      }
      
      // Calculate remote path
      const relativePath = path.relative(process.cwd(), localFilePath);
      const remoteFilePath = path.posix.join(remotePathBase, relativePath.split(path.sep).join('/'));
      const remoteDir = path.posix.dirname(remoteFilePath);

      // Ensure remote directory exists
      await sftp.mkdir(remoteDir, true);
      
      // Upload file
      await sftp.fastPut(localFilePath, remoteFilePath);
      console.log(`Uploaded ${relativePath} to ${remoteFilePath}`);
    }
  } catch (err) {
    console.error('Error during SFTP upload:', err);
  } finally {
    sftp.end();
  }
}

uploadFiles();
