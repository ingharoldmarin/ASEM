<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE folders MODIFY status ENUM('sin_subir', 'en_revision', 'aprobado', 'rechazado', 'pendiente_subir') DEFAULT 'sin_subir'");
    }

    public function down(): void
    {
        DB::statement("UPDATE folders SET status = 'rechazado' WHERE status = 'pendiente_subir'");
        DB::statement("ALTER TABLE folders MODIFY status ENUM('sin_subir', 'en_revision', 'aprobado', 'rechazado') DEFAULT 'sin_subir'");
    }
};
