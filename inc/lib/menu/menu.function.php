<?php
include_once __DIR__.'/menu.config.php';

function getJSON($path){
  return json_decode(file_get_contents($path), true);
}

function show($stuff){
  echo "<pre>";
  var_dump($stuff);
  echo "</pre>";
}


function getLordIcon($src, $trigger = 'hover') {
    $basePath = '/resource/vendor/lordicon/json/';
    $fullSrc = $basePath . ltrim($src, '/');
    printf(
        '<lord-icon src="%s" trigger="%s"></lord-icon>',
        htmlspecialchars($fullSrc, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($trigger, ENT_QUOTES, 'UTF-8')
    );
}


function checkBoardLocale($BOARD_NUM_LIST){
  if(isset($_GET['board_no'])){
    foreach($BOARD_NUM_LIST as $lang => $list){
      foreach($list as $i => $no){
        if($_GET['board_no'] === (string)$no){
          return $lang;
        }
      }
    }
  }
  return null;
}

function getImage($path){
  return 'style="background-image: url('.IMG_PATH.$path.')"';
}
