<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
          <span class="sr-only">Toggle Navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="https://www.flowkl.com">Flowkl</a>
      </div>
      <div class="collapse navbar-collapsed" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
          <li>
            <a href="{{ url('/')}}">Home</a>
          </li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          @if(Auth::guest())
            <li>
              <a href="{{ url('/auth/login') }}">Login</a>
            </li>
            <li>
              <a href="{{ url('/auth/register') }}">Register</a>
            </li>
          @else 
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->name }}<span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">
                @if(Auth::user()->can_post())
                  <li>
                    <a href="{{ url('new-post') }}">Add new post</a>
                  </li>
                  <li>
                    <a href="{{ url('/user/'.Auth::id().'/posts') }}">My posts</a>
                  </li>
                @endif
                  <li>
                    <a href="{{ url('/user/'.Auth::id()) }}">My profile</a>
                  </li>
                  <li>
                    <a href="{{ url('/logout') }}">Logout</a>
                  </li>
              </ul>
            </li>              
          @endif
        </ul>
      </div>
    </div>
  </nav>
  <div class="container">
    @if(Session::has('message'))
      <div class="flash alert-info">
        <p class="panel-body">
          {{ Session::get('message') }}
        </p>
      </div>
    @endif
    @if($errors->any())
      <div class="flash alert-danger">
        <ul class="panel-body">
          @foreach ($errors->all() as $error)
            <li>
              {{ $error }}
            </li>    
          @endforeach
        </ul>
      </div>
    @endif
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h2>@yield('title')</h2>
            @yield('title-meta')
          </div>
          <div class="panel-body">
            @yield('content')
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <p>Copyright <a href="https://www.flowkl.com">Flowkl</a></p>
      </div>
    </div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
</body>
</html>
