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

        /** @global wpdb */
        global $wpdb;
        $editions = array();
        $posts = array();
        $unpublished = array();
        $edition_date = null;
        $edition_number = null;
        $edition_id = null;

        // Electing edition date
        list($edition_number, $edition_date) = self::getEdition((int)$_GET['edition_id']);

        if ($edition_date && $edition_number){
            $edition_id = (int)$edition_number;
        }

        if (isset($_POST['_wpnonce'])){
            if (wp_verify_nonce($_POST['_wpnonce'], 'daily-edition-new')){
                self::processEdition($edition_number, (array)$_POST['post_order'], (array)$_POST['post_publish']);
            }
            elseif (wp_verify_nonce($_POST['_wpnonce'], 'daily-edition-add')){
                self::processEdition($edition_number, (array)$_POST['post_order'], (array)$_POST['post_publish']);
            }
            elseif (wp_verify_nonce($_POST['_wpnonce'], 'daily-edition-update')){
                self::processUpdateEdition($edition_number, (array)$_POST['post_order'], (array)$_POST['post_unpublish']);
            }
        }

        // List latest editions
        $editions = $wpdb->get_results( $wpdb->prepare(
            "SELECT posts.ID, posts.post_date, meta.meta_value as edition_number ".
            "FROM $wpdb->postmeta AS meta ".
            "INNER JOIN $wpdb->posts AS posts ON posts.ID = meta.post_id ".
            "WHERE meta_key = %s ".
            "GROUP BY meta_value DESC LIMIT 10",
            'daily-edition-number'
        ));

        // Extract candidate posts
        $unpublished = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'pending',
            'numberposts' => -1
        ));

        // Edition posts
        if ($edition_id){
            $posts = $wpdb->get_results( $wpdb->prepare(
                "SELECT posts.ID, posts.post_title, posts.post_date ".
                "FROM $wpdb->postmeta as meta ".
                "INNER JOIN $wpdb->posts as posts ON posts.ID = meta.post_id ".
                "WHERE meta_key = %s AND meta_value = %d ",
                'daily-edition-number', $edition_id
            ) );
        }

        include dirname(__FILE__) . '/../includes/new-edition.php';
    }

    public static function getEdition($edition_id = null){
        global $wpdb;

        $edition_number = null;
        $edition_date = null;

        if ($edition_id){
            $result = $wpdb->get_row( $wpdb->prepare(
                "SELECT post_date, meta_value ".
                "FROM $wpdb->postmeta INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id ".
                "WHERE meta_key = %s AND meta_value = %d LIMIT 1;", 'daily-edition-number', $edition_id
            ) );

            $edition_number = (int)$result->meta_value;
            $edition_date = $result->post_date;
        }
        else{
            $result = $wpdb->get_row( $wpdb->prepare(
                "SELECT MAX(meta_value) as edition_number ".
                    "FROM $wpdb->postmeta ".
                    "WHERE meta_key = %s;", 'daily-edition-number'
            ) );

            $edition_number = (int)$result->edition_number + 1;
        }

        if ($edition_date !== null){
            $edition_date = mysql2date('l j F Y', $edition_date);
        }

        return array($edition_number, $edition_date);
    }

    public static function processEdition($edition_number, array $post_order, array $publish){
        foreach ((array)$post_order as $post_id => $post_order){
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

    public static function processUpdateEdition($edition_number, array $post_order, array $unpublish){
        foreach ((array)$post_order as $post_id => $post_order){
            if (isset($unpublish[ $post_id ])){
                wp_update_post(array('ID' => $post_id, 'post_status' => 'pending'));
                delete_post_meta($post_id, 'daily-edition-number');
                delete_post_meta($post_id, 'daily-edition-order');
            }
            else{
                // just to be sure
                add_post_meta($post_id, 'daily-edition-order', (int)$post_order, true);
                update_post_meta($post_id, 'daily-edition-order', (int)$post_order);
            }
        }
    }
}
