<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReportFile extends Model
{
    protected $fillable = ['report_id', 'original_name', 'file_path', 'file_type'];

    public function report()
    {
        return $this->belongsTo(MonthlyReport::class, 'report_id');
    }
}
