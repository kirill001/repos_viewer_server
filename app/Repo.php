<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repo extends Model
{
    protected $fillable = [
        'repo_id',
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
