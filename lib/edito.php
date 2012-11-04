<?php

namespace DailyEdition;

class Edito{
    private static $edito;

    public static function set(\StdClass $post){
        self::$edito = $post;
    }

    public static function get(){
        return self::$edito;
    }
}

