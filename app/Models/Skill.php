<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name', 'name_ar', 'slug', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
