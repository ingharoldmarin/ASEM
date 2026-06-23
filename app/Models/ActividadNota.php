<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadNota extends Model
{
    protected $table = 'actividad_notas';
    protected $fillable = ['actividad_id', 'aprendiz_id', 'nota'];
    protected $casts = ['nota' => 'decimal:1'];

    public function actividad()
    {
        return $this->belongsTo(ResultadoActividad::class, 'actividad_id');
    }

    public function aprendiz()
    {
        return $this->belongsTo(User::class, 'aprendiz_id');
    }

    public function getAprobadoAttribute(): bool
    {
        return $this->nota >= 3.5;
    }

    public function getAprobadoLabelAttribute(): string
    {
        return $this->aprobado ? 'Aprobado' : 'Reprobado';
    }
}
