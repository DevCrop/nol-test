<?php
if(PHP_SAPI!=='cli') exit(1);
function env($key,$default=null){return $key==='APP_ENV'?$GLOBALS['qa_environment']:$default;}
define('GATE_ALLOW_IPS',''); define('GATE_HOST','gate.local');
require dirname(__DIR__).'/inc/lib/Gate.php';require dirname(__DIR__).'/inc/lib/GateAllowlist.php';
$_SERVER['HTTP_HOST']='gate.local';$_SERVER['SCRIPT_NAME']='/index.php';
$pass=0;
foreach(['production','Production','prod','staging'] as $environment){
    $GLOBALS['qa_environment']=$environment;
    if(!GateAllowlist::shouldEnforce()) {echo "FAIL empty production gate policy\n";exit(1);}
    echo "PASS empty production gate policy requires enforcement: $environment\n";$pass++;
}
echo "OK $pass\n";
