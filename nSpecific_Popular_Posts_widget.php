<?php
/*
Plugin Name: nSpecific Popular Posts Widget
Plugin URI: http://websolstore.com/nspecific-popular-posts-widget/?ref=nsp_pp_wdgt
Description: One of the best widget plugin for showing Popular Posts in Wordpress.Easy to install and use
Author: Aziz Ahmed Chouhan
Version: 1.0
Author URI: http://websolstore.com/?ref=nsp_pp_wdgt
*/

if ( ! function_exists( 'nspecific_visit_count' ) ) {
	function nspecific_visit_count()
	{
		if(is_single())
		{
			$post_id =  get_the_ID();

			$visit = get_post_meta($post_id,"nspecs_viscount",true)?
				get_post_meta($post_id,"nspecs_viscount",true):0;
			$visit = $visit + 1;	
				
			update_post_meta($post_id,"nspecs_viscount",($visit));
		}
	}
	add_action('wp_head','nspecific_visit_count');
}




class nSpecific_Popular_Posts_Widget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct() {
			// widget actual processes
			parent::__construct(
			'nSpecific_Popular_Posts_Widget', // Base ID
			'nSpecific Popular Posts', // Name
			array( 'description' => __( 'A Widget related to nSpecific Popular Posts.', 'text_domain' ), ) // Args
			);
		}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array An array of standard parameters for widgets in this theme
	 * @param array An array of settings for this widget instance
	 * @return void Echoes it's output
	 **/
	 
	function set_custom_excerpt($string, $length) {
		if (strlen($string)>$length) 
				{$dots=" ...";} 
		else 
			{$dots="";}  
			$shorttext = strip_tags($string, '');
			$shorttext = substr($shorttext, 0, $length).$dots;
		return $shorttext;
	}
	
	function string_limit_words($string, $word_limit)
	{
	  $words = explode(' ', $string, ($word_limit + 1));
	  if(count($words) > $word_limit)
	  array_pop($words);
	  return implode(' ', $words);
	}
	
	
	function widget( $args, $instance ) {

		ob_start();
		extract( $args, EXTR_SKIP );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'nSpecific Popluar Posts') : $instance['title'], $instance, $this->id_base);


		$pp_args = array(
			'post_type' => 'post',
			'meta_key' => 'nspecs_viscount',
			'meta_value' => '1',
			'meta_compare' => '>=',
			'orderby' => 'meta_value_num',
			'posts_per_page' => $instance['displayed_posts'],
			'order' => 'DESC',
			'post__not_in' => get_option( 'sticky_posts' ),
		);
		$query = new WP_Query( $pp_args );
		if ( $query->have_posts() ) :
			if($instance['post_title']==1 or $instance['post_author']==1 or 
			   $instance['post_date']==1 or $instance['post_view_count']==1 or 
			   $instance['post_thumbnail']==1 or $instance['post_excerpt'] == 1):
			echo $before_widget;
			echo $before_title;
			echo $title; // Can set this with a widget option, or omit altogether
			echo $after_title;
			?>
			<style type="text/css">
				a.post_aClass {float:left;  padding: 5px;  width: 96%; border:1px solid #fff;}
				
				a.post_aClass:hover{background-color: #E2E2E2;
								text-decoration:none;border: 1px solid #CCCCCC;}
			</style>
			<ul>
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
			
			
				<li class="widget-entry-title" style="width:100%; float:left; 
					margin-bottom: 2px;">
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="post_aClass" 
					title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), 
							the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" 
						style="">
						
					<?php 
						if($instance['post_title'] != 1)
							$post_title = "";
						else
							$post_title = get_the_title(get_the_ID()).'<br>';
							
						if($instance['post_view_count'] != 1)
							$post_views = "";
						else
							$post_views = "Viewed : ".get_post_meta(get_the_ID(),'nspecs_viscount',true).'<br>';
						
						if($instance['post_author'] != 1)
							$post_author = "";
						else
							$post_author = "Posted by : ".get_the_author().'<br>';
							
						if($instance['post_date'] != 1)
							$posted_on = "";
						else
							$posted_on = "Posted on : ".get_the_date().'<br>';
							
						if($instance['post_excerpt'] != 1)
							$post_excerpt = "";
						else
						{	
							$post_excerpt = $this->string_limit_words(get_the_content(),$instance['excerpt_length']);
							$post_excerpt .= "<br>";
						}

						$img_id = get_post_thumbnail_id();
						$img_urls = wp_get_attachment_image_src($img_id,'thumbnail',true);
						$img_url =  $img_urls[0];
						switch($instance['post_thumbnail_direction'])
						{
							case 'left': $img_direction = "float:left; margin:5px 5px 5px 0px ;";break;
							case 'right': $img_direction = "float:right; margin:5px 10px 5px 5px;";break;
							default : $img_direction = "float:none;";break;
						}

						$img_h_w = $instance['post_thumbnail_size'];
						
						if($instance['post_thumbnail'] == 1 && has_post_thumbnail(get_the_ID()) )
							$post_img = "<img src='$img_url' width='$img_h_w' height='$img_h_w' 
									style='$img_direction'>"; 
						else
							$post_img = "";
						
						$output_string = $post_img ;
						$output_string .= $post_title; 
						$output_string .= $post_excerpt;
						$output_string .= $post_views;
						$output_string .= $post_author;
						$output_string .= $posted_on; 
						
						echo $output_string ;
					?>
					</a>
				</li>
			
			<?php endwhile; ?>
			</ul>
			<?php

			echo $after_widget;

			// Reset the post globals as this query will have stomped on it
			wp_reset_postdata();

		// end check for ephemeral posts
		endif;
		endif;
		}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 **/
	function update( $new_instance, $old_instance ) {
			// processes widget options to be saved
			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['post_title'] = ( !empty( $new_instance['post_title'] ) ) ? strip_tags( $new_instance['post_title'] ) : '';
			$instance['post_author'] = ( !empty( $new_instance['post_author'] ) ) ? strip_tags( $new_instance['post_author'] ) : '';
			$instance['post_date'] = ( !empty( $new_instance['post_date'] ) ) ? strip_tags( $new_instance['post_date'] ) : '';
			$instance['post_view_count'] = ( !empty( $new_instance['post_view_count'] ) ) ? strip_tags( $new_instance['post_view_count'] ) : '';
			$instance['post_excerpt'] = ( !empty( $new_instance['post_excerpt'] ) ) ? strip_tags( $new_instance['post_excerpt'] ) : '';
			$instance['excerpt_length'] = strip_tags( $new_instance['excerpt_length'] ) ;
			
			$instance['post_thumbnail'] = ( !empty( $new_instance['post_thumbnail'] ) ) ? strip_tags( $new_instance['post_thumbnail'] ) : '';
			$instance['post_thumbnail_size'] = strip_tags( $new_instance['post_thumbnail_size'] ) ;
			$instance['post_thumbnail_direction'] = ( !empty( $new_instance['post_thumbnail_direction'] ) ) ? strip_tags( $new_instance['post_thumbnail_direction'] ) : '';
			$instance['displayed_posts'] = ( !empty( $new_instance['displayed_posts'] ) ) ? strip_tags( $new_instance['displayed_posts'] ) : 10;
			
			return $instance;
		}

	
	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 **/
	function form( $instance ) {
	
		$defaults = array(	'title' => 'Popular Posts',
							'excerpt_length' => 15,
							'post_thumbnail_size' => 80,
							'displayed_posts' => 10,
							'post_title' => 1,
							);
		
		$instance = wp_parse_args((array) $instance, $defaults);
		
		?>	
		
		<input name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="hidden" 
		value="<?php echo $instance[ 'excerpt_length' ]; ?>" />
		
		<input name="<?php echo $this->get_field_name( 'post_thumbnail_size' ); ?>" type="hidden" 
		value="<?php echo $instance[ 'post_thumbnail_size' ]; ?>" />
		
		
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance[ 'title' ]; ?>" />
		</p>
		
		<p>
			<input class="" id="<?php echo $this->get_field_id( 'post_title' ); ?>" 
			name="<?php echo $this->get_field_name( 'post_title' ); ?>" type="checkbox" 
			value="1" 
			<?php if($instance['post_title'] == 1)
					 echo 'checked="checked"';?> 
			/>
			<label for="<?php echo $this->get_field_id( 'post_title' ); ?>"><?php _e( 'Display Post Title' ); ?></label> 
		</p>
		
		<p>
			<input class="" id="<?php echo $this->get_field_id( 'post_author' ); ?>" name="<?php echo $this->get_field_name( 'post_author' ); ?>" type="checkbox" value="1" 
			<?php if($instance['post_author'] == 1)
					 echo 'checked="checked"';?> 
			/>
			<label for="<?php echo $this->get_field_id( 'post_author' ); ?>"><?php _e( 'Display Post Author' ); ?></label> 
		</p>
		
		<p>
			<input class="" id="<?php echo $this->get_field_id( 'post_date' ); ?>" name="<?php echo $this->get_field_name( 'post_date' ); ?>" type="checkbox" value="1" 
			<?php if($instance['post_date'] == 1)
					 echo 'checked="checked"';?> 
			/>
			<label for="<?php echo $this->get_field_id( 'post_date' ); ?>"><?php _e( 'Display Post Date' ); ?></label> 
		</p>
		
		<p>
			<input class="" id="<?php echo $this->get_field_id( 'post_view_count' ); ?>" name="<?php echo $this->get_field_name( 'post_view_count' ); ?>" type="checkbox" value="1" 
			<?php if($instance['post_view_count'] == 1)
					 echo 'checked="checked"';?> 
			/>
			<label for="<?php echo $this->get_field_id( 'post_view_count' ); ?>"><?php _e( 'Display View Count' ); ?></label> 
		</p>
		
		<p>
			<input class="" id="<?php echo $this->get_field_id( 'post_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'post_excerpt' ); ?>" type="checkbox" value="1" 
			<?php if($instance['post_excerpt'] == 1)
					 echo 'checked="checked"';?> 
			/>
			<label for="<?php echo $this->get_field_id( 'post_excerpt' ); ?>"><?php _e( 'Display Post Excerpt?' ); ?></label> 
		</p>
		
		<p>
			<input class="" id="<?php echo $this->get_field_id( 'displayed_posts' ); ?>" name="<?php echo $this->get_field_name( 'displayed_posts' ); ?>" type="text" value="<?php echo $instance['displayed_posts'];?>" size="2"/>
			<label for="<?php echo $this->get_field_id( 'displayed_posts' ); ?>"><?php _e( 'No. of Posts to be Displayed?' ); ?></label> 	
		</p>
		
		<p>
			<input class="" id="<?php echo $this->get_field_id( 'post_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'post_thumbnail' ); ?>" type="checkbox" value="1" 
			<?php if($instance['post_thumbnail'] == 1)
					 echo 'checked="checked"';?> 
			/>
			<label for="<?php echo $this->get_field_id( 'post_thumbnail' ); ?>"><?php _e( 'Display Post Thumbnail?' ); ?></label> 
		</p>
		
		
		<p>
			<label for="<?php echo $this->get_field_id( 'post_thumbnail_direction' ); ?>"><?php _e( 'Thumbnail Direction :' ); ?></label> 
			
			<select id="<?php echo $this->get_field_id( 'post_thumbnail_direction' ); ?>" 
					name="<?php echo $this->get_field_name( 'post_thumbnail_direction' ); ?>" >
				
				<option value="left" 
					<?php if($instance['post_thumbnail_direction'] == 'left')
					 echo 'selected="selected"';?>
				>Left</option>
				<option value="right"
					<?php if($instance['post_thumbnail_direction'] == 'right')
					 echo 'selected="selected"';?>
				>Right</option>
			</select>
			
		</p>
		<?php
	}
}

function nsppw_16_06_widget_init(){
	register_widget( 'nSpecific_Popular_Posts_Widget' );

}
add_action( 'widgets_init', 'nsppw_16_06_widget_init');
?>