<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('resultados_aprendizaje', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('competencia_id')->constrained('competencias')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('aprendiz_resultado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aprendiz_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('resultado_id')->constrained('resultados_aprendizaje')->onDelete('cascade');
            $table->enum('status', ['pendiente', 'aprobado', 'no_aprobado'])->default('pendiente');
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
            $table->unique(['aprendiz_id', 'resultado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aprendiz_resultado');
        Schema::dropIfExists('resultados_aprendizaje');
        Schema::dropIfExists('competencias');
    }
};
