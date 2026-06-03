<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear índice simple en instructor_id para que la FK pueda usarlo
        DB::statement('ALTER TABLE monthly_reports ADD INDEX idx_instructor_id (instructor_id)');
        // 2. Ahora sí podemos soltar el índice único compuesto
        DB::statement('ALTER TABLE monthly_reports DROP INDEX monthly_reports_instructor_id_ficha_id_month_year_unique');
        // 3. Hacer ficha_id nullable
        DB::statement('ALTER TABLE monthly_reports MODIFY ficha_id BIGINT UNSIGNED NULL');
        // 4. Nuevo unique: instructor + mes + año (sin ficha)
        DB::statement('ALTER TABLE monthly_reports ADD UNIQUE reports_instructor_month_year_unique (instructor_id, month, year)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE monthly_reports DROP INDEX reports_instructor_month_year_unique');
        DB::statement('ALTER TABLE monthly_reports MODIFY ficha_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE monthly_reports DROP INDEX idx_instructor_id');
        DB::statement('ALTER TABLE monthly_reports ADD UNIQUE (instructor_id, ficha_id, month, year)');
    }
};
