#!/usr/bin/env python3
import os
import sys
import json

# Lista de archivos a subir
FILES_TO_UPLOAD = [
    # Migraciones
    "database/migrations/tenant/2026_08_14_000001_create_tipo_hitos_table.php",
    "database/migrations/tenant/2026_08_14_000002_create_hitos_table.php",
    "database/migrations/tenant/2026_08_14_000003_create_hito_fotos_table.php",
    "database/migrations/tenant/2026_08_14_000004_create_hito_likes_table.php",
    "database/migrations/tenant/2026_08_14_000005_create_hito_denuncias_table.php",
    "database/migrations/tenant/2026_08_14_000006_create_hito_usuario_table.php",
    "database/migrations/tenant/2026_08_14_000007_create_hito_restricciones_tables.php",

    # Modelos
    "app/Models/TipoHito.php",
    "app/Models/Hito.php",
    "app/Models/HitoFoto.php",
    "app/Models/HitoLike.php",
    "app/Models/HitoDenuncia.php",
    "app/Models/HitoUsuario.php",
    "app/Models/CrecimientoUsuario.php",
    "app/Models/TareaConsolidacionUsuario.php",

    # Traits y Servicios
    "app/Traits/AplicaEfectosAprobacion.php",
    "app/Services/HitoTriggerService.php",

    # Seeders
    "database/seeders/TipoHitoSeeder.php",
    "database/seeders/PermisoHitoSeeder.php",

    # Policies y Controladores
    "app/Policies/HitoPolicy.php",
    "app/Http/Controllers/HitoController.php",

    # Componentes Livewire
    "app/Livewire/Hitos/GestionarHitos.php",
    "app/Livewire/Hitos/CrearEditarHito.php",
    "app/Livewire/Hitos/GestionarAsistencias.php",
    "app/Livewire/Hitos/GestionarDenuncias.php",

    # Vistas Livewire
    "resources/views/livewire/hitos/gestionar-hitos.blade.php",
    "resources/views/livewire/hitos/crear-editar-hito.blade.php",
    "resources/views/livewire/hitos/gestionar-asistencias.blade.php",
    "resources/views/livewire/hitos/gestionar-denuncias.blade.php",

    # Vistas Contenedoras
    "resources/views/contenido/paginas/hitos/gestionar.blade.php",
    "resources/views/contenido/paginas/hitos/crear-editar.blade.php",
    "resources/views/contenido/paginas/hitos/asistencias.blade.php",
    "resources/views/contenido/paginas/hitos/denuncias.blade.php",

    # Rutas y Documentación
    "routes/app.php",
    ".agent/workflows/agenteHitos.md",
]

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    sftp_config_path = os.path.join(base_dir, ".vscode", "sftp.json")

    if not os.path.exists(sftp_config_path):
        print(f"Error: No se encontró {sftp_config_path}")
        sys.exit(1)

    with open(sftp_config_path, "r", encoding="utf-8") as f:
        config = json.load(f)

    host = config.get("host")
    port = config.get("port", 22)
    username = config.get("username")
    password = config.get("password")
    remote_root = config.get("remotePath", "/home/redil2024/public_html")

    print(f"🚀 Conectando a {username}@{host}:{port}...")

    try:
        import paramiko
    except ImportError:
        print("⚠️ Instalando paramiko para transferencias SFTP/SSH...")
        os.system("pip3 install paramiko --quiet")
        import paramiko

    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port=port, username=username, password=password, timeout=30)
    print("✅ Conexión SSH establecida.")

    sftp = ssh.open_sftp()
    print("✅ Canal SFTP abierto.")

    def ensure_remote_dir(remote_dir):
        dirs = []
        current = remote_dir
        while current not in ("", "/", "."):
            dirs.append(current)
            current = os.path.dirname(current)
        dirs.reverse()
        for d in dirs:
            try:
                sftp.stat(d)
            except IOError:
                try:
                    sftp.mkdir(d)
                except IOError:
                    pass

    print(f"\n📦 Subiendo {len(FILES_TO_UPLOAD)} archivos al servidor...")
    subidos = 0
    errores = 0

    for rel_path in FILES_TO_UPLOAD:
        local_path = os.path.join(base_dir, rel_path)
        remote_path = os.path.join(remote_root, rel_path)

        if not os.path.exists(local_path):
            print(f"  ❌ No existe en local: {rel_path}")
            errores += 1
            continue

        remote_dir = os.path.dirname(remote_path)
        ensure_remote_dir(remote_dir)

        try:
            sftp.put(local_path, remote_path)
            print(f"  ⬆️ Subido: {rel_path}")
            subidos += 1
        except Exception as e:
            print(f"  ❌ Error subiendo {rel_path}: {e}")
            errores += 1

    sftp.close()
    print(f"\n✨ Transferencia finalizada: {subidos} subidos, {errores} errores.")

    if errores == 0:
        print("\n⚙️ Ejecutando comandos artisan en el servidor...")
        artisan_commands = [
            f"cd {remote_root} && php artisan tenant:migrate",
            f"cd {remote_root} && php artisan tenant:db --seed --class=TipoHitoSeeder",
            f"cd {remote_root} && php artisan tenant:db --seed --class=PermisoHitoSeeder",
            f"cd {remote_root} && php artisan route:clear && php artisan view:clear && php artisan cache:clear",
        ]

        for cmd in artisan_commands:
            print(f"\n👉 Ejecutando: {cmd}")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            out = stdout.read().decode("utf-8")
            err = stderr.read().decode("utf-8")
            if out:
                print(out.strip())
            if err:
                print(f"⚠️ {err.strip()}")

    ssh.close()
    print("\n🎉 ¡Todo listo y sincronizado en el servidor!")

if __name__ == "__main__":
    main()
