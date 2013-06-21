<?php
/**
 * Adds the Latest tweets widget.
 *
 * @category Genesis_Twitter
 * @package  Widgets
 * @author   Travis Smith, for StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 */

/**
 * Genesis Latest Tweets widget class.
 *
 * @category Genesis
 * @package Widgets
 *
 * @since 1.1.0
 */
class Genesis_Official_Twitter_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 0.1.8
	 */
	function __construct() {

		$this->defaults = array(
			'title'                => '',
			'twitter_id'           => '',
			'twitter_num'          => '',
			'twitter_duration'     => '',
			'twitter_hide_replies' => 0,
			'follow_link_show'     => 0,
			'follow_link_text'     => '',
		);

		$widget_ops = array(
			'classname'   => 'latest-tweets',
			'description' => __( 'Display a list of your latest tweets.', GLTW_DOMAIN ),
		);

		$control_ops = array(
			'id_base' => 'latest-tweets',
			'width'   => 200,
			'height'  => 250,
		);

		$this->WP_Widget( 'latest-tweets', __( 'Genesis Twitter Widget', GLTW_DOMAIN ), $widget_ops, $control_ops );

	}

	/**
	 * Echo the widget content.
	 *
	 * @since 0.1.8
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {
		extract( gltw_api_config() );
		if ( empty( $consumer_key ) && empty( $consumer_secret ) && empty( $access_key ) && empty( $access_secret ) ) return;
		
		extract( $args );

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $before_widget;

		if ( $instance['title'] )
			echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

		echo '<ul>' . "\n";

		$key = $instance['twitter_id'] . '-' . $instance['twitter_num'] . '-' . $instance['twitter_duration'];
		$tweets = get_transient( $key );
		
		$screen_name = $instance['twitter_id'];

		if ( ! $tweets ) {
			
			global $GLTW_API;
		
			// We could cache the rendered HTML right here, but this keeps caching abstracted in library
			$GLTW_API->api_enable_cache( absint( $instance['twitter_duration'] ) * 60 );
			
			// Build API params for "statuses/user_timeline" // https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
			$screen_name     = $instance['twitter_id'];
			$trim_user       = true;
			$include_rts     = ! empty($instance['twitter_include_retweets']);
			$exclude_replies = ! empty($instance['twitter_hide_replies']);
			$count           = isset( $instance['twitter_hide_replies'] ) ? (int) $instance['twitter_num'] * 3 : (int) $instance['twitter_num'];
			$params          = compact('count','exclude_replies','include_rts','trim_user','screen_name');
			$tweets = $GLTW_API->api_get('statuses/user_timeline', $params );
			
			if( isset($tweets[$count]) )
				$tweets = array_slice( $tweets, 0, $count );

			$time = ( absint( $instance['twitter_duration'] ) * 60 );

			/** Save them in transient */
			set_transient( $instance['twitter_id'].'-'.$instance['twitter_num'].'-'.$instance['twitter_duration'], $tweets, $time );
			
		}
		
		if ( $instance['follow_link_show'] && $instance['follow_link_text'] )
				$follow_link = '<li class="last"><a href="' . esc_url( 'http://twitter.com/'.$instance['twitter_id'] ).'">'. esc_html( $instance['follow_link_text'] ) .'</a></li>';
		
		$tweet_count = 0;
		
		if ( ! empty( $tweets ) ) {
			foreach( (array) $tweets as $tweet ){
						if ( $tweet_count >= (int)$instance['twitter_num'] )
							break;
				extract( $tweet );
				
				$link = esc_html( 'http://twitter.com/'.$screen_name.'/status/'.$id_str);
				
				// render nice datetime, unless theme overrides with filter
				$date = apply_filters( 'glt_render_date', $created_at );
				if( $date === $created_at ){
					$date = esc_html( gltw_tweet_relative_date($created_at) );
					$date = '<span style="font-size: 85%;">'.$date.'</span>';
				}
				
				// render and linkify tweet, unless theme overrides with filter
				$html = apply_filters('glt_render_text', $text );
				if( $html === $text ){
					$html = gltw_tweet_linkify( $text );
				}
				
				// piece together the whole tweet, allowing overide
				$final = apply_filters('glt_render_tweet', $html, $date, $link );
				if( $final === $html ){
					$final = '<li><span class="tweet-text">'.$html.'</span>'.
							 ' <span class="tweet-details"><a href="'.$link.'" target="_blank" rel="nofollow">'.$date.'</a></span></li>';
				}
				
				echo $final;
				
				$tweet_count++;
			}
		} else {
			echo apply_filters( 'glt_no_tweets', __( 'No tweets found.', GLTW_DOMAIN ) );
		}
		
		echo $follow_link;

		echo '</ul>' . "\n";

		echo $after_widget;

	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @since 0.1.8
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {

		/** Force the transient to refresh */
		delete_transient( $old_instance['twitter_id'].'-'.$old_instance['twitter_num'].'-'.$old_instance['twitter_duration'] );
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		return $new_instance;

	}

	/**
	 * Echo the settings update form.
	 *
	 * @since 0.1.8
	 *
	 * @param array $instance Current settings
	 */
	function form( $instance ) {
		extract( gltw_api_config() );

		if ( empty( $consumer_key ) && empty( $consumer_secret ) && empty( $access_key ) && empty( $access_secret ) ) {
			printf(
				'<%1$s>%2$s<a href="%3$s">%4$s</a>.</%1$s>',
				'p',
				__( 'Please set the ', GLTW_DOMAIN ),
				admin_url( 'admin.php?page=genesis-twitter-widget-settings' ),
				__( 'Genesis Twitter Widget Settings', GLTW_DOMAIN )
			);
			return;
		}
		
		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', GLTW_DOMAIN ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_id' ); ?>"><?php _e( 'Twitter Username', GLTW_DOMAIN ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'twitter_id' ); ?>" name="<?php echo $this->get_field_name( 'twitter_id' ); ?>" value="<?php echo esc_attr( $instance['twitter_id'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_num' ); ?>"><?php _e( 'Number of Tweets to Show', GLTW_DOMAIN ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'twitter_num' ); ?>" name="<?php echo $this->get_field_name( 'twitter_num' ); ?>" value="<?php echo esc_attr( $instance['twitter_num'] ); ?>" size="3" />
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'twitter_hide_replies' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'twitter_hide_replies' ); ?>" value="1" <?php checked( $instance['twitter_hide_replies'] ); ?>/>
			<label for="<?php echo $this->get_field_id( 'twitter_hide_replies' ); ?>"><?php _e( 'Hide @ Replies', GLTW_DOMAIN ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_duration' ); ?>"><?php _e( 'Load new Tweets every', GLTW_DOMAIN ); ?></label>
			<select name="<?php echo $this->get_field_name( 'twitter_duration' ); ?>" id="<?php echo $this->get_field_id( 'twitter_duration' ); ?>">
				<option value="5" <?php selected( 5, $instance['twitter_duration'] ); ?>><?php _e( '5 Min.' , GLTW_DOMAIN ); ?></option>
				<option value="15" <?php selected( 15, $instance['twitter_duration'] ); ?>><?php _e( '15 Minutes' , GLTW_DOMAIN ); ?></option>
				<option value="30" <?php selected( 30, $instance['twitter_duration'] ); ?>><?php _e( '30 Minutes' , GLTW_DOMAIN ); ?></option>
				<option value="60" <?php selected( 60, $instance['twitter_duration'] ); ?>><?php _e( '1 Hour' , GLTW_DOMAIN ); ?></option>
				<option value="120" <?php selected( 120, $instance['twitter_duration'] ); ?>><?php _e( '2 Hours' , GLTW_DOMAIN ); ?></option>
				<option value="240" <?php selected( 240, $instance['twitter_duration'] ); ?>><?php _e( '4 Hours' , GLTW_DOMAIN ); ?></option>
				<option value="720" <?php selected( 720, $instance['twitter_duration'] ); ?>><?php _e( '12 Hours' , GLTW_DOMAIN ); ?></option>
				<option value="1440" <?php selected( 1440, $instance['twitter_duration'] ); ?>><?php _e( '24 Hours' , GLTW_DOMAIN ); ?></option>
			</select>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'follow_link_show' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'follow_link_show' ); ?>" value="1" <?php checked( $instance['follow_link_show'] ); ?>/>
			<label for="<?php echo $this->get_field_id( 'follow_link_show' ); ?>"><?php _e( 'Include link to twitter page?', GLTW_DOMAIN ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'follow_link_text' ); ?>"><?php _e( 'Link Text (required)', GLTW_DOMAIN ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'follow_link_text' ); ?>" name="<?php echo $this->get_field_name( 'follow_link_text' ); ?>" value="<?php echo esc_attr( $instance['follow_link_text'] ); ?>" class="widefat" />
		</p>
		<?php

	}

}
