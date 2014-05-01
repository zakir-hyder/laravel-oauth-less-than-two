## Laravel OAuth Less Than Two

This app uses https://github.com/zakir-hyder/oauth-1-lib and https://github.com/guzzle/guzzle. You can get details about this here http://blog.jambura.com/2014/04/26/add-your-own-github-library-in-laravel-using-composer/

All the app keys and secrets is in .env.local.php as mentioned in http://laravel.com/docs/configuration#environment-configuration. So you have add those details in .env.local.php file. .env.local.php is not added in this repo, so you have to create this file.

For twitter we will add app key and secret from https://apps.twitter.com/app/your_app_id/keys. 

    return array(
    	'twitter_client_key'     => '',
      'twitter_client_secret' => '',
    );

Then these values are added credential.php

    return array(
    	'twitter_client_key'     => $_ENV['twitter_client_key'],
      'twitter_client_secret' => $_ENV['twitter_client_secret'],
      'twitter_base_url' => 'https://api.twitter.com/oauth/',
    );

###Twitter
Checkout this post for how the app is posting on Twitter.     

## Contact

Follow [@jambura.blog](https://www.facebook.com/jambura.blog) on Facebook 

### Creators

[Zakir Hyder](https://github.com/zakir-hyder)  

## License

Laravel OAuth Less Than Two is available under the MIT license. See the LICENSE file for more info.    