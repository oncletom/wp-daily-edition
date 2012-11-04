<?php

/**
 * Retrieves the current edito within a Loop
 *
 * @param null $post
 * @return null
 */
function get_edito(){
    return DailyEditionEdito::get();
}