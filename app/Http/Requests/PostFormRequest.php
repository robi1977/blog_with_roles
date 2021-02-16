<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostFormRequest extends FormRequest
{
  //Sprawdzenie czy użytkownik ma uprawnienia do wywołania tego sprawdzania
  public function authorize(){
    if($this->user()->can_post()){
      return true;
    }
    return false;
  }

  //sprawdzenie poprawności wprowadzonych danych
  //zwraca tablicę z reguałmi które powinnien przejść wskazany input z formularza
  public function rules(){
    return [
      'title' => 'required|unique:posts|max:255',
      'title' => array('regex:/^[A-Za-z0-9 ]+$/'),
      'body' => 'required',
    ];
  }
}