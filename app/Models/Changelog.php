<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Changelog extends Model
{
    protected $fillable = [
        'author_name',
        'author_email',
        'commit_hash',
        'commit_message',
        'description',
        'type',
        'section',
    ];
}
