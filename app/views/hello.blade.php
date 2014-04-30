@extends('layouts.main')
@section('content')


    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
      <div class="container">
        <p>This is app uses <a href="https://github.com/zakir-hyder/oauth-1-lib" target="_blank">https://github.com/zakir-hyder/oauth-1-lib</a> OAuth 1 library to post on different networks.</p>
        <p><a class="btn btn-primary btn-lg" role="button" href="https://github.com/zakir-hyder/laravel-oauth-less-than-two" target="_blank">Learn more &raquo;</a></p>
      </div>
    </div>

    <div class="container">
      <!-- Example row of columns -->
      @if ( $errors->count() > 0 )
			  <div class="alert alert-block alert-danger fade in">
			      <button type="button" class="close" data-dismiss="alert"></button>
			      <h4 class="alert-heading">Error!</h4>
			      <p>
			          @foreach($errors->all() as $error_msg)
			            <p >{{ $error_msg }}</p>
			          @endforeach
			      </p>
			  </div>
			@endif

			@if(Session::has('message'))
			  <div class="alert alert-block alert-success fade in">
			      <button type="button" class="close" data-dismiss="alert"></button>
			      <h4 class="alert-heading">Success!</h4>
			      <p>
			          {{ Session::get('message') }}
			      </p>
			  </div>
			@endif

			@if(Session::has('err_msg'))
        <div class="alert alert-block alert-danger fade in">
            <button type="button" class="close" data-dismiss="alert"></button>
            <h4 class="alert-heading">Error!</h4>
            <p>
                {{ Session::get('err_msg') }}
            </p>
        </div>
	    @endif
      <div class="row">
        <div class="col-md-4">
          <h2>Twitter</h2>
          <p>After clicking the button you will be redirect to twitter to authorise the app. After you authorise the app, twitter redirect you to back the site with oauth_token i.e. getCreate() function. With that token app will send request to https://api.twitter.com/oauth/access_token and will get oauth_token & oauth_token_secret. Then getCreate() function will forward the request to getTwit(). getTwit() will post status to your twitter profile.</p>
          <p><a class="btn btn-default" href="{{ action('TwitterProfileAccountsController@getCreate') }}" role="button">Click here To Auth & Tweet &raquo;</a>&nbsp;<a class="btn btn-default" href="{{ action('TwitterProfileAccountsController@getTwit') }}" role="button">Click here To Tweet &raquo;</a></p>
        </div>
      </div>


      <hr>

    </div> <!-- /container -->






@stop