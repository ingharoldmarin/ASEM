<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aprendiz_ficha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aprendiz_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ficha_id')->constrained('fichas')->onDelete('cascade');
            $table->foreignId('enrolled_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['aprendiz_id', 'ficha_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aprendiz_ficha');
    }
};
