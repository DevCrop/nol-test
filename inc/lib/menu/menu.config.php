<?php

// 언어 정보 ==============================================
$DEFAULT_LANG = 'ko';
$LOCALE_LIST = array(
  'ko' => 'ko-kr',
  'en' => 'en-us',
  'ja' => 'ja-jp',
  'es' => 'es-es',
  'pt' => 'pt-pt',
);


// 경로 정보 ==============================================
// 서버루트 - include
$SERVER_ROOT	= $_SERVER['DOCUMENT_ROOT'];

// ✅ 페이지경로로 사용 - lang 없음
$ROOT			= $NO_IS_SUBDIR;

// ✅ 페이지경로로 사용 - lang 추가됨
$CLIENT_ROOT	= $ROOT;

// ✅ 인클루드로 사용 - project include
$STATIC_ROOT	= empty($ROOT) ? $SERVER_ROOT : $SERVER_ROOT.'/'.$ROOT; 

$HTML_LANG		= $DEFAULT_LANG;

// 보드 정보 ==============================================
$BOARD_DIR      = $ROOT.'/pages/board';
$BOARD_PATH     = $BOARD_DIR.'/board.list.php';
$BOARD_NUM_LIST = array(
  "ko" => array(),
  "en" => array(),
  "ja" => array(),
  "es" => array(),
  "pt" => array()
);


// 메뉴 JSON파일 경로설정 및 CLIENT_ROOT 경로 재설정 ==============================================
$MENU_DIR   = $STATIC_ROOT.'/json';

// 보드일 경우 경로 및 언어설정
$BOARD_LANG = checkBoardLocale($BOARD_NUM_LIST);
if($BOARD_LANG){
  $HTML_LANG = $BOARD_LANG;
  $CLIENT_ROOT  = $ROOT."/lang/{$HTML_LANG}";
}

// 일반 페이지일 경우 경로 및 언어설정
$split_path = explode('/', $_SERVER['REQUEST_URI']);
$current_lang = $split_path[1];
if($current_lang === 'lang'){
  $HTML_LANG    = $split_path[2];
  $CLIENT_ROOT  = $ROOT."/lang/{$HTML_LANG}";
}

$DIR_LIST   = array(
  'layouts'     => $STATIC_ROOT.'/inc/layouts',
  'lib'         => $STATIC_ROOT.'/inc/lib',
  'pages'       => $CLIENT_ROOT.'/pages',
  'board'       => $STATIC_ROOT.'/pages/board',
  'resource'    => $STATIC_ROOT.'/resource',
);

$MENU_PATH = $MENU_DIR."/menu.{$HTML_LANG}.json";


// 공통 경로 설정
define('IMG_PATH', "$ROOT/resource/images");

if(!file_exists($MENU_PATH) && $HTML_LANG === $DEFAULT_LANG){
  $MENU_PATH   = $MENU_DIR."/menu.json";
}

$LOCALE = $LOCALE_LIST[$HTML_LANG];