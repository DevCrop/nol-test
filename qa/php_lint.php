<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$bad=[];$count=0;
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__),FilesystemIterator::SKIP_DOTS));
foreach($it as $file){
    if($file->getExtension()!=='php'||strpos($file->getPathname(),DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR)!==false)continue;
    exec(escapeshellarg(PHP_BINARY).' -d short_open_tag=1 -l '.escapeshellarg($file->getPathname()).' 2>&1',$output,$status);
    if($status!==0)$bad[]=$file->getPathname().': '.implode(' ',$output);
    $output=[];$count++;
}
foreach($bad as $error)echo "FAIL {$error}\n";
echo $bad ? "FAIL PHP syntax\n" : "PASS PHP syntax ({$count} files)\n";
exit($bad?1:0);
