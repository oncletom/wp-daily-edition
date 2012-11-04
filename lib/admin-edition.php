<?php

namespace DailyEdition\Admin;

class Edition
{
    public static function register(){
        \add_posts_page(
            __('Manage Editions', 'daily-edition'),
            __('Manage Editions', 'daily-edition'),
            'publish_posts',
            'editions',
            array('\DailyEdition\Admin\Edition', 'ManagementPage')
        );
    }

    public static function ManagementPage(){

    }
}
