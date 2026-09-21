<?php

class DB {
    static $instance = null;

    public static function getInstance() {
        return \Database\DB::getInstance(); 
    }
}