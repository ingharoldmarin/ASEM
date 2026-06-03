<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = ['code', 'name'];

    public function instructors()
    {
        return $this->belongsToMany(User::class, 'instructor_program', 'program_id', 'instructor_id')->withTimestamps();
    }

    public function fichas()
    {
        return $this->hasMany(Ficha::class);
    }

    public function competencias()
    {
        return $this->hasMany(Competencia::class);
    }
}
