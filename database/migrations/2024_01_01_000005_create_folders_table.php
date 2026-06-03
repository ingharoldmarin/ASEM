<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('position'); // 1-33
            $table->foreignId('ficha_id')->constrained('fichas')->onDelete('cascade');
            $table->enum('status', ['sin_subir', 'en_revision', 'aprobado', 'rechazado'])->default('sin_subir');
            $table->text('rejection_comment')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['ficha_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
