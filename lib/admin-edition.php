<?php

namespace DailyEdition\Admin;

class Edition
{
    public static function register(){
        $page = \add_posts_page(
            __('Manage Editions', 'daily-edition'),
            __('Manage Editions', 'daily-edition'),
            'publish_posts',
            'editions',
            array('\DailyEdition\Admin\Edition', 'ManagementPage')
        );

        add_action('admin_print_scripts-'.$page, array('\DailyEdition\Admin\Edition', 'registerScripts'));
    }

    public static function registerScripts(){
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-sortable');
    }

    public static function ManagementPage(){
        if ( !current_user_can( 'publish_posts' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        // Extract candidate posts
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'pending',
            'numberposts' => -1
        ));

        include dirname(__FILE__) . '/../includes/new-edition.php';
    }
}
