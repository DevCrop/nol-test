<title><?= $NO_STATIC_TITLE ?> :: 관리자</title>
<meta charset="UTF-8">
<meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
<script src="<?= $NO_ADMIN_RESOURCE_BASE ?>/js/csrf.js?v=<?= filemtime($NO_ADMIN_PATH.'/resource/js/csrf.js') ?>"></script>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="shortcut icon" href="<?= $NO_META_SHORTCUT_ICON ?>">
