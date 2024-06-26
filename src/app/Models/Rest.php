<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'rest_start_time',
        'rest_end_time',
    ];

    public function work()
    {
        return $this->belongsTo(Work::class);
    }
}
