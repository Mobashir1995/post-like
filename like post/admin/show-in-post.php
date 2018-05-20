<?php
function lp_show_metabox(){
    $lp_args = array(
       'public'   => true,
       '_builtin' => false
    );

    $lp_output = 'names'; // names or objects, note names is the default
    $lp_operator = 'and'; // 'and' or 'or'

    $lp_post_types = get_post_types( $lp_args, $lp_output, $lp_operator ); 
    $lp_all_post_types[] = 'post';

    foreach ( $lp_post_types  as $lp_post_type ) {
       array_push($lp_all_post_types, $lp_post_type);
    }

    foreach ($lp_all_post_types as $key => $value) {
        add_action( "add_meta_boxes_$value", function(){
                add_meta_box( 
                'lp-total-like',
                __( 'Total Post Like' ),
                'lp_total_post_like',
                "$value",
                'normal',
                'default'
            );
        });
    }

    function lp_total_post_like($post){
    ?>  
        <table>
            <tr>
                <td>Total Post Like: &nbsp; &nbsp; &nbsp; &nbsp;</td>
                <td><?php echo lp_admin_show_total_like($post->ID); ?></td>
            <tr>
        <table>
    <?php
    }

}
add_action('admin_init','lp_show_metabox');


function lp_admin_show_total_like($id){
    global $wpdb;
    $table_name = $wpdb->prefix.'like_post';
    $like_time = date('Y-m-d',time());
    $selected_time = get_option('lp_showing_year');
    $selected_date_time = date('Y-m-d', strtotime("-$selected_time days"));
    $wpdb->show_errors( true );

    $total_like = $wpdb->get_var("SELECT count(POST_ID) FROM $table_name WHERE POST_ID = $id AND STATUS = 1 AND LIKE_TIME BETWEEN '$selected_date_time' AND '$like_time' ");
    return $total_like;
}
?>