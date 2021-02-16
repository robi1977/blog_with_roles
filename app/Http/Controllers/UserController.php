<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Posts;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function user_posts($id)
    {
        $posts = Post::where('author_id', $id)->where('active', 1)->orderBy('created_at', 'desc')->paginate(5);
        $title = User::find($id)->name;

        return view('home')->with('posts', $posts)->with('title', $title);
    }
    public function user_posts_all(Request $request)
    {
        $user = $request->user();
        $posts = Post::where('author_id', $user->id)->orderBy('created_at', 'desc')->paginate(5);
        $title = $user->name;

        return view('home')->with('posts', $posts)->with('title', $title);
    }
    public function user_posts_drafts(Request $request)
    {
        $user = $request->user();
        $posts = Post::where('author_id', $user->id)->where('active', 0)->orderBy('created_at', 'desc')->paginate(5);
        $title = $user->name;

        return view('home')->with('posts', $posts)->with('title', $title);
    }
    public function profile(Request $request, $id)
    {
        //TODO: Sprawdzić inny sposób zapisu typu $data->user = User::find($id);
        //TODO: $data->latest_comments = $data->user->comments->take(5);  itd...
        $data['user'] = User::find($id);
        if(!$data['user']){
            return redirect('/');
        }
        if($request->user() && $data['user']->id == $request->user()->id){
            $data['author'] = true;
        } else {
            $data['author'] = null;
        }
        $data['comments_count'] = $data['user']->comments->count();
        $data['posts_count'] = $data['user']->posts->count();
        $data['posts_active_count'] = $data['user']->posts->where('active',1)->count();
        $data['posts_drafts_count'] = $data['posts_count'] - $data['posts_active_count'];
        $data['latest_posts'] = $data['user']->posts->where('active', 1)->take(5);
        $data['latest_comments'] = $data['user']->comments->take(5);

        //TODO: Sprawdzić zapis return view('admin.profile')->with('data', $data);
        return view('admin.profile', $data);
    }
}
