<?php
require __DIR__ . '/bootstrap.php';
$root = dirname(__DIR__); $bad = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->getExtension() !== 'php' || strpos($file->getPathname(), DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR) !== false) continue;
    $cmd = escapeshellarg(PHP_BINARY) . ' -d short_open_tag=1 -l ' . escapeshellarg($file->getPathname()) . ' 2>&1';
    exec($cmd, $output, $status); if ($status !== 0) $bad[] = $file->getPathname() . ': ' . implode(' ', $output); $output = [];
}
foreach ($bad as $line) echo "FAIL {$line}\n";
qa_expect($bad === [], 'all PHP files parse on PHP ' . PHP_VERSION); qa_done();

