<?php

namespace DailyEdition\Admin;

class Hooks
{
    public static function register($condition = false){
        add_action('admin_menu', array('\DailyEdition\Admin\Edition', 'register'));
    }
}
