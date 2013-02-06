<?php

namespace DailyEdition;

class Hooks{

    public static function register($condition = true){
        if (!$condition){
            return false;
        }

        add_action('parse_query', array('\DailyEdition\Hooks', 'filterHomeQuery'));
        add_action('parse_query', array('\DailyEdition\Hooks', 'filterArchiveQuery'));
        add_action('archive_template', array('\DailyEdition\Hooks', 'filterArchiveTemplate'));
        add_action('search_template', array('\DailyEdition\Hooks', 'filterSearchTemplate'));
        add_action('category_template', array('\DailyEdition\Hooks', 'filterSearchTemplate'));
    }

    /**
     * Adds the proper parameters to make frontpage and daily pagination the right content
     *
     * @param \WP_Query $query
     */
    public static function filterHomeQuery(\WP_Query &$query){
        /*
         * Nothing to do
         */
        if (!$query->is_main_query()){
            return $query;
        }

        // Front Page
        if (is_home()){
            $date = get_latest_edition_date();

            $query->set('year', $date['year']);
            $query->set('monthnum', $date['month']);
            $query->set('day', $date['day']);
        }

        // Daily Pagination
        if (is_home() || (is_archive() && is_day())){
            $query->set('nopaging', true);
            $query->set('meta_key', 'daily-edition-order');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'ASC');
        }
    }

    public static function filterArchiveQuery(\WP_Query &$query){
        /*
         * Nothing to do
         */
        if (!$query->is_main_query()){
            return $query;
        }

        /*
         * Yearly and Monthly
         */
        if (is_archive() && (is_year() || is_month())){
            $query->set('cat', get_cat_ID('Edito'));
            $query->set('meta_query', array(
                array(
                    'key' => 'daily-edition-number',
                    'compare' => 'EXISTS',
                )
            ));
        }

        /*
         * Categories and tags
         */
        if (is_category() || is_tag()){

        }
    }

    /**
     * For daily pagination, we still rely on the index
     * So far, index and daily pagination has the same appearance
     *
     * @param string $template_file
     * @return string
     */
    public static function filterArchiveTemplate($template_file){

        if (is_day()){
            $template_file = locate_template('index.php');
        }

        return $template_file;
    }

    /**
     * For daily pagination, we still rely on the index
     * So far, index and daily pagination has the same appearance
     *
     * @param string $template_file
     * @return string
     */
    public static function filterSearchTemplate($template_file){
        $template_file = locate_template('archive.php');

        return $template_file;
    }
}
