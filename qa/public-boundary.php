<?php
ob_start(); require __DIR__.'/bootstrap.php'; require_once dirname(__DIR__).'/inc/lib/db.php';
if(APP_ENV!=='development'||!is_file('/.dockerenv')) exit(1);
$db=DB::getInstance();$ids=[];$tag='qa_public_'.bin2hex(random_bytes(5));
function publicBoundaryGet(string $path): array {
    $ctx=stream_context_create(['http'=>['header'=>"Host: localhost\r\n",'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1'.$path,false,$ctx);preg_match('/\s(\d{3})\s/',$http_response_header[0]??'',$m);
    return [(int)($m[1]??0),(string)$body,json_decode($body,true)];
}
try {
    foreach(['public'=>['BLUESQ','Y','N'],'hidden'=>['BLUESQ','N','N'],'secret'=>['BLUESQ','Y','Y'],'other'=>['QAOTH','Y','N']] as $kind=>$flags){
        $db->prepare('INSERT INTO nb_board(sitekey,board_no,title,contents,is_view,is_secret,secret_pwd,regdate) VALUES(?,12,?,?,?,?,?,NOW())')->execute([$flags[0],$tag.$kind,$tag.'content',$flags[1],$flags[2],'qa_private_hash']);
        $ids[$kind]=(int)$db->lastInsertId();
    }
    foreach(['/app/gerne.php','/app/api.php?board_no=12','/api/work.php?search_term='.$tag] as $path){
        [$status,$body,$json]=publicBoundaryGet($path);$rows=$json['rows']??$json['data']??[];$found=array_column($rows,'no');
        qa_expect($status===200 && in_array($ids['public'],$found),'public fixture remains available '.$path);
        qa_expect(!array_intersect([$ids['hidden'],$ids['secret'],$ids['other']],$found),'hidden secret and foreign-site fixtures absent '.$path);
        qa_expect(strpos($body,'secret_pwd')===false && strpos($body,'qa_private_hash')===false,'password field not serialized '.$path);
    }
    [$status,$body]=publicBoundaryGet('/pages/board/board.edit.php?board_no=12&no='.$ids['hidden']);
    qa_expect($status===404 && strpos($body,$tag)===false,'hidden edit denies before rendering');
    [$status,$body]=publicBoundaryGet('/pages/board/board.edit.php?board_no=12&no='.$ids['public']);
    qa_expect($status===403 && strpos($body,$tag)===false,'anonymous edit denies before rendering');
    foreach(['/api/work.php?page=-1','/api/work2.php?page=-1'] as $path){
        [$status,$body,$json]=publicBoundaryGet($path);
        qa_expect($status===200 && is_array($json) && !isset($json['query'],$json['debugSql'],$json['message']),'public pagination valid JSON without diagnostics '.$path);
    }
    foreach(['board.confirm.php','board.comment.confirm.php'] as $file){
        [$status,$body]=publicBoundaryGet('/pages/board/'.$file.'?board_no=12&mode='.rawurlencode("'-alert(97531)-'"));
        qa_expect(strpos($body,'this.dataset.mode')!==false && !preg_match('/onclick="[^"]*97531/i',$body),'confirmation mode remains data '.$file);
    }
} catch(Throwable $e){qa_expect(false,$e->getMessage());}
finally {foreach($ids as $id)$db->prepare('DELETE FROM nb_board WHERE no=? AND title LIKE ?')->execute([$id,$tag.'%']);}
qa_done();
