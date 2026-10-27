<?php
// Execute the real endpoint catch blocks with isolated dependency failures.
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('APP_ENV')!=='development')exit(1);
$dir=sys_get_temp_dir().'/blue-auth-errors-'.bin2hex(random_bytes(6));
mkdir($dir.'/inc/lib',0700,true);
$bootstrap=<<<'PHP'
<?php
namespace Security {
    final class AdminAccount {
        public static function findByUid($uid) { throw $GLOBALS['qa_exception']; }
    }
    final class Mfa {
        public static function bindEmail($email) { throw $GLOBALS['qa_exception']; }
        public static function resend() { throw $GLOBALS['qa_exception']; }
        public static function verify($code) { throw $GLOBALS['qa_exception']; }
    }
}
namespace { function blue_admin_url($path) { return $path; } }
PHP;
file_put_contents($dir.'/inc/lib/base.class.php',$bootstrap);
$fail=0;
try {
    foreach(['login.process.php','mfa.email.php','mfa.process.php','mfa.resend.php'] as $file) {
        foreach(['PDOException','RuntimeException','Error'] as $type) {
            $message='QA_ONLY_SQL_PATH_SENTINEL';
            $endpoint=dirname(__DIR__).'/admin/lib/login/'.$file;
            $script='<?php $_SERVER["DOCUMENT_ROOT"]='.var_export($dir,true).'; $_SERVER["REQUEST_METHOD"]="POST";'
                .'$_POST=["uid"=>"qa_user","upwd"=>"QaPassword9!","r_captcha"=>"1234","mfa_email"=>"qa@example.com","code"=>"123456"];'
                .'$_SESSION=["captcha_secure"=>"1234"]; $GLOBALS["qa_exception"]=new '.$type.'('.var_export($message,true).');'
                .'register_shutdown_function(function(){echo json_encode($_SESSION);}); require '.var_export($endpoint,true).';';
            file_put_contents($dir.'/request.php',$script);
            $output=[];$status=0;exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg($dir.'/request.php'),$output,$status);
            $state=json_decode(implode("\n",$output),true);
            $actual=$state[$file==='login.process.php'?'admin_flash_error':'mfa_error']??'';
            $fallback=$file==='login.process.php'?'로그인을 처리할 수 없습니다.':($file==='mfa.process.php'?'인증을 처리할 수 없습니다.':'인증 메일을 발송할 수 없습니다.');
            $ok=$status===0 && $actual===($type==='RuntimeException'?$message:$fallback) && empty($state['no_adm_login_no']) && empty($state['no_adm_login_token']);
            echo ($ok?'PASS ':'FAIL ').$file.' '.$type.($type==='RuntimeException'?' retains user guidance':' hides internal details').PHP_EOL;
            if(!$ok)$fail++;
        }
    }
}finally{
    foreach([$dir.'/request.php',$dir.'/inc/lib/base.class.php'] as $path)if(is_file($path))unlink($path);
    rmdir($dir.'/inc/lib');rmdir($dir.'/inc');rmdir($dir);
}
echo $fail?"AUTH ERROR QA FAILED\n":"AUTH ERROR QA PASSED\n";exit($fail?1:0);
