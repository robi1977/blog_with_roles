# Laravel Blog with Roles

Celem projektu jest utworzenie struktury **bloga** z założeniami że:
- każdy może się zarejestrować/zalogować;
- użytkownicy mogą mieć jedną z ról *admin*, *autor*, *czytelnik*;
- *Autorzy* mogą w blogu dodawać nowe posty, edytować lub usuwać posty swojego autorstwa;
- *Admini* mają pełny dostęp do wszystkich postów tj. mogą dodawać nowe, edytować lub usuwać wszystkie posty;
- *Admini* mają dostęp również do edycji kont użytkowników tj. mogą im zmieniać role lub ich usuwać;
- *Czytelnicy* mogą tylko komentować pod danym postem;
- każdy może czytać opublikowane posty

Projekt bazuje na projekcie [Harish Kumar](https://www.flowkl.com/tutorial/web-development/simple-blog-application-in-laravel-7)

# Przygotowanie bazy projektu

Pusty projekt Laravela tworzymy za pomocą komendy w polu poleceń:
`composer create-project laravel/laravel nazwa_projektu`
W tym przypadku nazwą projektu jest **"blog_with_roles"**

Następnie doinstalowane są:
- pakiet z podręcznymi klasami pomocniczymi: `composer require laravel/helpers`
- pakiet odpowiadający za wczytanie odpowiedniego UI: `composer require laravel/ui`
- przełączamy się na UI bootstrapa wraz ze ścieżkami autoryzacji `php artisan ui bootstrap --auth`

Konfigurujemy bazę danych w dwóch krokach:
1. tworzymy bazę danych w MySQL poprzez `CREATE DATABASE nazwa_bazy` - u mnie **blog_with_roles**
2. wprowadzamy dane do pliku *.env*:
```sql
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nazwa_bazy #blog_with_roles
DB_USERNAME=nazwa_użytkownika
DB_PASSWORD=hasło_użytkownika
```
Korzystając z możliwości *artisan*'a tworzymy dla postów i komentarzy równocześnie pliki modelu, migracji i kontrolera wraz z podstawowymi metodami:
`php artisan make:model Post -mcr`
`php artisan make:model Comment -mcr`
Znaczenie przełączników przy tworzeniu modelu:
- "m" - utworzenie migracji
- "c" - utworzenie kontrolera
- "cr" - utworzenie kontrolera wraz z podstawowymi metodami
- "s" - utworzenie pliku wpisującego dane z fabryki do bazy oraz pierwsze dane np. z góry przewidzianych użytkowników
- "f" - utworzenie pliku "fabryki" z informacjami, jak tworzyć fake'owe dane

Tworzymy kontroler użytkownika: `php make:controller UserController -r`
Zarówno migracja jak i model są zaimplementowane odrazu przy tworzeniu projektu.

## Uzupełnianie migracji

### Posts
Do istniejących pól `$table` dopisujemy informacje o nowych kolumnach tak, żeby całość wyglądała:
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('author_id');
    $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
    $table->string('title')->unidue();
    $table->text('body');
    $table->string('slug')->unique();
    $table->boolean('active');
    $table->timestamps();
});
```
### Comments
Podobnie jak w przypadku **Posts** uzupełniamy informacje o brakujące kolumny:
```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('on_post');
    $table->unsignedBigInteger('from_user');
    $table->foreign('on_post')->references('id')->on('posts')->onDelete('cascade');
    $table->foreign('from_user')->references('id')->on('users')->onDelete('cascade');
    $table->text('body');
    $table->timestamps();
});
```

### Users
Dodajemy jedną kolumnę do tabeli mówiącą o rolu użytkownika:
```php
            $table->enum('role', ['admin', 'author', 'subcriber'])->default('author');
