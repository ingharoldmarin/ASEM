<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultadoActividad extends Model
{
    protected $table = 'resultado_actividades';
    protected $fillable = ['resultado_id', 'nombre', 'descripcion'];

    public function resultado()
    {
        return $this->belongsTo(ResultadoAprendizaje::class, 'resultado_id');
    }

    public function notas()
    {
        return $this->hasMany(ActividadNota::class, 'actividad_id');
    }

    public function notaDelAprendiz(int $aprendizId): ?ActividadNota
    {
        return $this->notas()->where('aprendiz_id', $aprendizId)->first();
    }
}
