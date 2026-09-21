<?php 

function dd($var, $exit = true){
    echo '<pre>';
    var_dump($var); 
    echo '</pre>';
    
    if($exit){
        exit;
    }
}

function escape($str) {
    return htmlspecialchars($str); 
}

function unescape($str) {
    return htmlspecialchars_decode($str); 
}