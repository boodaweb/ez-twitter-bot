<?php
/**
 * @author : jlchassaing <boodaweb@gmail.com>
 *
 */

namespace BWeZTwitterBotBundle\Twitter;


/**
 * Class TwitterBot
 * @package BWeZTwitterBotBundle\Twitter
 */
class TwitterBot
{
    protected $url_update = 'https://api.twitter.com/1.1/statuses/update.json';
    protected $url_search = 'https://api.twitter.com/1.1/search/tweets.json?q=%s&amp;result_type=recent&amp;count=50&amp;since_id=%s';
    protected $url_verify = 'https://api.twitter.com/1.1/account/verify_credentials.json';
    protected $url_token = 'https://twitter.com/oauth/request_token';
    protected $url_token_access = 'https://twitter.com/oauth/access_token';
    protected $url_auth = 'http://twitter.com/oauth/authorize';

    private $oauth;

    private $filePath = "var/cache/twitterbot/";

    private $data;

    private $replies = array();
    private $screenName;

    /**
     * TwitterBot constructor.
     *
     * @param $key
     * @param $secret
     */
    public function __construct( $key, $secret )
    {
        $this ->oauth = new \OAuth( $key, $secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI );
        $this->oauth->disableSSLChecks();
    }

    /**
     * @param $token
     * @param $secret
     */
    public function setToken( $token, $secret )
    {
        $this ->oauth ->setToken( $token, $secret );
    }

    public function addReply($terms,$regex,$type){
        $this->replies[] = array('terms' => $terms,'regex' => $regex,'type' => $type);
    }

    private function verifyAccountWorks(){
        try{
            $this->oauth->fetch($this->url_verify, array(), OAUTH_HTTP_METHOD_GET);
        $response = json_decode($this->oauth->getLastResponse());
        $this->screenName = $response->screen_name;
        return true;
    }catch(\Exception $ex){
            return false;
        }
    }


    public function getSinceId($file='since_id'){
        $since_id = @file_get_contents($this->filePath.$file);
        //$since_id = $this->data[$file];
        if(!$since_id){
            $since_id = 0;
        }
        return $since_id;
    }

    public function setSinceId($max_id=null,$file='since_id'){
       file_put_contents($this->filePath.$file, $max_id);
       // $this->data[$file] = $max_id;
    }

    public function run()
    {
        echo '========= ', date( 'Y-m-d g:i:s A' ), ' - Started =========' . "\n";

        $since_id = $this->getSinceId();

        $max_id = $since_id;

        if ( $this->verifyAccountWorks() )
        {
            /* For each request on tweet.php */
            foreach ( $this->replies as $key => $t )
            {
                /* find every tweet since last ID, or the maximum lasts tweets if no since_id */
                $this->oauth->fetch( sprintf( $this->url_search, urlencode( $t['terms'][0] ), $since_id ) );
                $search = json_decode( $this->oauth->getLastResponse() );
                if ( $search )
                {
                    echo 'Terms #' . $key . ' : ' . count( $search->statuses ) . ' found(s)' . "\n";
                    /* Store the last max ID */
                    if ( $search->search_metadata->max_id_str > $max_id )
                    {
                        $max_id = $search->search_metadata->max_id_str;
                    }

                    $i = 0;
                    foreach ( $search->statuses as $tweet )
                    {
                        echo '<b><a style="color: red;" href="https://twitter.com/' . $tweet->user->screen_name . '" target="_blank">@' . $tweet->user->screen_name . '</a> :</b> <a style="color: black; text-decoration: none;" href="https://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id . '" target="_blank">' . $tweet->text . '</a>';

                        /* If you are the author of the tweet, we ignore it */
                        if ( $tweet->user->screen_name == $this->screenName )
                        {
                            continue;
                        }
                        /* if tweet is a quote (like a RT), we ignore it */
                        if ( $tweet->is_quote_status )
                        {
                            continue;
                        }

                        $pass = false;

                        switch ( $t['type'] )
                        {
                            case( 'dogbot' ):
                               // echo '<b><a style="color: red;" href="https://twitter.com/'.$tweet->user->screen_name.'" target="_blank">@'.$tweet->user->screen_name.'</a> :</b> <a style="color: black; text-decoration: none;" href="https://twitter.com/'.$tweet->user->screen_name.'/status/'.$tweet->id.'" target="_blank">'.$tweet->text.'</a>";


                                $t['word'] = null; /* initialisation variable mot additionnel */

                                /* if the regex specified found something, we try to get the content */
                                if ( preg_match( $t['regex'], $tweet->text, $content ) )
                                {
                                    /* get the longest word after keyword */
                                    $words     = explode( ' ', $content[1] );
                                    $maxword   = null;
                                    $maxlength = 0;
                                    foreach ( $words as $w )
                                    {
                                        $wlength = strlen( $w );
                                        if ( $wlength >= $maxlength )
                                        {
                                            $maxword   = $w;
                                            $maxlength = $wlength;
                                        }
                                    }
                                    if ( $maxword )
                                    {
                                        $t['word'] = $maxword;
                                    }
                                }

                                $pass = true;
                                $i ++;
                                break;
                            default:
                                echo 'ERROR: NO TYPE DEFINED';
                                break;
                        }

                        if ( $pass )
                        {
                            //$this->sendReply( $tweet, $t );
                            /* wait 100ms */
                            usleep( 100000 );
                        }
                    }
echo 'Terms #'.$key.' : '.$i.' valid(s)'."\n";


                }
                echo 'Terms #' . $key . ' : ' . $i . ' valid(s)' . "\n";
            }
        }

        /* setting new max id */
        $this->setSinceId( $max_id );
        echo '========= ', date('Y-m-d g:i:s A'), ' - Finished ========='."\n";
    }


    private function sendReply($tweet, $tab, $nodie=false){
        switch($tab['type']){
            case('dogbot'):
                $m3 = [' ☺',' ☺',' ????',' ????',' ????',':)','!',';)','!'];
                $reply = 'Woof Woof ! '.($tab['word'] ? $tab['word'].' ' : '').$m3[array_rand($m3)];
                break;
            default:
                echo 'ERROR: NO TYPE DEFINED';
                die();
        }
        try{
            $this->oauth->fetch($this->url_update, array('status' => '@'.$tweet->user->screen_name.' '.$reply,'in_reply_to_status_id' => $tweet->id_str,), OAUTH_HTTP_METHOD_POST);
    }catch(\OAuthException $ex){
            echo 'ERROR: '.$ex->lastResponse;
        if(!$nodie){
            die();
        }
    }
    }

    /**
     *
     */
    public function test()
    {
        $array = array( 'status' =>'Hello World !' );
        $this ->oauth ->fetch( $this ->url_update, $array, OAUTH_HTTP_METHOD_POST);
    }
} 
