<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_matricula_horario_materia_periodo_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matricula_horario_materia_periodo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id') // ID del ALUMNO
                  ->comment('ID del Alumno que cursa')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->foreignId('horario_materia_periodo_id')
                  ->comment('ID del HorarioMateriaPeriodo (clase) cursado')
                  ->constrained('horarios_materia_periodo')
                  ->onDelete('cascade');

            // Clave foránea a la matricula (pago) que habilitó esta cursada.
            // Esta FK DEBE SER ÚNICA porque una Matricula (pago) solo permite UNA instancia de cursada/estado_aprobacion.
            $table->foreignId('matricula_id')
                  ->unique() // Una Matricula (pago) solo puede tener un registro de estado académico asociado
                  ->comment('FK a la Matrícula (pago) que habilitó esta cursada')
                  ->constrained('matriculas')
                  ->onDelete('cascade');

            $table->foreignId('periodo_id')
                  ->comment('Periodo en el que se cursa, denormalizado para facilidad')
                  ->constrained('periodos')
                  ->onDelete('cascade');

            $table->string('estado_aprobacion')->default('cursando'); // 'aprobado', 'no_aprobado', 'retirado_oficialmente'
            $table->decimal('nota_final_numerica', 5, 2)->nullable();
            $table->string('nota_final_conceptual')->nullable();
            $table->text('observaciones_cierre')->nullable();
            $table->timestamp('fecha_actualizacion_estado')->nullable();

            $table->timestamps();

            // Unicidad: Un ALUMNO solo puede tener un registro de estado académico
            // para un horario_materia_periodo_id específico.

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matricula_horario_materia_periodo');
    }
};