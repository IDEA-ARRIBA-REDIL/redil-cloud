#!/usr/bin/env bash
set -e

SERVER="redil.ubicalo.com"
PORT="22"
USER="redil2024"
PASS="e2u7,cy-IQLl"
REMOTE_ROOT="/home/redil2024/public_html"
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "=============================================="
echo "🚀 INICIANDO SUBIDA Y DESPLIEGUE EN EL SERVIDOR"
echo "=============================================="

# 1. Crear archivo de comandos SFTP
SFTP_BATCH="/tmp/sftp_batch_hitos.txt"
cat << 'EOF' > "$SFTP_BATCH"
-mkdir /home/redil2024/public_html/database/migrations/tenant
-mkdir /home/redil2024/public_html/app/Models
-mkdir /home/redil2024/public_html/app/Traits
-mkdir /home/redil2024/public_html/app/Services
-mkdir /home/redil2024/public_html/app/Policies
-mkdir /home/redil2024/public_html/app/Http/Controllers
-mkdir /home/redil2024/public_html/app/Livewire/Hitos
-mkdir /home/redil2024/public_html/database/seeders
-mkdir /home/redil2024/public_html/resources/views/livewire/hitos
-mkdir /home/redil2024/public_html/resources/views/contenido/paginas/hitos
-mkdir /home/redil2024/public_html/.agent/workflows
EOF

FILES=(
    "database/migrations/tenant/2026_08_14_000001_create_tipo_hitos_table.php"
    "database/migrations/tenant/2026_08_14_000002_create_hitos_table.php"
    "database/migrations/tenant/2026_08_14_000003_create_hito_fotos_table.php"
    "database/migrations/tenant/2026_08_14_000004_create_hito_likes_table.php"
    "database/migrations/tenant/2026_08_14_000005_create_hito_denuncias_table.php"
    "database/migrations/tenant/2026_08_14_000006_create_hito_usuario_table.php"
    "database/migrations/tenant/2026_08_14_000007_create_hito_restricciones_tables.php"
    "app/Models/TipoHito.php"
    "app/Models/Hito.php"
    "app/Models/HitoFoto.php"
    "app/Models/HitoLike.php"
    "app/Models/HitoDenuncia.php"
    "app/Models/HitoUsuario.php"
    "app/Models/CrecimientoUsuario.php"
    "app/Models/TareaConsolidacionUsuario.php"
    "app/Traits/AplicaEfectosAprobacion.php"
    "app/Services/HitoTriggerService.php"
    "database/seeders/TipoHitoSeeder.php"
    "database/seeders/PermisoHitoSeeder.php"
    "app/Policies/HitoPolicy.php"
    "app/Http/Controllers/HitoController.php"
    "app/Livewire/Hitos/GestionarHitos.php"
    "app/Livewire/Hitos/CrearEditarHito.php"
    "app/Livewire/Hitos/GestionarAsistencias.php"
    "app/Livewire/Hitos/GestionarDenuncias.php"
    "resources/views/livewire/hitos/gestionar-hitos.blade.php"
    "resources/views/livewire/hitos/crear-editar-hito.blade.php"
    "resources/views/livewire/hitos/gestionar-asistencias.blade.php"
    "resources/views/livewire/hitos/gestionar-denuncias.blade.php"
    "resources/views/contenido/paginas/hitos/gestionar.blade.php"
    "resources/views/contenido/paginas/hitos/crear-editar.blade.php"
    "resources/views/contenido/paginas/hitos/asistencias.blade.php"
    "resources/views/contenido/paginas/hitos/denuncias.blade.php"
    "routes/app.php"
    ".agent/workflows/agenteHitos.md"
)

for f in "${FILES[@]}"; do
    echo "put \"$BASE_DIR/$f\" \"$REMOTE_ROOT/$f\"" >> "$SFTP_BATCH"
done
echo "bye" >> "$SFTP_BATCH"

echo "📦 1. Subiendo archivos por SFTP al servidor..."

/usr/bin/expect << EXPECT_EOF
set timeout 180
spawn sftp -P $PORT -o StrictHostKeyChecking=no -b $SFTP_BATCH $USER@$SERVER
expect {
    -nocase "password:" {
        send "$PASS\r"
        exp_continue
    }
    eof
}
EXPECT_EOF

rm -f "$SFTP_BATCH"

echo "⚙️ 2. Ejecutando migraciones y seeders en el servidor..."

/usr/bin/expect << EXPECT_EOF
set timeout 180
spawn ssh -p $PORT -o StrictHostKeyChecking=no $USER@$SERVER "cd $REMOTE_ROOT && php artisan tenants:migrate && php artisan tenants:seed --class=TipoHitoSeeder && php artisan tenants:seed --class=PermisoHitoSeeder && php artisan route:clear && php artisan view:clear && php artisan cache:clear"
expect {
    -nocase "password:" {
        send "$PASS\r"
        exp_continue
    }
    eof
}
EXPECT_EOF

echo ""
echo "=============================================="
echo "🎉 ¡DESPLIEGUE Y MIGRACIONES COMPLETADAS CON ÉXITO!"
echo "=============================================="
