<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'project_id',
        'author',
        'name',
        'description',
        'url',
        'stars'
    ];

    public function commits()
    {
        return $this->hasMany('App\Commit');
    }
}
