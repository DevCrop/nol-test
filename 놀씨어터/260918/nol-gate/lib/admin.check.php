<?php

include_once dirname(__DIR__, 2) . "/inc/lib/base.class.php";
require_once __DIR__ . "/AuthSession.php";

if (empty($_SESSION["no_adm_login_uid"])) {
    AuthSession::denyLogin();
}
