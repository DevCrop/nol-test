<?php
if (PHP_SAPI !== 'cli') exit(1);
$root=dirname(__DIR__);$count=0;$failed=0;
foreach ([$root.'/nol-gate',$root.'/inc'] as $directory) {
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,FilesystemIterator::SKIP_DOTS)) as $file) {
        if($file->getExtension()!=='php')continue;
        $output=[];$status=0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file->getPathname()).' 2>&1',$output,$status);
        $count++;if($status){$failed++;echo implode("\n",$output)."\n";}
    }
}
echo "PHP lint files={$count} fail={$failed}\n";exit($failed?1:0);
