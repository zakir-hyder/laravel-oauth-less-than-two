@extends('layouts.coming_soon')
        
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-6 col-md-offset-3 coming-soon-header">
			<a class="brand" href="/">
			<img src="{{ asset('assets/img/logo.png') }}" alt="logo"/>
			</a>
		</div>
		<div class="col-md-6 col-md-offset-3 coming-soon-countdown">
			<h1 style="color:#FFFFFF;">Goes Live In:</h1>
			<div id="defaultCountdown">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6 col-md-offset-3 coming-soon-content">
			<h1>EXCLUSIVE ACCESS ONLY!</h1>
			<p>
				 We're offering premier access to our exclusive partners until the public launch date shown above.  If you have an official invitation from the SYNDWIRE team, please login using the credentials you have been provided. 
			</p>
			<br>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<a class="btn btn-lg green" type="button" href="{{ action('UsersController@getLogin') }}">
			<span>
				 Login
			</span>
			<i class="m-icon-swapright m-icon-white"></i></a>
		</div>
	</div>
	<!--/end row-->
</div>

@stop