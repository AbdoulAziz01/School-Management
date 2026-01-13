<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'order', 'cycle'];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'level_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'level_subject')
                    ->withPivot('coefficient')
                    ->withTimestamps();
    }
}