```
## Uzupełnianie modeli 
W uzupełnianiu modeli głównie zależy nam na wypisaniu co można zmieniać w tabelach oraz wypisać relacje pomiędzy daną tabelą a innymi tabelami.

### Post model
Uzupełniamy w relacje oraz w informacje które komulny nie można lub można zmieniać
```php
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
```

### Comment model
Podobnie jak model **Post** uzupełniamy w relacje oraz informację, które kolumny są zabezpieczone przed zmianą
```php
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
```

### User model
Model **User** uzupełniamy o nowe metody pozwalające nam wczytać posty i komentarze danego użytkownika oraz sprawdzić czy może publikować posty oraz czy jest adminem
```php
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
```
## uzupełnianie kontrolerów
Dzięki skorzystaniu z przełącznika `-cr` podczas tworzenia modeli w kontrolerach mamy już podstawowe metody typu: `index`, `create`, `store`, `edit`, `show`, `destroy`.
W przypadku kiedy korzystamy tylko z Laravela i widoków Blade będziemy korzystać z wszystkich tych metod. W przypadku kiedy będziemy korzystać z Laravel i ReactJS, niektóre z tych metod nie będą wykorzystywane.

### PostController
Pełna wersja:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
//dodatkowe klasy wykorzstane w tej klasie
use App\Requests\PostFormRequest;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //wczytanie 5-ciu najnowszysch OPUBLIKOWANYCH postów
        $posts = Post::where('active', 1)->orderedBy('created_at', 'desc')->paginate(5);
        //nazwa dla tytułu
        $title = "Ostatnie posty";
        //wyświetlenie widoku home wraz z ostatnimi postami i odpowiednim tytułem
        //zamiast 'withPosts($posts)' można zastosować konstrukcję 'with('posts', $posts)'
        //i podobnie zamiast 'withTitle($title)' można użyć 'with('title', $title)`
        return view('home')->withPosts($posts)->withTitle($title);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //metoda ta ma na celu sprawdzenie czy użytkownik może wpisać post i zwrócenie odpowiedniego widoku z formularzem dla wpisania postu lub powrotem do widoku podstawowego 'home' z wyświetleniem błędu
        if($request->user()->can_post()){
            return view('posts.create');
        } else {
            return redirect('/')->withErrors('Nie masz uprawnień do pisania postów.');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostFormRequest $request)
    {
        //metoda ma na celu wprowadzenie postu do bazy danych po wcześniejszym sprawdzeniu poprawności wprowadzonych danych przez klasę PostFormRequest
        $post = new Post();
        $post->title = $request->get('title');
        $post->body = $request->get('body');
        $post->slug = Str::slug($post->title);

        //sprawdzenie czy wprowadzony tytuł już czasem nie istnieje
        $duplicate = Post::where('slug', $post->slug)->first();
        if($duplicate){
            return redirect('new-post')->withErrors('Tytuł już istnieje w bazie danych')->withInput();
        }

        //przypisanie autora do tablicy $post
        $post->author_id = $request->user()->id;

        //sprawdzenie czy post jest do publikacji czy tylko zapisany
        if($request->has('save')){
            $post->active = 0;
            $message = "Post został zapisany.";
        } else {
            $post->active = 1;
            $message = "Post został opublikowany";
        }

        //wprowadzenie do bazy
        $post->save();

        return redirect('edit/'. $post->slug)->with('message', $message);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        //wyświetlenie postu o danym $slug
        $post = Post::where('slug', $slug)->first();

        if(!$post){
            return redirect('/')->withErrors("Nie ma takiego postu.");
        }

        //pobranie komentarzy
        $comments = $post->comments();

        return view('posts.show')->withPost($post)->withComments($comments);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $slug)
    {
        //sprawdzenie czy post istnieje, czy użytkownik ma prawa do edycji postu oraz wyświetlenie widoku do edycji postu
        $post = Post::where('slug', $slug)->first();

        if($post && ($request->user()->id == $post->author_id || $request->user()->is_admin())){
            return view('posts.edit')->with('post', $post);
        }
        return redirect('/')->withErrors("Nie masz uprawnień do edycji tego postu.");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        //wysłanie zaktualizowanych danych do bazy
        $post_id = $request->input('post_id');
        $post = Post::find($post_id);
        if($post && ($post->author_id == $request->user()->id || $request->user()->is_admin())){
            $title = $request->input('title');
            $slug = Str::slug($title);

            //czy jest duplikatem
            $duplicate = Post::where('slug', $slug)->first();
            if($duplicate){
                if($duplicate->id != $post_id){
                    return redirect('edit/'.$post->slug)->withErrors("Taki tytuł już ma inny post");
                } else {
                    $post->slug = $slug;
                }
            }

            $post->title = $title;
            $post->body = $request->input('body');

            if($request->has('save')){
                $post->active = 0;
                $message = "Post został zapisany.";
                $landing = 'edit/.'.$post->slug;
            } else {
                $post->active = 1;
                $message = "Post został opublikowany.";
                $landing = $post->slug;
            }
            $post->save();
            return redirect($landing)->withMessage($message);
        } else {
            return redirect('/')->withErrors("Nie masz uprawnień do edytowania postu.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id, Post $post)
    {
        //usuwanie postu z bazy
        $post = Post::find($id);
        if($post && ($post->author_id == $request->user()->id || $request->user()->is_admin())){
            $post->delete();
            $message = "Post został usunięty.";
            return redirect('/')->withMessage($message);
        }
        return redirect('/')->withErrors("Nie masz uprawnień do usunięcia postu.");
    }
}
```
### CommentController
Poniżej tylko fragment kontrolera dodawania komentarzy. Należy uzupełnić resztę metod na podstawie kontrolera do publikowania postów.
```php
public function store(Request $request)
{
    //zapisanie komentarza do bazy wraz z odpowiednimi odnośnikami do autora oraz do postu
    $input['from_user'] = $request->user()->id;
    $input['on_post'] = $request->input('on_post');
    $input['body'] = $request->input('body');

    $slug = $request->input('slug');

    Comments::create($input);
    return redirect($slug)->withMessage("Komentarz został opublikowany.");
}
```
## Ścieżki dostępu
Aktualizacja pliku **routes/web.php** związana z wypełnieniem sposobów przekierowania/wykorzystania odpowiednich metod w odpowiednich kontrolerach. 
==Z pewnością będzie należało je przeglądnąć poniweaż nie podobają mi się.==

Wygląd pliku po aktualizacji:
```php
Route::get('/', [App\Http\Controller\PostController::class, 'index'])->name('home');
Route::get('/home', ['as'=>'home', 'uses'=>'PostController@index']);

Route::get('/logout', 'UserController@logout');

Route::prefix('auth')->group(function () {
    Auth::routes();
});

Route::middleware(['auth'])->group(function () {
    Route::get('new-post', 'PostController@create');
    Route::post('new-post', 'PostController@store');
    Route::get('edit/{slug}', 'PostController@edit');
    Route::post('update', 'PostController@update');
    Route::get('delete/{id}', 'PostController@destroy');
    Route::get('my-all-post', 'UserController@user_posts_all');
    Route::get('my-drafts', 'UserController@user_posts_draft');
    Route::post('comment/add', 'CommentController@store');
    Route::post('comment/delete/{id}', 'CommentController@destroy');
});

Route::get('user/{id}', 'UserController@profile')->where('id', '[0-9]+');
Route::get('user/{id}/posts', 'UserController@user_posts')->where('id', '[0-9]+');

Route::get('/{slug}', ['as'=>'post', 'uses'=>'PostController@show'])->where('slug', '[A-Za-z0-9-_]+');
```

# Front-end
Bazując na szablonach Blade będziemy usupełniać i tworzyć odpowiednie widoki dla odpowiednich metod kontrolerów.


# UWAGI
1. dokończyć kontrolery CommentController i UserController
2. uzupełnić widoki
3. sprawdzić klasy dla elementu `nav` bo za cholerę nie działa jak należy