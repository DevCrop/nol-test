<?php

class Menu {
  public $boardPath;
  public $dirList;
  public $menuPath;
  public $boardNumList;
  
  
  private $data;
  private $site_name;
  private $menuItems;
  private $curPage;
  private $pageTitle;
  
  public function __construct(){
    $this->setInfo();
    $this->data = getJSON($this->menuPath);
    $this->setData();
    $this->setPages(); 
    $this->setPageIsActive();
    $this->setPageTitle();
  }

  public function getSiteName(){
    return $this->site_name;
  }

  public function setData(){
    $this->site_name = $this->data['site_name'];
  }

  public function setPageTitle(){
    $title = $this->site_name;
    $menuItems = $this->menuItems;

    if(!$this->curPage){
      $this->pageTitle = $title;
      return;
    }
    $curIndex = $this->curPage['index'];

    foreach($curIndex as $k => $v){
      switch($k){
        case 0: { 
          $title = $menuItems[$curIndex[0]]['title'] . ' | ' . $title;
        } break;
        case 1: {
          $title = $menuItems[$curIndex[0]]['pages'][$curIndex[1]]['title'] . ' | ' . $title;
        } break;
        case 2: {
          $title = $menuItems[$curIndex[0]]['pages'][$curIndex[1]]['pages'][$curIndex[2]]['title'] . ' | ' . $title;
        } break;
      }
    }

    $this->pageTitle = $title;
  }

  public function setPageIsActive(){
    $newItems = $this->menuItems;
    if(!$this->curPage){
      // echo 'curPage not found. url does not match.';
      return; 
    }
    $curIndex = $this->curPage['index'];
    
    foreach($curIndex as $i => $v){
      switch($i){
        case 0: {
          $newItems[$curIndex[0]]['isActive'] = true;
        } break;
        case 1: {
          $newItems[$curIndex[0]]['pages'][$curIndex[1]]['isActive'] = true;
        } break;
        case 2: {
          $newItems[$curIndex[0]]['pages'][$curIndex[1]]['pages'][$curIndex[2]]['isActive'] = true;
        } break;
        
      }
    }

    $this->menuItems = $newItems;
  }

  public function setInfo(){
    global $DIR_LIST, 
           $BOARD_PATH, 
           $BOARD_NUM_LIST,
           $MENU_PATH;
    
    $this->dirList = $DIR_LIST;
    $this->boardPath = $BOARD_PATH;
    $this->boardNumList = $BOARD_NUM_LIST;
    $this->menuPath = $MENU_PATH;
  }

  public function setPages(){
    $pages = $this->data['pages'];
    $this->menuItems = $this->setPageInfo($pages, null);
  }

  public function setPageInfo($pure_pages, $prev_page){
    $pages = array();

    foreach($pure_pages as $k => &$v){

      // set index
      if(isset($prev_page['index'])){
        $v['index'] = $prev_page['index'];
        array_push($v['index'], $k);
      } else {
        $v['index'] = array($k);
      }

       // set dirname
       $v['dirname'] = $this->getDirname($prev_page, $v);

      // check pages
      if(array_key_exists('pages', $v) && count($v['pages'])){
        $childPage = $this->setPageInfo($v['pages'], $v);
        $v['pages'] = $childPage;
      }

      // set path
      $v['path'] = $this->getPath($prev_page, $v);
	  $v['target'] = "_self";
	  if(strpos($v['path'], '.com') ||
		 strpos($v['path'], '.co.kr') ||
		 strpos($v['path'], '.org') ||
		 strpos($v['path'], '.io') ||
		 strpos($v['path'], '.ne.kr')
		){
		$v['target'] = "_blank";
	  }

      // set isActive
      $v['isActive'] = $this->isPageActive($v);
      if($v['isActive'] && !$this->curPage){
        $this->curPage = $v;
      }
      
      array_push($pages, $v);
    }
    unset($v);

    return $pages;
  }

public function isPageActive($page) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // 현재 URI 경로 추출
    $pagePath = parse_url($page['path'], PHP_URL_PATH); // 페이지 path의 경로 추출

    // 기본 경로가 동일한지 확인
    if ($uriPath !== $pagePath) {
        return false; // 경로가 다르면 false
    }

