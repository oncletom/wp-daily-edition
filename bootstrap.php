<?php
/*
Plugin Name: Daily Edition
Description: Handling posting as a daily edition, with reviews and so on.
Author: Oncle Tom
Version: 1.0-beta
Author URI: http://cyneticmonkey.com/
Plugin URI: http://wordpress.org/extend/plugins/daily-edition/

  This plugin is released under version 3 of the GPL:
  http://www.opensource.org/licenses/gpl-3.0.html
*/

$dir = dirname(__FILE__);
require $dir . '/lib/edito.php';
require $dir . '/lib/navigation.php';
require $dir . '/lib/hooks.php';
require $dir . '/helpers.php';

DailyEditionHooks::register(is_admin() === false);
