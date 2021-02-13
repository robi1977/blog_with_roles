<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    //zabezpieczone przed zmianą kolumny => w tym wypadku żadna
    protected $guarded = [];

    //odniesienie do autora komentarza
    public function author()
    {
        return $this->belongsTo('App\Models\User', 'from_user');
    }

    //odnalezienie postu do którego należy komentarz
    public function post()
    {
        return $this->belongsTo('App\Models\Post', 'on_post');
    }
}
