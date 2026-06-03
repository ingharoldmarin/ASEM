<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['name', 'position', 'ficha_id', 'status', 'responsible_role', 'rejection_comment', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function ficha()
    {
        return $this->belongsTo(Ficha::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'sin_subir'   => 'Sin subir',
            'en_revision' => 'En revisión',
            'aprobado'    => 'Aprobado',
            'rechazado'   => 'Rechazado',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'sin_subir'   => 'gray',
            'en_revision' => 'yellow',
            'aprobado'    => 'green',
            'rechazado'   => 'red',
            default       => 'gray',
        };
    }
}
