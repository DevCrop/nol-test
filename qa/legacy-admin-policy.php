<?php
// Pure policy matrix; no production connection or environment file is read.
require dirname(__DIR__).'/inc/lib/Gate.php';
require dirname(__DIR__).'/src/Http/TransportSecurity.php';
define('PUBLIC_HOST','noltheater-daehakro.com');
define('GATE_HOST','gate.noltheater-daehakro.com');
define('ADMIN_DIR','nol-gate');
define('GATE_ENFORCE',true);
$enabled=false;
function env($key,$default=null){return $key==='GATE_ALLOW_LEGACY_ADMIN'?$GLOBALS['enabled']:($key==='GATE_ROUTE_MODE'?'php':$default);}
$cases=[
 [false,PUBLIC_HOST,true,'/nol-gate/',true],
 [true,PUBLIC_HOST,true,'/nol-gate/',false],
 [true,PUBLIC_HOST,true,'/nol-gate/Controller/AccountController.php',false],
 [true,PUBLIC_HOST,false,'/nol-gate/',true],
 [true,'unknown.example',true,'/nol-gate/',true],
 [true,GATE_HOST,true,'/nol-gate/',true],
 [true,PUBLIC_HOST,true,'/other/nol-gate/',true],
];
$fail=0;
foreach($cases as $i=>$c){[$enabled,$host,$tls,$path,$expected]=$c;$_SERVER=['HTTP_HOST'=>$host,'HTTPS'=>$tls?'on':'off','SERVER_PORT'=>$tls?443:80,'REQUEST_URI'=>$path,'SCRIPT_NAME'=>$path,'REMOTE_ADDR'=>'127.0.0.1'];
 $ok=Gate::shouldHideAdminDir()===$expected;echo ($ok?'PASS ':'FAIL ').'legacy policy case '.($i+1)."\n";if(!$ok)$fail++;
}
echo (count($cases)-$fail)." PASS / $fail FAIL\n";exit($fail?1:0);
