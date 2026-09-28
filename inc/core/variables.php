<?php

$MENU                    = new Menu();
$PAGE_TITLE                = $MENU->getPageTitle();
$MENU_ITEMS                = $MENU->getMenuItems();
$CUR_PAGE                = $MENU->getCurPage();
$CUR_PAGE_LIST            = $MENU->getCurPageList();
$CUR_PAGE_ITEMS            = isset($CUR_PAGE_LIST[0]) ? $CUR_PAGE_LIST[0]['pages'] : array();
$CUR_PAGE_INDEX            = $MENU->getCurPageIndex();