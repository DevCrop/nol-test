<?php
require_once '../../../inc/lib/base.class.php';
\Security\AdminAccount::requireSuper();
$depthnum=8; $pagenum=1; $adminLogStyles=true;
$logKind='access'; $logTitle='개인정보 접속기록';
$logPath=blue_admin_base().'/pages/account/access.php';
try { $reader=new \Security\AdminLogReader(DB::getInstance(),$logKind); $logData=$reader->read($_GET); }
catch (Throwable $error) {
    error_log('Admin log read failed');
    $logData=['filters'=>['from'=>'','to'=>'','entity'=>'','action'=>'','q'=>'','perpage'=>20],'errors'=>['기록을 불러오지 못했습니다. 잠시 후 다시 시도해주세요.'],'rows'=>[],'total'=>0,'page'=>1,'pages'=>1,'start'=>0,'end'=>0];
}
$e=static function($value):string{return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
include_once '../../inc/admin.title.php'; include_once '../../inc/admin.css.php'; include_once '../../inc/admin.js.php';
?>
</head><body class="no-account-page no-admin-log-page"><div class="no-wrap">
<?php include_once '../../inc/admin.header.php'; ?>
<main class="no-app no-container"><?php include_once '../../inc/admin.drawer.php'; ?>
<section class="no-content"><div class="no-toolbar"><div class="no-toolbar-container no-flex-stack"><div class="no-page-indicator"><h1 class="no-page-title"><?=$e($logTitle)?></h1><div class="no-breadcrumb-container"><ul class="no-breadcrumb-list"><li class="no-breadcrumb-item"><span>계정 및 권한</span></li><li class="no-breadcrumb-item"><span><?=$e($logTitle)?></span></li></ul></div></div></div></div>
<div class="no-content-container"><?php include '../../inc/log.view.php'; ?></div>
</section></main><?php include_once '../../inc/admin.footer.php'; ?></div></body></html>
