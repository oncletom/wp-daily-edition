<?php

/**
 *
 *
 * @param string|null $format
 * @return string
 */
function get_latest_edition_date($format = null){
    static $date;

    if (!isset($date)){
        $latest_post = get_latest_post();

        $date = $latest_post->post_date;
    }

    if ($format === null){
        return array_combine(array('year', 'month', 'day'), explode('-', mysql2date('Y-m-d', $date)));
    }
    else{
        return date_i18n($format, mysql2date('U', $date));
    }
}

function get_latest_edition_number(){
    return get_post_meta(get_latest_post()->ID, 'daily-edition-number', true);
}

function get_latest_post(){
    static $latest_post;

    if (!isset($latest_post)){
        $posts = get_posts(array(
            'numberposts' => 1,
            'meta_key' => 'daily-edition-number'
        ));

        $latest_post = count($posts) ? $posts[0] : null;
    }

    return $latest_post;
}

/**
 * @param null $date
 * @return mixed|null
 */
function previous_edition_nav($date = null){
    return edition_nav($date, '<', '00:00:00');
}

/**
 * @param null $date
 * @return mixed|null
 */
function next_edition_nav($date = null){
    return edition_nav($date, '>', '23:59:59');
}

/**
 * @param $date
 * @param $operator
 * @param $time
 * @return mixed|null
 */
function edition_nav($date, $operator, $time){
    /** @var $wpdb WPDB */
    global 	$wpdb;

    if ($date === null){
        /** @var $post StdClass  */
        global $post;

        $date = $post->post_date;
    }

    $operator = $operator === '<' ? '<' : '>';
    $ORDER = $operator === '>' ? 'ASC' : 'DESC';
    $date = preg_replace('# \d{2}:\d{2}:\d{2}#', ' '.$time, $date);

    //retrieving candidate
    $candidate = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_date FROM $wpdb->posts ".
        "INNER JOIN $wpdb->postmeta ON (post_id = id AND meta_key = %s) ".
        "WHERE post_date ".$operator." %s AND post_status = %s AND post_type = %s ".
        "ORDER BY post_date $ORDER LIMIT 1;",
        'daily-edition-number', $date, 'publish', 'post'
    ));

    if ($candidate !== null){
        $day = preg_split('#[ -]#U', $candidate);
        array_pop($day);
        return call_user_func_array('get_day_link', $day);
    }

    return null;
}

function get_edition_link($post = null){
    if ($post === null){
        global $post;
    }

    return get_day_link(get_the_time('Y', $post), get_the_time('m', $post), get_the_time('d', $post));
}