    // 쿼리 파라미터 추출
    $pageQueryParams = [];
    $currentQueryParams = [];

    if (isset(parse_url($page['path'])['query'])) {
        parse_str(parse_url($page['path'], PHP_URL_QUERY), $pageQueryParams); // 메뉴 path의 쿼리 파라미터 추출
    }

    if (isset(parse_url($_SERVER['REQUEST_URI'])['query'])) {
        parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $currentQueryParams); // 현재 URI의 쿼리 파라미터 추출
    }

    // `board_no`가 있는 경우 특별 처리
    if (isset($pageQueryParams['board_no'])) {
        $isActive = true;

        foreach ($pageQueryParams as $key => $value) {
            if (!isset($currentQueryParams[$key])) {
                $isActive = false; // 필수 파라미터가 없으면 false
                break;
            }

            if ($currentQueryParams[$key] !== $value) {
                $isActive = false; // 값이 일치하지 않으면 false
                break;
            }
        }

        return $isActive; // board_no 처리 후 결과 반환
    }

    // 필수 쿼리 파라미터가 설정되어 있는 경우 비교
    foreach ($pageQueryParams as $key => $value) {
        if (!isset($currentQueryParams[$key])) {
            return false; // 필수 파라미터가 없는 경우 false
        }

        // 빈 값이 허용되지 않으면 strict 비교
        if ($value !== '' && $currentQueryParams[$key] !== $value) {
            return false; // 값이 일치하지 않는 경우 false
        }
    }

    return true; // 모든 조건이 만족되면 true
}


  public function getDirname($prev_page, $v){
    $prev_dirname = '';
    $page_dirname = '';

    if(isset($prev_page['dirname'])){
      $prev_dirname = $prev_page['dirname'];
    }
    if(isset($v['dirname'])){
      $page_dirname = $v['dirname'];
    }

    if(!empty($prev_dirname) && !empty($page_dirname)){
      return $prev_dirname.'/'.$page_dirname;
    }
    if(!empty($prev_dirname) && empty($page_dirname)){
      return $prev_dirname;
    }
    if(empty($prev_dirname) && !empty($page_dirname)){
      return $page_dirname;
    }
  }

  public function getPath($prev_info, $v){
    
	if(isset($v['ext_link'])){
		return $v['ext_link'];
	}

    if(isset($v['board_no'])){
      $params = "?board_no=".$v['board_no'];

      if(isset($v['category_no'])){
        $params = $params."&category_no=".$v['category_no'];
      }


      return $this->boardPath.$params;
    }
    
    if(!array_key_exists('filename', $v) && empty($v['filename'])){
      if(array_key_exists('pages', $v) && count($v['pages']) > 0){
        return $v['pages'][0]['path'];
      } else {
        return $this->getFile('index');
      }
    }
    
    $path = !empty($prev_info['dirname']) 
              ? $prev_info['dirname'].'/'.$v['filename']
              : $v['filename'];
    return $this->getFile($path);
  }

  public function getCurPageIndex(){
    return $this->curPage? $this->curPage['index'] : null;
  }

  public function getCurPageList(){
	$curPageList = array();
	$menuItems = $this->menuItems; 

    $curPageIndex = $this->getCurPageIndex();
	if(!$curPageIndex){
		return null; 
	}
    
    foreach($curPageIndex as $i => $v){
      switch($i){
        case 0: {
			  array_push($curPageList, $menuItems[$curPageIndex[0]]);
        } break;
        case 1: {
			array_push($curPageList, $menuItems[$curPageIndex[0]]['pages'][$curPageIndex[1]]);
        } break;
        case 2: {
			array_push($curPageList, $menuItems[$curPageIndex[0]]['pages'][$curPageIndex[1]]['pages'][$curPageIndex[2]]);
        } break;
      }
    }

	return $curPageList;
  }

  public function getFile($path){
    return $this->dirList['pages'].'/'.$path.'.php';
  }

  public function getCurPage(){
    return $this->curPage? $this->curPage : null;
  }

  public function getMenuItems(){
    return $this->menuItems;
  }

  public function getPageTitle(){
    return $this->pageTitle;
  }
}