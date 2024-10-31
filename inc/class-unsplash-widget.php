<?php
/*-----------------------------------------------------------------------------------*/
/*	Unsplash Widget Class
/*-----------------------------------------------------------------------------------*/

class Unsplash_Widget extends WP_Widget {

	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'unsplash_widget', 'description' => __( 'Display your Unsplash photostream', 'unsplash-widget' ) );
		$control_ops = array( 'id_base' => 'unsplash_widget' );
		parent::__construct( 'unsplash_widget', __( 'Unsplash Widget', 'unsplash-widget' ), $widget_ops, $control_ops );

		if ( !is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		}

		$this->defaults = array(
			'title' => 'Unsplash Photos',
			'id' => '',
            'access_key' => '',
			'count' => 3,
			't_width' => '100%',
			't_height' => 0,
			'randomize' => 0,
		);

		//Allow themes or plugins to modify default parameters
		$this->defaults = apply_filters( 'unsplash_widget_modify_defaults', $this->defaults );
	}

	function enqueue_styles() {
		wp_register_style( 'unsplash-widget', UNSPLASH_WIDGET_URL.'css/style.css', false, UNSPLASH_WIDGET_VER );
		wp_enqueue_style( 'unsplash-widget' );
	}


	function widget( $args, $instance ) {

		$instance = wp_parse_args( (array) $instance, $this->defaults );

		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		$photos = $this->get_photos( $instance['id'], $instance['count'], $instance['access_key'] );

		if ( !empty( $photos ) ) {

			if($instance['randomize']){
				shuffle($photos);
			}

			$height = $instance['t_height'] ? $instance['t_height'].'' : 'auto';
			$style = 'style="width: '.esc_attr( $instance['t_width'] ).'; height: '.esc_attr( $height ).';"';

			echo '<ul class="unsplash">';
			foreach ( $photos as $photo ) {
				echo '<li><a href="'.esc_url( $photo['img_url'] ).'" title="'.esc_attr( $photo['title'] ).'" target="_blank"><img src="'.esc_attr( $photo['img_src'] ).'" alt="'.esc_attr( $photo['title'] ).'" '.$style.'/></a></li>';
			}
			echo '</ul>';
			echo '<div class="clear"></div>';
		}
		echo $after_widget;
	}


	function get_photos( $id, $count = 8, $access_key = '' ) {
		if ( empty( $id ) )
			return false;

		$transient_key = md5( 'unsplash_cache_' . $id . $count );
		$cached = get_transient( $transient_key );
		if ( !empty( $cached ) ) {
			return $cached;
		}

		$output = array();
		$url = 'https://api.unsplash.com/users/'.$id.'/photos/?client_id='.$access_key;
		$contents = json_decode(wp_remote_retrieve_body( wp_remote_get( $url ) ), TRUE);

		// If $contents is not a boolean FALSE value.
		if($contents !== false) {
			foreach ( $contents as $item ) {
				$temp = array();
				$temp['img_url'] = esc_url( $item['links']['html'] );
				$temp['title'] = esc_html( $item['description'] );
				$temp['img_src'] = $item['urls']['small'];
				$output[] = $temp;
			}

            $output = array_slice($output, 0, $count);

			set_transient( $transient_key, $output, 60 * 60 * 24 );
		}

		//print_r( $output );

		return $output;
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['id'] = strip_tags( $new_instance['id'] );
        $instance['access_key'] = strip_tags( $new_instance['access_key'] );
		$instance['count'] = absint( $new_instance['count'] );
		$instance['t_width'] = $new_instance['t_width'];
		$instance['t_height'] = $new_instance['t_height'];
		$instance['randomize'] = isset( $new_instance['randomize'] ) ? 1 : 0;
		return $new_instance;
	}


	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, $this->defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'unsplash-widget' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'Unsplash Profile', 'unsplash-widget' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" type="text" value="<?php echo esc_attr( $instance['id'] ); ?>" />
			<small class="howto"><?php _e( 'Example Profile: ', 'unsplash-widget' ); ?><a href="https://unsplash.com/@orenyomtov">orenyomtov</a></small>
		</p>
        <p>
			<label for="<?php echo $this->get_field_id( 'access_key' ); ?>"><?php _e( 'Unsplash Access Key (mandatory)', 'unsplash-widget' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'access_key' ); ?>" name="<?php echo $this->get_field_name( 'access_key' ); ?>" type="text" value="<?php echo esc_attr( $instance['access_key'] ); ?>" />
			<small class="howto"><a href="https://www.odoo.com/documentation/user/13.0/general/unsplash/unsplash_access_key.html"><?php _e( 'How to generate an Unsplash access key?', 'unsplash-widget' ); ?></a></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number of photos', 'unsplash-widget' ); ?>:</label>
			<input class="small-text" type="text" value="<?php echo absint( $instance['count'] ); ?>" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 't_width' ); ?>"><?php _e( 'Thumbnail width', 'unsplash-widget' ); ?>:</label>
			<input class="small-text" type="text" value="<?php echo $instance['t_width']; ?>" id="<?php echo $this->get_field_id( 't_width' ); ?>" name="<?php echo $this->get_field_name( 't_width' ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 't_height' ); ?>"><?php _e( 'Thumbnail height', 'unsplash-widget' ); ?>:</label>
			<input class="small-text" type="text" value="<?php echo $instance['t_height']; ?>" id="<?php echo $this->get_field_id( 't_height' ); ?>" name="<?php echo $this->get_field_name( 't_height' ); ?>" />
			<small class="howto"><?php _e( 'Note: You can use "0" value for auto height', 'unsplash-widget' ); ?></small>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'randomize' ); ?>">
			<input type="checkbox" value="1" id="<?php echo $this->get_field_id( 'randomize' ); ?>" name="<?php echo $this->get_field_name( 'randomize' ); ?>" <?php checked( $instance['randomize'], 1 ); ?>/> <?php _e( 'Randomize photos?', 'unsplash-widget' ); ?>
		</label>
		</p>

		<?php
	}
}
?>
