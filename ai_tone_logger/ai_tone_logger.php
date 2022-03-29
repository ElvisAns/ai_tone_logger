<?php 

/**
 * Plugin Name:       AI Comment Tone Logger
 * Plugin URI:        https://elvisansima.netlify.app
 * Description:       A small Plugin help you get log of your wordpress applications
 * Version:           0.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            ANSIMA Elvis
 * Author URI:        https://elvisansima.netlify.app
 * Text Domain:       Plugin for Job application
 * Domain Path:       /languages
 */

//=================================================
// Security: Abort if this file is called directly
//=================================================
if ( !defined('ABSPATH') ) { 
    die;
}


function logger($comment_ID, $comment_approved, $commentdata)
{
    $by = "${commentdata['comment_author']}: ${commentdata['comment_author_email']}";
    $commentdata['comment_content'] = str_replace("+", " ", $commentdata['comment_content']);
    $sanitize = strip_tags($commentdata['comment_content']);
    $sanitize = urlencode($sanitize);

    /*
        Call an IBM API intance created on my IBM account
    */

    $url = "https://api.eu-gb.tone-analyzer.watson.cloud.ibm.com/instances/e86ba83c-8f91-4b33-bb22-d5bd95e59c13/v3/tone?version=2017-09-21&text=${sanitize}";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
       "Authorization: Basic YXBpa2V5Olc1OWd2Q25kVXd0UlQ2YUhsTXIyOGloNko1RmxKa2QzWXVycUlITHNFbE1L",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);


    $raw = json_decode($resp,true);

    if(empty($raw["document_tone"]['tones'])){
        return; //for comment where tone detection caused a little problem
    }

    $feedback = "Your comment seems to be more related to :".$raw["document_tone"]["tones"][0]["tone_name"];
    $file = fopen("./comments.log", "a");
    $str = "${commentdata['comment_date']} >>>";

    $str .= "-> Comment Posted (comment id ${comment_ID}, by ${by}) ... Content: ".strip_tags($sanitize).", Tone: ".$raw["document_tone"]["tones"][0]["tone_name"]."\n\r\n";
    fwrite($file, $str);
    fclose($file);
 
}

add_action('comment_post','logger',10,3); //



?>