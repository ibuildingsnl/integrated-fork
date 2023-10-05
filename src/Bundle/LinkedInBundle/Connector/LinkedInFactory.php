<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use Abraham\TwitterOAuth\TwitterOAuth;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use League\OAuth2\Client\Provider\LinkedIn;

final class LinkedInFactory
{
    public function __construct(
       private readonly string $key,
       private readonly string $secret,
    ) {}

    public function createClient(?string $token = null, ?string $secret = null): LinkedIn
    {
        $provider = new LinkedIn([
            'clientId'          => '78472da81cu8sw',
            'clientSecret'      => 'UdCakJSXcDMM5q3q',
            'redirectUri'       => 'https://integrated.localhost.e-active.nl/admin/media/authorization_result',
        ]);

        return $provider;

        if (!isset($_GET['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl($options);
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

            // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            exit('Invalid state');

        } else {

            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Optional: Now you have a token you can look up a users profile data
            try {

                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);

                // Use these details to create a new profile
                printf('Hello %s!', $user->getFirstName());

            } catch (Exception $e) {

                // Failed to get user details
                exit('Oh dear...');
            }

            // Use this to interact with an API on the users behalf
            echo $token->getToken();
        }

        $client = new Client();

        $headers = [
            'LinkedIn-Version' => '202309',
            'X-Restli-Protocol-Version' => '2.0.0',
            'Content-Type' => 'text/plain',
            'Authorization' => 'Bearer AQXXDUQZPke4p9ZKAPufRVAWiXcGNWk8OcDXBFrZ61SgCQFqr5L-OpfIorIxzRUn0A2FVzuDUiUgkPAsFdaKqvcZ8LxgcnB-eJ1Dzfgb-0Mg6BROBmGLenH1eMpJcgF7ult52uE7Fu33PRTkdgrTv4OBxlGbOz-eEssDTC7LjgqC1APqc-4TNWY3K2MEH8i7BrAHbeOVRyI8hZLzGUPIoiWVXoIsrHG96Sx3vKYdq_pHl5qU7rSCrfDmfFlWFgGUKbPXbvJ2lqeteKTS7szn4AXsnkxHEE2_WiJFS4RR7tv4Jobjj-c9SOFigRT1lJBwq1RdXCcygeHpm3QerdbdwagaLyaZ0w',
            'Cookie' => 'lidc="b=TB74:s=T:r=T:a=T:p=T:g=3873:u=246:x=1:i=1696253933:t=1696335588:v=2:sig=AQEXD88VnyHqy_viJAtYHJ8KTJUl3teJ"; lidc="b=TB74:s=T:r=T:a=T:p=T:g=3878:u=248:x=1:i=1696404063:t=1696487562:v=2:sig=AQGWyk189Wd1cZaJcPTFnoDJPNX8moEn"; bcookie="v=2&23a437ae-3da8-47c3-8018-cbe1c396531b"'
        ];

        $json_body = '{
          "author": "urn:li:organization:98903555",
          "commentary": "At Mettao, we\'re always on the lookout for innovation. 🌟In the upcoming weeks, we\'re diving headfirst into small projects to explore the potential of new frameworks. 💡Stay tuned as we share our progress and discoveries! 🚀 #MettaoInnovation #NewFrameworks #Exploration Best Regards, Mettao",
          "visibility": "LOGGED_IN",
          "distribution": {
            "feedDistribution": "MAIN_FEED",
            "targetEntities": [],
            "thirdPartyDistributionChannels": []
          },
          "lifecycleState": "PUBLISHED",
          "isReshareDisabledByAuthor": false
        }';

        $body = [
            "author" => "urn:li:organization:98903555",
            "commentary" => "At Mettao, we're always on the lookout for innovation. 🌟In the upcoming weeks, we're diving headfirst into small projects to explore the potential of new frameworks. 💡Stay tuned as we share our progress and discoveries! 🚀 #MettaoInnovation #NewFrameworks #Exploration Best Regards, Mettao",
            "visibility" => "LOGGED_IN",
            "distribution" => array(
                "feedDistribution" => "MAIN_FEED",
                "targetEntities" => array(),
                "thirdPartyDistributionChannels" => array()
            ),
            "lifecycleState" => "PUBLISHED",
            "isReshareDisabledByAuthor" => false
        ];

        $request = new Request('POST', 'https://api.linkedin.com/rest/posts', $headers, $body);
        $res = $client->sendAsync($request)->wait();
        echo $res->getBody();

//        $linkedin = new TwitterOAuth($this->key, $this->secret, $token, $secret);
//        $linkedin->setApiVersion('2');
//        $linkedin->setDecodeJsonAsArray(true);
        return $linkedin;
    }
}
