<?php

include_once "../../../../inc/lib/base.class.php";
include_once "../../../lib/admin.check.ajax.php";

$pdo = DB::getInstance();
$mode = $_POST['mode'];

if ($mode == "pwd.change") {
    http_response_code(410);
    echo json_encode(["result" => "fail", "msg" => "계정 보안 화면에서 비밀번호를 변경하세요."]);
    exit;

} else if ($mode == "setting.config.save") {

    // Collect all sanitized POST data directly without a custom function
    $title = $_POST['title'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $hp = $_POST['hp'] ?? '';
    $fax = $_POST['fax'] ?? '';
    $email = $_POST['email'] ?? '';
    $customercenter_able_time = $_POST['customercenter_able_time'] ?? '';
    $company_able_time = $_POST['company_able_time'] ?? '';
    $google_map_key = $_POST['google_map_key'] ?? '';
    $footer_name = $_POST['footer_name'] ?? '';
    $footer_address = $_POST['footer_address'] ?? '';
    $footer_phone = $_POST['footer_phone'] ?? '';
    $footer_hp = $_POST['footer_hp'] ?? '';
    $footer_fax = $_POST['footer_fax'] ?? '';
    $footer_email = $_POST['footer_email'] ?? '';
    $footer_owner = $_POST['footer_owner'] ?? '';
    $footer_ssn = $_POST['footer_ssn'] ?? '';
    $footer_policy_charger = $_POST['footer_policy_charger'] ?? '';
    $meta_keywords = $_POST['meta_keywords'] ?? '';
    $meta_description = $_POST['meta_description'] ?? '';
    $logo_top_filename = $_POST['logo_top_filename'] ?? '';
    $logo_footer_filename = $_POST['logo_footer_filename'] ?? '';
    $meta_thumb_filename = $_POST['meta_thumb_filename'] ?? '';
    $meta_favicon_ico_filename = $_POST['meta_favicon_ico_filename'] ?? '';

    $dir_logo = $UPLOAD_SITEINFO_DIR_LOGO;
    $dir_meta = $UPLOAD_META_DIR;

    // Handle file uploads
    $logo_top = '';
    $logo_footer = '';
    $meta_thumb = '';
    $meta_favicon_ico = '';

    if (isset($_FILES['logo_top'])) {
        $uploadResult = imageUpload($dir_logo, $_FILES['logo_top'], $logo_top_filename, false);
        $logo_top = $uploadResult['saved'] ?? '';
    }

    if (isset($_FILES['logo_footer'])) {
        $uploadResult = imageUpload($dir_logo, $_FILES['logo_footer'], $logo_footer_filename, false);
        $logo_footer = $uploadResult['saved'] ?? '';
    }

    if (isset($_FILES['meta_thumb'])) {
        $uploadResult = imageUpload($dir_meta, $_FILES['meta_thumb'], $meta_thumb_filename, false);
        $meta_thumb = $uploadResult['saved'] ?? '';
    }

    if (isset($_FILES['meta_favicon_ico'])) {
        $uploadResult = imageUpload($dir_meta, $_FILES['meta_favicon_ico'], $meta_favicon_ico_filename, false);
        $meta_favicon_ico = $uploadResult['saved'] ?? '';
    }

    // Check if entry exists
    $stmt = $pdo->prepare("SELECT a.no FROM nb_siteinfo a WHERE a.sitekey = :sitekey");
    $stmt->execute(['sitekey' => $NO_SITE_UNIQUE_KEY]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Update existing record
        $query = "UPDATE nb_siteinfo SET 
                    title = :title,
                    phone = :phone,
                    hp = :hp,
                    fax = :fax,
                    email = :email,
                    customercenter_able_time = :customercenter_able_time,
                    company_able_time = :company_able_time,
                    google_map_key = :google_map_key,
                    footer_name = :footer_name,
                    footer_address = :footer_address,
                    footer_phone = :footer_phone,
                    footer_hp = :footer_hp,
                    footer_fax = :footer_fax,
                    footer_email = :footer_email,
                    footer_owner = :footer_owner,
                    footer_ssn = :footer_ssn,
                    footer_policy_charger = :footer_policy_charger,
                    meta_keywords = :meta_keywords,
                    meta_description = :meta_description";

        if ($logo_top) $query .= ", logo_top = :logo_top";
        if ($logo_footer) $query .= ", logo_footer = :logo_footer";
        if ($meta_thumb) $query .= ", meta_thumb = :meta_thumb";
        if ($meta_favicon_ico) $query .= ", meta_favicon_ico = :meta_favicon_ico";

        $query .= " WHERE sitekey = '$NO_SITE_UNIQUE_KEY'";

        $stmt = $pdo->prepare($query);
        $params = compact(
            'title', 'phone', 'hp', 'fax', 'email', 'customercenter_able_time',
            'company_able_time', 'google_map_key', 'footer_name', 'footer_address',
            'footer_phone', 'footer_hp', 'footer_fax', 'footer_email', 'footer_owner',
            'footer_ssn', 'footer_policy_charger', 'meta_keywords', 'meta_description'
        );
        if ($logo_top) $params['logo_top'] = $logo_top;
        if ($logo_footer) $params['logo_footer'] = $logo_footer;
        if ($meta_thumb) $params['meta_thumb'] = $meta_thumb;
        if ($meta_favicon_ico) $params['meta_favicon_ico'] = $meta_favicon_ico;

        $result = $stmt->execute($params);

        echo json_encode(["result" => $result ? "success" : "fail", "msg" => $result ? "정상적으로 수정되었습니다." : "처리중 문제가 발생하였습니다.[Error-DB]"]);

    } else {
        // Insert new record
        $query = "INSERT INTO nb_siteinfo (
                    sitekey, title, logo_top, logo_footer, meta_thumb, meta_favicon_ico,
                    phone, hp, fax, email, customercenter_able_time, company_able_time,
                    google_map_key, footer_name, footer_address, footer_phone, footer_hp,
                    footer_fax, footer_email, footer_owner, footer_ssn, footer_policy_charger,
                    meta_keywords, meta_description
                  ) VALUES (
                    '$NO_SITE_UNIQUE_KEY', :title, :logo_top, :logo_footer, :meta_thumb, :meta_favicon_ico,
                    :phone, :hp, :fax, :email, :customercenter_able_time, :company_able_time,
                    :google_map_key, :footer_name, :footer_address, :footer_phone, :footer_hp,
                    :footer_fax, :footer_email, :footer_owner, :footer_ssn, :footer_policy_charger,
                    :meta_keywords, :meta_description
                  )";

        $stmt = $pdo->prepare($query);
        $params = compact(
            'title', 'logo_top', 'logo_footer', 'meta_thumb',
            'meta_favicon_ico', 'phone', 'hp', 'fax', 'email', 'customercenter_able_time',
            'company_able_time', 'google_map_key', 'footer_name', 'footer_address',
            'footer_phone', 'footer_hp', 'footer_fax', 'footer_email', 'footer_owner',
            'footer_ssn', 'footer_policy_charger', 'meta_keywords', 'meta_description'
        );

        $result = $stmt->execute($params);
        echo json_encode(["result" => $result ? "success" : "fail", "msg" => $result ? "정상적으로 등록 되었습니다." : "처리중 문제가 발생하였습니다.[Error-DB]"]);
    }
}


?>
