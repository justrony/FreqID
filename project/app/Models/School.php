<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = "schools";

    protected $fillable= [
        'name',
        'inep_code',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'school_user', 'school_id', 'user_id');
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }
}
