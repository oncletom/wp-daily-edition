<?php

namespace DailyEdition\Admin;

class Edition
{
    public static function register(){
        $class = '\DailyEdition\Admin\Edition';

        $page = \add_posts_page(
            __('Manage Editions', 'daily-edition'),
            __('Manage Editions', 'daily-edition'),
            'publish_posts',
            'editions',
            array($class, 'ManagementPage')
        );

        add_action('load-'.$page, array($class, 'processForm'));
        add_action('admin_print_scripts-'.$page, array($class, 'registerScripts'));
    }

    public static function registerScripts(){
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-sortable');
    }

    public static function processForm(){
        if (!isset($_POST) || empty($_POST) || !isset($_POST['_wpnonce'])){
            return;
        }

        $editionArgs = self::getEdition((int)$_GET['edition_id'], (int)$_POST['edition_number']);

        if (wp_verify_nonce($_POST['_wpnonce'], 'daily-edition-new')){
            self::processEdition($editionArgs, (array)$_POST['post_order'], (array)$_POST['post_publish']);
            do_action('daily-edition-new', $editionArgs, $_POST['post_publish']);
            wp_redirect(admin_url('edit.php?page=editions&edition_id='.$editionArgs['edition_id'].'&message=created'));
        }
        elseif (wp_verify_nonce($_POST['_wpnonce'], 'daily-edition-add')){
            self::processEdition($editionArgs, (array)$_POST['post_order'], (array)$_POST['post_publish']);
            do_action('daily-edition-add', $editionArgs, $_POST['post_publish']);
            wp_redirect(admin_url('edit.php?page=editions&edition_id='.$editionArgs['edition_id'].'&message=added'));
        }
        elseif (wp_verify_nonce($_POST['_wpnonce'], 'daily-edition-update')){
            self::processUpdateEdition($editionArgs, (array)$_POST['post_order'], (array)$_POST['post_unpublish']);
            do_action('daily-edition-update', $editionArgs, $_POST['post_order'], $_POST['post_unpublish']);

            // Everything is unpublished
            if (count($_POST['post_order']) === count($_POST['post_unpublish'])){
                wp_redirect(admin_url('edit.php?page=editions&message=updated'));
            }
            else{
                wp_redirect(admin_url('edit.php?page=editions&edition_id='.$editionArgs['edition_id'].'&message=updated'));
            }
        }
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
        $editionArgs = self::getEdition((int)$_GET['edition_id'], (int)$_POST['edition_number']);
        extract($editionArgs);

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
                "SELECT posts.ID, posts.post_title, posts.post_date, meta_order.meta_value as post_order ".
                "FROM $wpdb->postmeta as meta ".
                "INNER JOIN $wpdb->posts as posts ON posts.ID = meta.post_id ".
                "LEFT JOIN $wpdb->postmeta as meta_order on meta_order.post_id = meta.post_id and meta_order.meta_key = %s ".
                "WHERE meta.meta_key = %s AND meta.meta_value = %d ".
                "ORDER BY post_order;",
                'daily-edition-order', 'daily-edition-number', $edition_id
            ) );
        }

        include dirname(__FILE__) . '/../includes/new-edition.php';
    }

    public static function getEdition($edition_id = null, $default_id = null){
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

            $edition_number = $default_id ? $default_id : (int)$result->edition_number + 1;
            $edition_date = date('Y-m-d H:s');
        }

        // Backport fix: the 2 cases are always fulfilled.
        if ($edition_date && $edition_number){
            $edition_id = (int)$edition_number;
        }

        return array('edition_number' => $edition_number, 'edition_date' => $edition_date, 'edition_id' => $edition_id);
    }

    public static function processEdition(array $editionArgs, array $post_order, array $publish){
        foreach ((array)$post_order as $post_id => $post_order){
            if (!isset($publish[ $post_id ])){
                continue;
            }

            wp_update_post(array('ID' => $post_id, 'post_date' => $editionArgs['edition_date']));

            // just to be sure
            add_post_meta($post_id, 'daily-edition-order', (int)$post_order, true);
            update_post_meta($post_id, 'daily-edition-order', (int)$post_order);

            add_post_meta($post_id, 'daily-edition-number', (int)$editionArgs['edition_number'], true);
            update_post_meta($post_id, 'daily-edition-number', (int)$editionArgs['edition_number']);

            wp_publish_post($post_id);
        }
    }

    public static function processUpdateEdition(array $editionArgs, array $post_order, array $unpublish){
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
