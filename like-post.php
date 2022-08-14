<?php
/*
Plugin Name: Like Post
Description: Post Likes Plugin
Version:     1.0
Author:      Mobashir
Author URI:  https://plugin-devs.com
Text Domain: like-post
*/
?>
<?php

//Create Essential Tables On Install Plugin
function lp_create_table(){
	global $wpdb;
	$charset_collation = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix.'like_post';

	if( $wpdb->get_var("show tables like '$table_name'") != $table_name){
		$sql = "CREATE TABLE $table_name (
					ID 				INT(9)								NOT NULL AUTO_INCREMENT,
					POST_ID			INT(9) 								NOT NULL,
					STATUS 			INT(1) 								NOT NULL,
					LIKE_TIME		DATE								NOT NULL,
					PRIMARY KEY  (ID)
				) $charset_collation;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}
	add_option('lp_showing_year','');

}

register_activation_hook( __FILE__, 'lp_create_table' );

//Delete Plugins data on Delete Plugin
function lp_delete_table(){
	global $wpdb;
	$charset_collation = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix.'like_post';

    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);

    delete_option('lp_showing_year');
}
register_uninstall_hook(__FILE__, 'lp_delete_table');


//Create Menu Page 
function lp_menu_page_create(){

	if ( empty ( $GLOBALS['admin_page_hooks']['lp_menu_page'] ) ){
		add_menu_page(
			__( 'POST LIKE' , 'wordpress-team-member' ),
			'POST LIKE',
			'manage_options',
			'lp_menu_page',
			'lp_create_menupage',
			'dashicons-thumbs-up',
			60
		);
	}

}

//callback function for create menupage
function lp_create_menupage(){
?>
	<div class="wrap">
		<h1>Wordpress LIKE POST</h1>
		
		<form name="lp_option_form" action="" method="post">
		<?php
			if(isset($_POST['submit'])){
				$showing_year = $_POST['select_time'];
				update_option('lp_showing_year',$showing_year);
			}
			$showing_year = get_option('lp_showing_year');

		?>
		<h2 class="title">Plugin Settings</h2>
		<table class="form-table permalink-structure">
			<tbody>
				<tr>
					<th>
						<label for="select_time">Select Total Times You Want to show</label>
					</th>
					<td>
						<select id="select_time" name="select_time">
							<option value="" <?php if($showing_year==''){ echo "selected"; } ?> >All</option>
							<option value="7" <?php if($showing_year==7){ echo "selected"; } ?> >One Week</option>
							<option value="30" <?php if($showing_year==30){ echo "selected"; } ?> >One Month</option>
							<option value="365" <?php if($showing_year==365){ echo "selected"; } ?>>One Year</option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button(); ?></p>
	</div>
<?php
}

add_action( 'admin_menu', 'lp_menu_page_create' );


//Enqueue Scripts and Styles
function tm_mbr_add_jquery(){
	//wp_enqueue_style('team-member-style', plugin_dir_url( __FILE__ ).'css/team-member-style.css', array(), 1.0 );

	wp_enqueue_script( 'jquery' );

	if(is_admin()){
		wp_enqueue_script( 'lp_admin_js', plugin_dir_url( __FILE__ ).'js/admin.js', array(), 1.0, true );
		wp_localize_script( 'lp_admin_js', 'lp_admin_ajax', array(
			'ajaxurl'=>admin_url( 'admin-ajax.php' ),
		));
	}else{
		wp_enqueue_script( 'lp_front_js', plugin_dir_url( __FILE__ ).'js/front-end.js', array(), 1.0, true );
		wp_localize_script( 'lp_front_js', 'lp_front_ajax', array(
			'ajaxurl'=>admin_url( 'admin-ajax.php' ),
		));
	}
}
add_action('wp_enqueue_scripts', 'tm_mbr_add_jquery');
add_action('admin_enqueue_scripts', 'tm_mbr_add_jquery');


//Create Shortcode for showing Like-Unlike Option In frontend
function lp_show_frontend( $attr, $content='' ){
	$attributes = shortcode_atts(array(
					'post_id'	=> get_the_ID()
				),$attr);
	global $wpdb;
	$table_name = $wpdb->prefix.'like_post';
	$id = $attributes['post_id'];
	$cookie_like = 'lp_'.$id;
	if(!isset($_COOKIE[$cookie_like])){
		$lp_like_text = 'Like';
	}else{
		$lp_like_text = 'LIKED';
	}
	$selected_time = get_option('lp_showing_year');
	$selected_date_time = date('Y-m-d', strtotime("-$selected_time days"));
	$like_time = date('Y-m-d',time());
	$wpdb->show_errors( true );
	$total_like = $wpdb->get_var("SELECT count(POST_ID) FROM $table_name WHERE POST_ID = $id AND STATUS = 1 AND LIKE_TIME BETWEEN '$selected_date_time' AND '$like_time' ");

	$output  = "<p class='li_like'>";
	$output .= "<a class='lp_like_btn' href='javascript:void(0)' data-total-like='".$total_like."' data-post-id='".$id."' >".$lp_like_text."</a> ";
	$output .= "<span class='lp_like_count'><strong>".$total_like."</strong> Like</span>";
	$output .= "</p>";
	return $output;
}
add_shortcode('like_post','lp_show_frontend');
global $wpdb;


//function For Increment Like
function lp_increment_like(){
	global $wpdb;
	$table_name = $wpdb->prefix.'like_post';
	$id = $_POST['post_id'];
	$like_time = date('Y-m-d',time());
	$selected_time = get_option('lp_showing_year');
	$selected_date_time = date('Y-m-d', strtotime("-$selected_time days"));
	$wpdb->show_errors( true );
	$insert_like = $wpdb->insert(
						$table_name,
						array(
							'POST_ID'	=>	$id,
							'STATUS'	=>	1,
							'LIKE_TIME' =>	$like_time
						),
						array(
							'%d',
							'%d',
							'%s'
						)
					);
	$total_like = $wpdb->get_var("SELECT count(POST_ID) FROM $table_name WHERE POST_ID = $id AND STATUS = 1 AND LIKE_TIME BETWEEN '$selected_date_time' AND '$like_time' ");

	echo $total_like;

	wp_die();
}
add_action( 'wp_ajax_nopriv_increment_like', 'lp_increment_like' );
add_action('wp_ajax_increment_like', 'lp_increment_like');


//Save Plugins essential Option's values

require_once( plugin_dir_path( __FILE__ ).'/admin/show-in-post.php' );



