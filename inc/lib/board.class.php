<?php

function getBoardInfoByName($nm) {
    global $NO_SITE_UNIQUE_KEY;
    $connect = DB::getInstance(); // PDO 인스턴스 가져오기

    $query = "SELECT a.no, a.sitekey, a.title, a.skin, a.top_banner_image, a.contents, a.view_yn, a.secret_yn, 
                     a.sort_no, a.list_size, a.fileattach_yn, a.fileattach_cnt, a.comment_yn, a.depth1, 
                     a.depth2, a.depth3, a.lnb_path, a.view_skin 
              FROM nb_board_manage a 
              WHERE a.sitekey = :sitekey AND a.title = :title";
    
    $stmt = $connect->prepare($query);
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':title' => $nm]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBoardInfoByNo($board_no) {
    global $NO_SITE_UNIQUE_KEY;
    $connect = DB::getInstance();

    $query = "SELECT a.no, a.sitekey, a.title, a.skin, a.top_banner_image, a.contents, a.view_yn, 
                     a.secret_yn, a.sort_no, a.list_size, a.fileattach_yn, a.fileattach_cnt, a.comment_yn, 
                     a.depth1, a.depth2, a.depth3, a.lnb_path, a.isOpen, a.view_skin 
              FROM nb_board_manage a 
              WHERE a.sitekey = :sitekey AND a.no = :board_no";
    
    $stmt = $connect->prepare($query);
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $board_no]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBoardManageInfoByNo($board_no) {
    global $NO_SITE_UNIQUE_KEY;
    global $extra_fields;
    global $extra_match_fields;
    $connect = DB::getInstance();

    $query = "SELECT a.sitekey, a.title, a.skin, a.regdate, a.top_banner_image, a.contents, a.view_yn, 
                     a.secret_yn, a.list_size, a.fileattach_yn, a.fileattach_cnt, a.comment_yn, a.depth1, 
                     a.depth2, a.depth3, a.lnb_path, a.category_yn, a.view_skin, $extra_match_fields 
              FROM nb_board_manage a 
              WHERE a.sitekey = :sitekey AND a.no = :board_no";

    $stmt = $connect->prepare($query);
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $board_no]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBoardLimit($board_no, $limit = 5, $orderby = null) {
    global $NO_SITE_UNIQUE_KEY;
    global $extra_fields;
    $connect = DB::getInstance();

    $mainqry = "WHERE a.sitekey = :sitekey AND a.board_no = :board_no";
    $orderByqry = $orderby ? $orderby : "a.is_notice='Y' DESC, a.regdate DESC";
    
    $query = "SELECT a.no, a.board_no, a.user_no, a.category_no, a.comment_cnt, a.title, a.contents, 
                     a.regdate, a.read_cnt, a.direct_url, a.thumb_image, a.is_admin_writed, a.is_notice, 
                     a.is_secret, a.secret_pwd, a.write_name, a.isFile, a.file_attach_1, a.file_attach_origin_1, 
                     a.file_attach_2, a.file_attach_origin_2, a.file_attach_3, a.file_attach_origin_3, 
                     a.file_attach_4, a.file_attach_origin_4, a.file_attach_5, a.file_attach_origin_5, 
                     $extra_fields, b.title as board_name, c.name as category_name 
              FROM nb_board a 
              LEFT JOIN nb_board_manage b ON a.board_no = b.no 
			  LEFT JOIN nb_board_category c ON a.category_no = c.no 
              $mainqry ORDER BY $orderByqry LIMIT $limit";
    
    $stmt = $connect->prepare($query);
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $board_no]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBoardRole($board_no, $user_lev) {
    global $NO_SITE_UNIQUE_KEY;
    $connect = DB::getInstance();

    $query = "SELECT no, lev_no, role_write, role_edit, role_view, role_list, role_delete, role_comment 
              FROM nb_board_lev_manage 
              WHERE sitekey = :sitekey AND lev_no = :user_lev AND board_no = :board_no";
    
    $stmt = $connect->prepare($query);
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':user_lev' => $user_lev, ':board_no' => $board_no]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBoardCategory($board_no) {
    global $NO_SITE_UNIQUE_KEY;
    $connect = DB::getInstance();

    $query = "SELECT no, name 
              FROM nb_board_category 
              WHERE sitekey = :sitekey AND board_no = :board_no 
              ORDER BY sort_no ASC";
    
    $stmt = $connect->prepare($query);
    $stmt->execute([':sitekey' => $NO_SITE_UNIQUE_KEY, ':board_no' => $board_no]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBoardCnt($board_no) {
    $connect = DB::getInstance();

    $query = "SELECT COUNT(no) as cnt 
              FROM nb_board 
              WHERE board_no = :board_no";
    
    $stmt = $connect->prepare($query);
    $stmt->execute([':board_no' => $board_no]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
}

function getBoardCategoryCnt($category_no) {
    $connect = DB::getInstance();

    $query = "SELECT COUNT(no) as cnt 
              FROM nb_board 
              WHERE category_no = :category_no";
    
    $stmt = $connect->prepare($query);
    $stmt->execute([':category_no' => $category_no]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
}
?>
