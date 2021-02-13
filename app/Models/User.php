<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //zwracamy posty danego użytkownika - może być ich wiele
    public function posts(){
        return this->hanMany('App\Models\Post', 'author_id');
    }

    //zwracamy komentarze danego użytkownika - może ich być wiele
    public function comments(){
        return $this->hasMany('App\Models\Comment', 'from_user');
    }

    //sprawdzamy czy dany użytkownik może pisać posty
    public function can_post(){
        $role = $this->role;
        if ($role == 'author' || $role == 'admin'){
            return true;
        }
        return false;
    }

    //sprawdzamy czy użytkownik jest adminem
    public function is_admin(){
        $role = $this->role;
        if ($role == 'admin'){
            return true;
        }
        return false;
    }
}
