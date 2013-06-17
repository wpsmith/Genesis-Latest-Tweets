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
function gltw_tweet_linkify( $text ) {

	$text = preg_replace( "#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", '\\1<a href="\\2" target="_blank">\\2</a>', $text );
	$text = preg_replace( "#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", '\\1<a href="http://\\2" target="_blank">\\2</a>', $text );
	$text = preg_replace( '/@(\w+)/', '<a href="http://www.twitter.com/\\1" target="_blank">@\\1</a>', $text );
	$text = preg_replace( '/#(\w+)/', '<a href="http://search.twitter.com/search?q=\\1" target="_blank">#\\1</a>', $text );

	return $text;

}
