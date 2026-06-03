<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_ficha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ficha_id')->constrained('fichas')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['instructor_id', 'ficha_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_ficha');
    }
};
