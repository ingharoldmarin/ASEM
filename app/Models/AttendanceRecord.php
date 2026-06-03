<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = ['session_id', 'aprendiz_id', 'status'];

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function aprendiz()
    {
        return $this->belongsTo(User::class, 'aprendiz_id');
    }
}
