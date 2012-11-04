<?php

class DailyEditionHooks{

    public static function register($condition = true){
        if (!$condition){
            return false;
        }

        add_action('parse_query', array('DailyEditionHooks', 'filterHomeQuery'));
        add_action('posts_results', array('DailyEditionHooks', 'setupEdito'), 10, 2);
    }

    function filterHomeQuery(WP_Query &$query){
        if ($query->is_main_query() && is_home()){
            $date = get_latest_edition_date();

            $query->set('year', $date['year']);
            $query->set('monthnum', $date['month']);
            $query->set('day', $date['day']);
            $query->set('nopaging', true);
        }
    }

    function setupEdito(array $posts, WP_Query $query){
        if (!$query->is_main_query() || is_single()){
            return $posts;
        }

        foreach($posts as $i => &$post){
            foreach(get_the_category($post->ID) as $category){
                if ($category->slug === 'edito'){
                    DailyEditionEdito::set($post);
                    unset($posts[$i]);

                    break;
                }
            }
        }

        return $posts;
    }

}