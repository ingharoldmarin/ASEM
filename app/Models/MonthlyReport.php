<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    protected $fillable = ['instructor_id', 'ficha_id', 'month', 'year', 'status', 'comment'];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function ficha()
    {
        return $this->belongsTo(Ficha::class);
    }

    public function files()
    {
        return $this->hasMany(MonthlyReportFile::class, 'report_id');
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return $months[$this->month] ?? $this->month;
    }
}
