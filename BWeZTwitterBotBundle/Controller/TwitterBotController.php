<?php
/**
 * Created by PhpStorm.
 * User: jlchassaing
 * Date: 24/02/2017
 * Time: 16:20
 */

namespace BWeZTwitterBotBundle\Controller;


use BWeZTwitterBotBundle\Twitter\TwitterBot;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TwitterBotController extends Controller
{

    public function runAction(Request $request)
    {
        date_default_timezone_set( 'GMT' );

        $consumerKey = "";
        $consumerSecret = "";
        $accessToken = "";
        $accessTokenSecret = "";


        header( 'Content-Type: text/html; charset=utf-8' );
        $twitter = new TwitterBot( $consumerKey, $consumerSecret );
        $twitter->setToken( $accessToken, $accessTokenSecret );
        // $twitter->test();

        $twitter->addReply(array('%22c%27est ouf%22 -RT'),"~c'est ouf (.*)~i","dogbot");
        $twitter->run();

        die();
    }

}
