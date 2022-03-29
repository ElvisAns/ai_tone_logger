<?php 

/**
 * Plugin Name:       AI Comment Tone Logger
 * Plugin URI:        https://elvisansima.netlify.app
 * Description:       A small Plugin help you get log of your wordpress applications
 * Version:           0.2.2
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

require_once __DIR__."/http_mapper.php";
global $wpdb;

function logger($comment_ID, $comment_approved, $commentdata)
{
    $by = "${commentdata['comment_author']}: ${commentdata['comment_author_email']}";

    $sanitized =  $commentdata['comment_content'];
    $commentdata['comment_content'] = str_replace("+", " ", $commentdata['comment_content']);
    $sanitize = strip_tags($commentdata['comment_content']);
    $sanitize = urlencode($sanitize);

    /*
        Call an IBM API intance created on my IBM account using the HTTP class mapper
    */

    $request  = new httpMapper("https://api.eu-gb.tone-analyzer.watson.cloud.ibm.com/instances/e86ba83c-8f91-4b33-bb22-d5bd95e59c13/v3/tone",["version"=>"2017-09-21","text"=>$commentdata["comment_content"]]);

    $request->setup();
    $headers = ["Authorization: Basic YXBpa2V5Olc1OWd2Q25kVXd0UlQ2YUhsTXIyOGloNko1RmxKa2QzWXVycUlITHNFbE1L"];
    $request->auth($headers);
    $raw = $request->get_response("array"); //get response as an array
    if(empty($raw["document_tone"]['tones'])){
        return; //for comment where tone detection caused a little problem
    }

    $feedback = "Your comment seems to be more related to :".$raw["document_tone"]["tones"][0]["tone_name"];
    $file = fopen("./comments.log", "a");
    $str = "${commentdata['comment_date']} >>>";

    $str .= "-> Comment Posted (comment id ${comment_ID}, by ${by}) ... Content: ".$sanitized.", Tone: ".$raw["document_tone"]["tones"][0]["tone_name"]."\n\r\n";
    fwrite($file, $str);
    fclose($file);

    $GLOBALS['wpdb']->update( $GLOBALS['wpdb']->comments, array("comment_content" => "${sanitized} :".$raw["document_tone"]["tones"][0]["tone_name"]), array("comment_ID" => $comment_ID), array("%s"), array("%d") );//insert emoji after the comment
 
}

add_action('comment_post','logger',10,3); //



?>