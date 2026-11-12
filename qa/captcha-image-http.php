<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('APP_ENV')!=='development'||getenv('DB_USER')!=='qa_app')exit(1);
$host=getenv('GATE_HOST')?:'gate.local';$failed=0;$passed=0;
$check=static function(bool $ok,string $label)use(&$failed,&$passed){echo($ok?'PASS ':'FAIL ').$label.PHP_EOL;$ok?$passed++:$failed++;};
$request=static function(string $path)use($host):array{
    $ctx=stream_context_create(['http'=>['header'=>"Host: ".$host."\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);
    preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body,implode("\n",$http_response_header)];
};
[$status,$png,$headers]=$request('/index.php/inc/lib/captcha.admin.n.php');
$check($status===200,'CAPTCHA routed request succeeds');
$check(stripos($headers,'Content-Type: image/png')!==false,'CAPTCHA PNG content type');
$check(substr($png,0,8)==="\x89PNG\r\n\x1a\n",'PNG starts at byte zero without bootstrap whitespace');
$info=@getimagesizefromstring($png);
$check(is_array($info)&&$info[0]===100&&$info[1]===30&&$info[2]===IMAGETYPE_PNG,'image decoder accepts complete 100x30 PNG');
$image=@imagecreatefromstring($png);$check($image!==false,'GD decodes routed image');if($image!==false)imagedestroy($image);
$check(substr($png,-8)==="IEND\xaeB`\x82",'no HTML or whitespace after PNG');
[$loginStatus,$login]=$request('/');
$check($loginStatus===200&&strpos($login,'/index.php/inc/lib/captcha.admin.n.php')!==false,'login keeps correct routed CAPTCHA URL');
echo "$passed PASS / $failed FAIL\n";exit($failed?1:0);
