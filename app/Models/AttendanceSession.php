<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = ['instructor_id', 'ficha_id', 'date', 'topic'];

    protected $casts = ['date' => 'date'];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function ficha()
    {
        return $this->belongsTo(Ficha::class);
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }
}
