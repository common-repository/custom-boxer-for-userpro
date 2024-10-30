<?php

/*

Plugin name: Custom boxer for UserPro
Description: This is a plugin will help you to customize boxers of UserPro plugin
Author: Md. Sarwar-A-Kawsar
Author URI: https://fiverr.com/sa_kawsar
Version: 1.0

*/

defined('ABSPATH') or die('You cannot access to this page');
function cb_up_activate(){
	add_role( 'custom_boxer', 'Boxer', array('read' => true));
	add_role( 'custom_matchmaker', 'Matchmaker', array('read' => true));
	add_role( 'custom_promoter', 'Promoter', array('read' => true));
	add_role( 'custom_manager', 'Manager', array('read' => true));
	flush_rewrite_rules( $hard = true );
}
register_activation_hook( __FILE__, 'cb_up_activate' );

function cb_up_additional_field($form,$user_id){
    if(isset($form['cpb_i_am_active']) && $form['cpb_i_am_active']):
        update_user_meta($user_id,'last_activation_date',current_time( 'mysql' ));
    else:
        update_user_meta($user_id,'last_activation_date',false);
    endif;
}
add_action('userpro_profile_update','cb_up_additional_field',10,2);

function cb_up_active_badge( $user_id ){
    $user = get_userdata( $user_id );
    $roles = $user->roles;
    if(in_array('custom_boxer', $roles)):
    	if(get_user_meta( $user_id, 'cpb_i_am_active', true )):
    		echo esc_html( '<button style="border: 1px solid green;border-radius: 4px;margin: 4px 0px!important;padding: 4px!important;color: green;">Active</button>' );
    	else:
    		echo esc_html( '<button style="border: 1px solid gray;border-radius: 4px;margin: 4px 0px!important;padding: 4px!important;color: gray;">Inactive</button>' );
    	endif;
    endif;
}
add_action('userpro_after_profile_img','cb_up_active_badge');

function cb_up_cron(){
    $user_query = new WP_User_Query( array( 'role' => 'custom_boxer' ) );
    $users = $user_query->get_results();
    foreach ($users as $user):
        $user_id = $user->ID;
        if(get_user_meta($user_id,'cpb_i_am_active',true)):
            $activation_date = get_user_meta($user_id,'last_activation_date',true);
            if($activation_date):
                $ext_date = strtotime($activation_date.' + 15 days');
                $cur_date = current_time( 'timestamp' );
                if($cur_date >= $ext_date):
                    update_user_meta($user_id,'cpb_i_am_active',false);
                    update_user_meta($user_id,'last_activation_date',false);
                endif;
            else:
                update_user_meta($user_id,'cpb_i_am_active',false);
                update_user_meta($user_id,'last_activation_date',false);
            endif;
        endif;
    endforeach;
    update_user_meta( get_current_user_id(), 'test_var', $users );
    if(is_user_logged_in()):
        $user = get_userdata(get_current_user_id());
        $roles = $user->roles;
        if(!in_array('custom_boxer', $roles)):
            add_action( 'wp_footer', 'cb_up_custom_footer_script' );
        endif;
    endif;
}
add_action('init','cb_up_cron');

function cb_up_custom_footer_script(){
    ?>
    <script type="text/javascript">
        var cbp_userpro_section = document.getElementsByClassName('userpro-section');
        cbp_userpro_section[1].style.display = 'none';
    </script>
    <?php
}