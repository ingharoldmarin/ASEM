<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'program_id', 'instructor_id'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function resultados()
    {
        return $this->hasMany(ResultadoAprendizaje::class);
    }
}
