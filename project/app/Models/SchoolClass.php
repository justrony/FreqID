<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'school_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
