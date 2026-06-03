<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'numero_documento', 'telefono',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isCoordinacion(): bool { return $this->role === 'coordinacion'; }
    public function isInstructor(): bool { return $this->role === 'instructor'; }
    public function isAprendiz(): bool { return $this->role === 'aprendiz'; }
    public function canManage(): bool { return in_array($this->role, ['admin', 'coordinacion']); }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'instructor_program', 'instructor_id', 'program_id')->withTimestamps();
    }

    public function fichasAsInstructor()
    {
        return $this->belongsToMany(Ficha::class, 'instructor_ficha', 'instructor_id', 'ficha_id')->withTimestamps();
    }

    public function fichasAsAprendiz()
    {
        return $this->belongsToMany(Ficha::class, 'aprendiz_ficha', 'aprendiz_id', 'ficha_id')
            ->withPivot('enrolled_by')->withTimestamps();
    }

    public function competencias()
    {
        return $this->hasMany(Competencia::class, 'instructor_id');
    }

    public function resultadosAprendizaje()
    {
        return $this->belongsToMany(ResultadoAprendizaje::class, 'aprendiz_resultado', 'aprendiz_id', 'resultado_id')
            ->withPivot('status', 'evaluated_at')->withTimestamps();
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class, 'instructor_id');
    }

    public function monthlyReports()
    {
        return $this->hasMany(MonthlyReport::class, 'instructor_id');
    }
}
