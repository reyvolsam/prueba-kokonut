<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class UserPhoto extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'url', 'lat', 'lon', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
