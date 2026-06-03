<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ficha_id')->constrained('fichas')->onDelete('cascade');
            $table->date('date');
            $table->string('topic')->nullable();
            $table->timestamps();
            $table->unique(['instructor_id', 'ficha_id', 'date']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('attendance_sessions')->onDelete('cascade');
            $table->foreignId('aprendiz_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['presente', 'ausente', 'excusa'])->default('ausente');
            $table->timestamps();
            $table->unique(['session_id', 'aprendiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
    }
};
