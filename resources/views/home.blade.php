@extends('layouts.app')

@section('title')
  {{$title}}
@endsection
@section('content')
  @if (!$posts->count())
    Brak postów do wyświetlenia
  @else
    <div class="">
      @foreach ($posts as $post)
        <div class="list-group">
          <div class="list-group-item">
            <h3>
              <a href="{{ url('/'.$post->slug) }}">
                {{ $post->title}}
              </a>
              <!--TODO: przemyśleć skrócenie poniższych 2ch if'ów -->
              @if (!Auth::guest() && ($post->author_id ==Auth::user()->id || Auth::user()->is_admin()))
                @if ($post->active == 1)
                  <button class="btn" style="float: right">
                    <a href="{{ url('edit/'.$post->slug) }}">Edit post</a>
                  </button>                    
                @else
                <button class="btn" style="float: right">
                  <a href="{{ url('edit/'.$post->slug) }}">Edit draft</a>
                </button> 
                @endif
              @endif
            </h3>
            <p>{{ $post->created_at->format('M d,Y \a\t h:i a')}} by <a href={{ url('/user/'.$post->author_id)}}>{{ $post->author->name }}</a></p>
          </div>
          <div class="list-group-item">
            <article>
              {!! Str::limit($post->body, 1500, '...<a href='.url("/".$post->slug).'>Read more</a>') !!}
            </article>
          </div>
        </div>
      @endforeach
      {!! $posts->render() !!}
    </div>
  @endif
@endsection
