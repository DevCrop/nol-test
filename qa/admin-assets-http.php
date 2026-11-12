<?php
if(PHP_SAPI!=='cli'||!is_file('/.dockerenv')||getenv('APP_ENV')!=='development'||getenv('DB_USER')!=='qa_app'||getenv('GATE_ROUTE_MODE')!=='php')exit(1);
ob_start();require __DIR__.'/bootstrap.php';require dirname(__DIR__).'/inc/lib/db.php';
$root=dirname(__DIR__);$db=DB::getInstance();$uid='qa_assets_'.bin2hex(random_bytes(4));$id=0;$sid='';
$request=static function(string $path,string $method='GET')use(&$sid):array{
    $headers='Host: '.getenv('GATE_HOST')."\r\nCookie: ".session_name().'='.$sid."\r\n";
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>$headers,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=(string)file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),$body,implode("\n",$http_response_header)];
};
try{
    $db->prepare("INSERT INTO nb_admin(sitekey,uid,upwd,uname,email,active_status,role_code,password_changed_at,last_login_at,created_at)VALUES('BLUESQ',?,?,?,?,'Y','super',NOW(),NOW(),NOW())")->execute([$uid,password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT),'QA',$uid.'@example.invalid']);
    $id=(int)$db->lastInsertId();\Security\AdminAccount::establish(\Security\AdminAccount::findByNo($id));$sid=session_id();session_write_close();
    if(posix_geteuid()===0)chown((session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid,'www-data');
    $scripts=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/admin/pages',FilesystemIterator::SKIP_DOTS));
    foreach($scripts as $file){
        if($file->getExtension()!=='js')continue;
        $rel=substr($file->getPathname(),strlen($root.'/admin/'));$path='/index.php/'.$rel;
        [$status,$body,$headers]=$request($path);qa_expect($status===200&&strpos($headers,'application/javascript')!==false,'page script loaded '.$rel);
        if($rel==='pages/board/js/board.process.js')qa_expect(strpos($body,'async function doEditSave(')!==false&&strpos($body,".summernote(")!==false,'board save and Summernote initialization delivered together');
    }
    $post=(int)$db->query('SELECT no FROM nb_board ORDER BY no DESC LIMIT 1')->fetchColumn();
    if(!$post)throw new RuntimeException('Current backup must contain a board record.');
    [$status,$html]=$request('/index.php/pages/board/board.view.php?no='.$post.'&page=1');qa_expect($status===200,'real board edit HTML loads');
    libxml_use_internal_errors(true);$dom=new DOMDocument();$dom->loadHTML($html);$base='';
    foreach($dom->getElementsByTagName('base')as $node)$base=$node->getAttribute('href');
    qa_expect($base==='/index.php/pages/board/','board relative URL base points to PHP route');
    $refs=[];foreach($dom->getElementsByTagName('script')as $node)if($node->hasAttribute('src'))$refs[]=$node->getAttribute('src');
    foreach($dom->getElementsByTagName('link')as $node)if($node->getAttribute('rel')==='stylesheet')$refs[]=$node->getAttribute('href');
    foreach(array_unique($refs)as $ref){
        if(preg_match('#^(?:https?:)?//#',$ref))continue;
        $path=$ref[0]==='/'?$ref:$base.preg_replace('#^\./#','',$ref);
        [$s,$body,$headers]=$request($path);qa_expect($s===200&&$body!==''&&stripos($headers,'Location:')===false,'board dependency '.$path);
    }
    foreach(['resource/vendor/boxicons/fonts/boxicons.woff2','resource/vendor/fontawsome/webfonts/fa-solid-900.woff2','resource/vendor/summernote/font/summernote.woff2']as $rel){
        [$s,$body,$headers]=$request('/index.php/'.$rel);$size=strlen($body)>=12?unpack('Nlength',substr($body,8,4))['length']:0;
        qa_expect($s===200&&strpos($headers,'font/woff2')!==false&&substr($body,0,4)==='wOF2'&&$size===strlen($body)&&hash_equals(hash_file('sha256',$root.'/'.$rel),hash('sha256',$body)),'valid binary font delivered '.$rel);
    }
    [$s,$body]=$request('/index.php/pages/board/js/board.process.js','HEAD');qa_expect($s===200&&$body==='','page-script HEAD returns no body');
    [$s]=$request('/index.php/pages/board/js/board.process.js','POST');qa_expect($s===405,'page-script POST rejected');
    foreach(['/index.php/.env','/index.php/pages/member/js/member.process.js','/index.php/pages/board/js/../ajax/board.process.php','/index.php/inc/lib/PhpGateRouter.php']as $path){[$s]=$request($path);qa_expect(in_array($s,[400,403,404],true),'private or traversal route remains blocked '.$path);}
}finally{
    if($id)$db->prepare("DELETE FROM nb_admin WHERE no=? AND uid=?")->execute([$id,$uid]);
    if($sid){$path=(session_save_path()?:sys_get_temp_dir()).'/sess_'.$sid;if(is_file($path))unlink($path);}
}
qa_done();
