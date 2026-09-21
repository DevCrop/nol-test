<?php
include_once dirname(__DIR__, 3) . "/inc/lib/base.class.php";
require_once dirname(__DIR__) . "/AuthSession.php";

AuthSession::destroy();

header("Location: ../../");
exit;
