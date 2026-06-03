<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ficha extends Model
{
    protected $fillable = ['numero', 'program_id', 'institucion', 'municipio'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function instructors()
    {
        return $this->belongsToMany(User::class, 'instructor_ficha', 'ficha_id', 'instructor_id')->withTimestamps();
    }

    public function aprendices()
    {
        return $this->belongsToMany(User::class, 'aprendiz_ficha', 'ficha_id', 'aprendiz_id')
            ->withPivot('enrolled_by')->withTimestamps();
    }

    public function folders()
    {
        return $this->hasMany(Folder::class)->orderBy('position');
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function monthlyReports()
    {
        return $this->hasMany(MonthlyReport::class);
    }
}
