
<link rel="stylesheet" href="<?=$NO_IS_SUBDIR?>/resource/vendor/jquery/jquery-ui.css"/>

<!-- Summernote -->
<link rel="stylesheet" href="<?=$NO_IS_SUBDIR?>/resource/vendor/summernote/bootstrap.min.css">
<link rel="stylesheet" href="<?=$NO_IS_SUBDIR?>/resource/vendor/summernote/summernote.min.css">


<!--  css  -->
<link rel="stylesheet" href="<?=$NO_IS_SUBDIR?>/resource/vendor/boxicons/css/boxicons.min.css" />
<link rel="stylesheet" href="<?=$NO_IS_SUBDIR?>/resource/vendor/fontawsome/css/all.css" />


<!-- 
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="<?=$NO_IS_SUBDIR?>/resource/vendor/filter-multi-select/filter-multi-select.css"> -->

<link rel="stylesheet" href="<?=$NO_ADMIN_RESOURCE_BASE?>/css/style.css?c=<?=$STATIC_ADMIN_CSS_MODIFY_DATE?>"/>
<?php if (!empty($adminAuditStyles)): ?>
<link rel="stylesheet" href="<?=$NO_ADMIN_RESOURCE_BASE?>/css/audit-ui.css?v=20261020"/>
<?php endif; ?>
<?php if (!empty($adminAccountPreviewStyles)): ?>
<link rel="stylesheet" href="<?=$NO_ADMIN_RESOURCE_BASE?>/css/account-preview.css?v=20261020-copy-alert"/>
<?php endif; ?>
<!-- fontello -->
<?php if (!empty($adminCredentialStyles)): ?>
<link rel="stylesheet" href="<?=$NO_ADMIN_RESOURCE_BASE?>/css/account-credentials.css?v=20261021"/>
<?php endif; ?>
