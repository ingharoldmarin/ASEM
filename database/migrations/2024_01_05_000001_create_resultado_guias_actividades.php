<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guías de aprendizaje por resultado
        Schema::create('resultado_guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resultado_id')->constrained('resultados_aprendizaje')->onDelete('cascade');
            $table->string('original_name');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Actividades por resultado
        Schema::create('resultado_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resultado_id')->constrained('resultados_aprendizaje')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // Notas de aprendices por actividad
        Schema::create('actividad_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('resultado_actividades')->onDelete('cascade');
            $table->foreignId('aprendiz_id')->constrained('users')->onDelete('cascade');
            $table->decimal('nota', 3, 1)->default(0); // 0.0 a 5.0
            $table->timestamps();
            $table->unique(['actividad_id', 'aprendiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_notas');
        Schema::dropIfExists('resultado_actividades');
        Schema::dropIfExists('resultado_guias');
    }
};
