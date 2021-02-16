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
        $posts = Post::where('active', 1)->orderBy('created_at', 'desc')->paginate(5);
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
