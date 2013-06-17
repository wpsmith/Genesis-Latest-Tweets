<?php
/**
 * Genesis Twitter functions.
 *
 * @category Genesis_Twitter
 * @package  Functions
 * @author   Travis Smith, for StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 */

/**
 * Adds links to the contents of a tweet.
 *
 * Takes the content of a tweet, detects @replies, #hashtags, and
 * http:// links, and links them appropriately.
 *
 * @since 1.0.0
 *
 * @link http://www.snipe.net/2009/09/php-twitter-clickable-links/
 *
 * @param string $text A string representing the content of a tweet
 *
 * @return string Linkified tweet content
 */
function gtw_tweet_linkify( $text ) {

	$text = preg_replace( "#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", '\\1<a href="\\2" target="_blank">\\2</a>', $text );
	$text = preg_replace( "#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", '\\1<a href="http://\\2" target="_blank">\\2</a>', $text );
	$text = preg_replace( '/@(\w+)/', '<a href="http://www.twitter.com/\\1" target="_blank">@\\1</a>', $text );
	$text = preg_replace( '/#(\w+)/', '<a href="http://search.twitter.com/search?q=\\1" target="_blank">#\\1</a>', $text );

	return $text;

}


/**
 * Utility converts the date [of a tweet] to relative time descriprion, e.g. about 2 minutes ago
 * 
 */
 //*
function gtw_tweet_relative_date( $strdate ){
    // get universal time now.
    static $t, $y, $m, $d, $h, $i, $s, $o;
    if( ! isset($t) ){
        $t = time();
        sscanf(gmdate('Y m d H i s',$t), '%u %u %u %u %u %u', $y,$m,$d,$h,$i,$s);
    }
    // get universal time of tweet
    $tt = is_int($strdate) ? $strdate : strtotime($strdate);
    if( ! $tt || $tt > $t ){
        // slight difference between our clock and Twitter's clock can cause problem here - just pretend it was zero seconds ago
        $tt = $t;
        $tdiff = 0;
    }
    else {
        sscanf(gmdate('Y m d H i s',$tt), '%u %u %u %u %u %u', $yy,$mm,$dd,$hh,$ii,$ss);
        // Calculate relative date string
        $tdiff = $t - $tt;
    }
    // Less than a minute ago?
    if( $tdiff < 60 ){
        return __('Just now', 'genesis-latest-tweets');
    }
    // within last hour? X minutes ago
    if( $tdiff < 3600 ){
        $idiff = (int) floor( $tdiff / 60 );
        return sprintf( _n( '1 minute ago', '%u minutes ago', $idiff, 'genesis-latest-tweets' ), $idiff );
    }
    // within same day? About X hours ago
    $samey = ($y === $yy) and
    $samem = ($m === $mm) and
    $samed = ($d === $dd);
    if( ! empty($samed) ){
        $hdiff = (int) floor( $tdiff / 3600 );
        return sprintf( _n( 'About an hour ago', 'About %u hours ago', $hdiff, 'genesis-latest-tweets' ), $hdiff );
    }
    $tf = get_option('time_format') or $tf = 'g:i A';
    // within 24 hours?
    if( $tdiff < 86400 ){
        return __('Yesterday at', 'genesis-latest-tweets').date_i18n(' '.$tf, $tt );
    }
    // else return formatted date, e.g. "Oct 20th 2008 9:27 PM" 
    $df = get_option('date_format') or $df= 'M jS Y'; 
    return date_i18n( $df.' '.$tf, $tt );
}
/**/