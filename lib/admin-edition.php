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

        if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'daily-edition-new')){
            try{
                self::processEdition((int)$_POST['edition_number'], $_POST['post_order'], $_POST['post_publish']);
            }
            catch(Exception $e){
                //error message
            }
        }

        /** @global wpdb */
        global $wpdb;

        // Extract candidate posts
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'pending',
            'numberposts' => -1
        ));

        // Generating edition number
        $edition_number = $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(meta_value) FROM $wpdb->postmeta WHERE meta_key = %s;",
            'daily-edition-number'
        ) );
        $edition_number++;

        // Electing edition date
        $edition_date = isset($posts[0]) ? $posts[0]->post_date : null;
        foreach ($posts as $post){
            if ($post->post_date < $edition_date){
                $edition_date = $post->post_date;
            }
        }

        if ($edition_date !== null){
            $edition_date = mysql2date('l j F Y', $edition_date);
        }

        include dirname(__FILE__) . '/../includes/new-edition.php';
    }

    public static function processEdition($edition_number, array $post_order, array $publish){
        foreach ($post_order as $post_id => $post_order){
            if (!isset($publish[ $post_id ])){
                continue;
            }

            // just to be sure
            add_post_meta($post_id, 'daily-edition-order', (int)$post_order, true);
            update_post_meta($post_id, 'daily-edition-order', (int)$post_order);

            add_post_meta($post_id, 'daily-edition-number', (int)$edition_number, true);
            update_post_meta($post_id, 'daily-edition-number', (int)$edition_number);

            wp_publish_post($post_id);
        }
    }
}
