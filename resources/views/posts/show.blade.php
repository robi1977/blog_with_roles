@extends('layouts.app')
@section('title')
  @if ($post)
    {{ $post->title }}
    @if (!Auth::guest() && ($post->author_id == Auth::user()->id || Auth::user()->is_admin()))
      <button class="btn" style="float: right">
        <a href="{{ url('edit/'.$post->slug) }}">Edit post</a>
      </button>
    @endif
  @else
    Page does not exist.
  @endif
@endsection
@section('title-meta')
  <p>{{ $post->created_at->format('M d, Y \a\t h:i a')}} by <a href="{{ url('user/'.$post->author_id)}}">{{$post->author->name}}</a></p>
@endsection
@section('content')
  <div>
    {!! $post->body !!}
  </div>
  <div>
    <h2>Leave a comment</h2>
  </div>
  @if (Auth::guest)
    <p>Login to comment</p>
  @else
    <div class="panel-body">
      <form method="POST" action="/comment/add">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <input type="hidden" name="on_post" value="{{ $post->id }}" />
        <input type="hidden" name="slug" value="{{ $post->slug }}" />
        <input type="hidden" name="from_user" value="{{ Auth::user()->id }}" />
        <div class="form-group">
          <textarea required="required" placeholder="Enter comment here" name="body" class="form-control"></textarea>
        </div>
        <input type="submit" name="post_comment" class="btn btn-success" value = "Post" />
      </form>
    </div>
    <div>
      @if ($comments)
        <ul style="list-style: none; padding: 0">
          @foreach ($comments as $comment)
            <li class="panel-body">
              <div class="list-group">
                <div class="list-group-item">
                  <h3>{{ $comment->author->name }}</h3>
                  <p>{{ $comment->created_at->format('M d,Y \a\t h:i a') }}</p>
                </div>
                <div class="list-group-item">
                  <p>{{ $comment->body }}</p>
                </div>
              </div>
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  @else 
  404 error 
  @endif

@endsection