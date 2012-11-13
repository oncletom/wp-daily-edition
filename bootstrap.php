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
require $dir . '/lib/navigation.php';
require $dir . '/lib/hooks.php';
require $dir . '/lib/admin-hooks.php';
require $dir . '/lib/admin-edition.php';
require $dir . '/helpers.php';

load_plugin_textdomain('daily-edition', null, 'daily-edition/i18n');

DailyEdition\Hooks::register(is_admin() === false);
DailyEdition\Admin\Hooks::register(is_admin() === true);
