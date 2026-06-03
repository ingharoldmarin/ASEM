<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ficha_id')->constrained('fichas')->onDelete('cascade');
            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedSmallInteger('year');
            $table->enum('status', ['pendiente', 'revisado', 'aprobado', 'rechazado'])->default('pendiente');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['instructor_id', 'ficha_id', 'month', 'year']);
        });

        Schema::create('monthly_report_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('monthly_reports')->onDelete('cascade');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_report_files');
        Schema::dropIfExists('monthly_reports');
    }
};
