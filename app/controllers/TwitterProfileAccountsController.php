<?php

class TwitterProfileAccountsController extends \BaseController {

	public function getCreate()
	{
		Session::forget('twitter_oauth_token');
		Session::forget('twitter_oauth_token_secret');

		$redirect_url = URL::action('TwitterProfileAccountsController@getSaveTwitterProfile');
		$request_token_url = "https://api.twitter.com/oauth/request_token";
		$consumer_key  = Config::get('syndwire.twitter_client_id');
		$consumer_secret = Config::get('syndwire.twitter_client_secret');
		$args = array(
			'oauth_callback' =>$redirect_url
		);

		$consumer = new OAuth\OAuthConsumer($consumer_key, $consumer_secret);
		$request = OAuth\OAuthRequest::from_consumer_and_token($consumer, NULL,"GET", $request_token_url, $args);
		$request->sign_request(new OAuth\OAuthSignatureMethodHMACSHA1(), $consumer, NULL);
		$url = $request->to_url();

		$client = new Guzzle\Http\Client($url, array(
			'request.options' => array(
				'verify' => false
			)
		));

		try 
		{
			$response = $client->get()->send();
		} catch (Guzzle\Http\Exception\BadResponseException $e) 
		{
			return Redirect::action('UsersController@getThirdParty')
							->with('err_msg', 'Ooops! Something went wrong. Please check your account credentials and try adding your twitter account again. Also make sure your twitter account is verified.');
		}

		$response_body = $response->getBody();			
		$request_token = OAuth\OAuthUtil::parse_parameters($response_body);

		if($response->getStatusCode() == 200 )
		{
			Session::put('twitter_oauth_token', $request_token['oauth_token']);
			Session::put('twitter_oauth_token_secret', $request_token['oauth_token_secret']);
			return Redirect::to("https://api.twitter.com/oauth/authorize?oauth_token={$request_token['oauth_token']}");
		}
		return Redirect::action('UsersController@getThirdParty')
							->with('err_msg', 'Ooops! Something went wrong. Please check your account credentials and try adding your twitter account again. Also make sure your twitter account is verified.');
	}

	public function getEdit($id = null)
	{
		if($id)
		{
			$twitter_profile_account = Auth::user()->twitterProfileAccounts()
				->where('id', '=', $id)
				->first();
			if($twitter_profile_account)
			{
				Session::put('twitter_profile_id', $id);
				return Redirect::action('TwitterProfileAccountsController@getCreate');
			}	
		}
		
		return Redirect::action('UsersController@getThirdParty')
							->with('err_msg', 'Ooops! Something went wrong. Please check your account credentials and try adding your twitter account again. Also make sure your twitter account is verified.');
	}

	public function getSaveTwitterProfile()
	{
		if (Session::get('twitter_oauth_token') === Input::get('oauth_token')) {
			$access_token_url = 'https://api.twitter.com/oauth/access_token';
			$consumer_key  = Config::get('syndwire.twitter_client_id');
			$consumer_secret = Config::get('syndwire.twitter_client_secret');
			$oauth_token  = Session::get('twitter_oauth_token');
			$oauth_token_secret = Session::get('twitter_oauth_token_secret');
			$args['oauth_verifier'] = Input::get('oauth_verifier');

			$consumer = new OAuth\OAuthConsumer($consumer_key, $consumer_secret);
			$token = new OAuth\OAuthConsumer($oauth_token, $oauth_token_secret);
			$request = OAuth\OAuthRequest::from_consumer_and_token($consumer, $token,"GET", $access_token_url, $args);
			$request->sign_request(new OAuth\OAuthSignatureMethodHMACSHA1(), $consumer, $token);

			$url = $request->to_url();
			$client = new Guzzle\Http\Client($url, array(
				'request.options' => array(
					'verify' => false
				)
			));

			try 
			{
				$response = $client->get()->send();
			} catch (Guzzle\Http\Exception\BadResponseException $e) 
			{
				return Redirect::action('TwitterProfileAccountsController@getCreate');
			}

			$response_body = $response->getBody();
			$request_token = OAuth\OAuthUtil::parse_parameters($response_body);

			if(Session::get('twitter_profile_id'))
			{
				$twitter_profile_account = TwitterProfileAccount::where('id', '=', Session::get('twitter_profile_id'))->first();
				Session::forget('twitter_profile_id');
			}
			else 
			{
				$twitter_profile_account = new TwitterProfileAccount;
				$user = User::find(Auth::user()->id);
				$user->number_networks =  $user->number_networks + 1;
				$user->save();
				$dashboard_history = new DashBoardHistory;
				$dashboard_history->text = $request_token['screen_name'] . ' Twitter profile account added.';
				$dashboard_history->network_status = 1;
				$dashboard_history->user_id = Auth::user()->id;
				$dashboard_history->save();
			}
			// $twitter_profile_account = TwitterProfileAccount::where('twitter_id', '=', $request_token['user_id'])->first();
			// if (!$twitter_profile_account){
			// 	$twitter_profile_account = new TwitterProfileAccount;
			// } else {
			// 	if ($twitter_profile_account->user_id != Auth::user()->id){
			// 		return Redirect::action('UsersController@getThirdParty')
			// 			->with('message', $request_token['screen_name'] . ' already signuped with other account.');
			// 	}
			// }
			
			$twitter_profile_account->user_id = Auth::user()->id;
			$twitter_profile_account->twitter_id = $request_token['user_id'];
			$twitter_profile_account->name = $request_token['screen_name'];
			$twitter_profile_account->oauth_token = $request_token['oauth_token'];
			$twitter_profile_account->oauth_token_secret = $request_token['oauth_token_secret'];
			$twitter_profile_account->is_access_token_active = 1;
			
			$twitter_profile_account->save();
			Session::forget('twitter_oauth_token');
			Session::forget('twitter_oauth_token_secret');

			return Redirect::to(URL::action('UsersController@getThirdParty') . "#twitterProfile")
				->with('message', 'Twitter account added');
		}

		return Redirect::action('TwitterProfileAccountsController@getCreate');
	}

	public function getDeleteTwitterProfile($id = null)
	{
		if ($id) {
			$twitter_profile_account = TwitterProfileAccount::
																		where('twitter_id', '=', $id)
																		->where('user_id', '=', Auth::user()->id)
																		->first();
			if ($twitter_profile_account){	
				$user = User::find(Auth::user()->id);
				$user->number_networks = $user->number_networks - 1;
				$user->number_networks = ($user->number_networks < 0) ? 0 : $user->number_networks;
				$user->save();
				$dashboard_history = new DashBoardHistory;
				$dashboard_history->text = $twitter_profile_account->name . ' Twitter profile account deleted.';
				$dashboard_history->user_id = Auth::user()->id;
				$dashboard_history->network_status = 1;
				$dashboard_history->save();		
				$twitter_profile_account->delete();
				return Redirect::action('UsersController@getThirdParty')
						->with('message', $twitter_profile_account->name . ' Twitter profile account deleted.');
			}
		}
		return Redirect::action('UsersController@getThirdParty')
			->with('err_msg', 'Twitter account could not be deleted.');
	}

}