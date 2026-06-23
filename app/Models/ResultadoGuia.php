<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultadoGuia extends Model
{
    protected $table = 'resultado_guias';
    protected $fillable = ['resultado_id', 'original_name', 'file_path', 'uploaded_by'];

    public function resultado()
    {
        return $this->belongsTo(ResultadoAprendizaje::class, 'resultado_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
