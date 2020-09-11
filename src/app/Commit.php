<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    use \jdavidbakr\ReplaceableModel\ReplaceableModel;

    protected $fillable = [
        'project_id',
        'sha',
        'url',
        'message',
        'date'
    ];
}
