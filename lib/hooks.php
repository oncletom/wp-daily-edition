<?php

namespace DailyEdition;

class Hooks{

    public static function register($condition = true){
        if (!$condition){
            return false;
        }

        add_action('parse_query', array('\DailyEdition\Hooks', 'filterHomeQuery'));
    }

    function filterHomeQuery(\WP_Query &$query){
        if ($query->is_main_query() && is_home()){
            $date = get_latest_edition_date();

            $query->set('year', $date['year']);
            $query->set('monthnum', $date['month']);
            $query->set('day', $date['day']);
        }

        if ($query->is_main_query() && (is_home() || is_archive())){
            $query->set('nopaging', true);
            $query->set('meta_key', 'daily-edition-order');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'ASC');
        }
    }
}