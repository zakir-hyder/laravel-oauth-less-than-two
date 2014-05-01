<?php

class TwitterProfileAccountsController extends \BaseController {

	public function getCreate()
	{
		Session::forget('twitter_oauth_token');
		Session::forget('twitter_oauth_token_secret');
		Session::forget('twitter_request_token');

		$redirect_url = URL::action('TwitterProfileAccountsController@getSaveTwitterProfile');
		$request_token_url = "https://api.twitter.com/oauth/request_token";
		$consumer_key  = Config::get('credential.twitter_client_key');
		$consumer_secret = Config::get('credential.twitter_client_secret');
		$args = array(
			'oauth_callback' =>$redirect_url
		);
		$err_msg = '';
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
		} catch (Guzzle\Http\Exception\BadResponseException $e) {
			$err_msg = $e->getResponse();
		}
		catch (Guzzle\Http\Exception\CurlException $e) {
			$err_msg = $e->getError();
		}
		catch (Exception $e) {
			$err_msg = $e->getMessage();
		}

		if ($err_msg != '')
		{
			return Redirect::action('HomeController@showWelcome')
				->with('err_msg', 'Ooops! Something went wrong. Error:' . $err_msg);
		}

		$response_body = $response->getBody();			
		$request_token = OAuth\OAuthUtil::parse_parameters($response_body);

		if($response->getStatusCode() == 200 )
		{
			Session::put('twitter_oauth_token', $request_token['oauth_token']);
			Session::put('twitter_oauth_token_secret', $request_token['oauth_token_secret']);
			return Redirect::to("https://api.twitter.com/oauth/authorize?oauth_token={$request_token['oauth_token']}");
		}
		return Redirect::action('HomeController@showWelcome')
			->with('err_msg', 'Ooops! Something went wrong. Please check your account credentials and try adding your twitter account again.');
	}

	public function getSaveTwitterProfile()
	{
		if (Session::get('twitter_oauth_token') === Input::get('oauth_token')) 
		{
			$access_token_url = 'https://api.twitter.com/oauth/access_token';
			$consumer_key  = Config::get('credential.twitter_client_key');
			$consumer_secret = Config::get('credential.twitter_client_secret');
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

			Session::forget('twitter_oauth_token');
			Session::forget('twitter_oauth_token_secret');
			Session::put('twitter_request_token', $request_token);
			return Redirect::action('TwitterProfileAccountsController@getTwit');
		}
		return Redirect::action('HomeController@showWelcome')
			->with('err_msg', 'Ooops! Something went wrong. Please check your account credentials and try adding your twitter account again.');
	}

	public function getTwit()
	{
		if (Session::has('twitter_request_token')) 
		{
			$err_msg = $response_data = "";
			$post_url = 'https://api.twitter.com/1.1/statuses/update.json';
			$consumer_key  = Config::get('credential.twitter_client_key');
			$consumer_secret = Config::get('credential.twitter_client_secret');
			$args['status'] = "#laravel #oauth #Guzzle https://github.com/zakir-hyder/laravel-oauth-less-than-two";
			$request_token  = Session::get('twitter_request_token');

			$consumer = new OAuth\OAuthConsumer($consumer_key, $consumer_secret);
			$token = new OAuth\OAuthConsumer($request_token['oauth_token'], $request_token['oauth_token_secret']);
			$request = OAuth\OAuthRequest::from_consumer_and_token($consumer, $token,"POST", $post_url, $args);
			$request->sign_request(new OAuth\OAuthSignatureMethodHMACSHA1(), $consumer, $token);
			$url = $request->get_normalized_http_url();
			parse_str($request->to_postdata(), $post_data);
			$client = new Guzzle\Http\Client();
					
			try 
			{
				$request = $client->post($url, false, $post_data);
        $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);
				$response = $request->send();
				$response_data = $response->json();
			}
			catch (Guzzle\Http\Exception\BadResponseException $e) 
			{
				$err_msg = $e->getResponse();
			}
			catch (Guzzle\Http\Exception\CurlException $e) {
				$err_msg = $e->getError();
			}
			catch (Exception $e) {
				$err_msg = $e->getMessage();
			}
			return View::make('twit')
			->with('err_msg', $err_msg)
			->with('response', $response_data);
		}
		return Redirect::action('HomeController@showWelcome')
			->with('err_msg', 'Ooops! You forget to authorize twitter account.');
	}

}