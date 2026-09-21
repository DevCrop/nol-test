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
