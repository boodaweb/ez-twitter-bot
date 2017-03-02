<?php
/**
 * Created by PhpStorm.
 * User: jlchassaing
 * Date: 13/12/2016
 * Time: 09:08
 */

namespace BWeZTwitterBotBundle\Command;


use BWeZTwitterBotBundle\Twitter\TwitterBot;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;


class TwitterBotCommand extends ContainerAwareCommand
{

    private $twitterBot;

    protected function configure()
    {
        $this->setName( 'twitterbot:run' );
    }

    public function initialize( InputInterface $input, OutputInterface $output )
    {

    }

    protected function execute( InputInterface $input, OutputInterface $output )
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

    }


}
