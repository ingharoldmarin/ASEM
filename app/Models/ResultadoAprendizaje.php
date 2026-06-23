<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultadoAprendizaje extends Model
{
    protected $table = 'resultados_aprendizaje';

    protected $fillable = ['nombre', 'descripcion', 'competencia_id'];

    public function competencia()
    {
        return $this->belongsTo(Competencia::class);
    }

    public function aprendices()
    {
        return $this->belongsToMany(User::class, 'aprendiz_resultado', 'resultado_id', 'aprendiz_id')
            ->withPivot('status', 'evaluated_at')->withTimestamps();
    }

    public function guias()
    {
        return $this->hasMany(ResultadoGuia::class, 'resultado_id');
    }

    public function actividades()
    {
        return $this->hasMany(ResultadoActividad::class, 'resultado_id');
    }
}
