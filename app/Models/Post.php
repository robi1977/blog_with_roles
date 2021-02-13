<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    //informacja które kolumny są zabezpieczone przed modyfikowaniem
    protected $guarded = [];

    //post może posiadać wiele komentarzy
    //metoda zwraca komentarze przynależne do danego postu
    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'on_post');
    }

    //zwraca odniesienie do tablicy 'users' mówiącą o autorze postu
    public function author()
    {
        return $this->belongsTo('App\Models\User', 'author_id');
    }
}
