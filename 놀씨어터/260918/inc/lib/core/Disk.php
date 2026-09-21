<?php 

class Disk {
    private $rootDir;

    public function __construct($rootDir = '') {
        $defaultRoot = defined('ROOT') ? rtrim(ROOT, '/\\') . '/uploads' : dirname(__DIR__, 3) . '/uploads';
        $this->setRootDir($rootDir ?: $defaultRoot);
    }

    public function setRootDir($rootDir) {
        $this->rootDir = rtrim($rootDir, '/') . '/';
        $this->createDirectory($this->rootDir);
    }

    public function setDir($subDir) {
        $dir = $this->rootDir . trim($subDir, '/') . '/';
        $this->createDirectory($dir);
        return $dir;
    }

    private function createDirectory($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    public function upload(File $file) {
        if (!$file->isValid()) {
            throw new Exception('파일 업로드 중 문제가 발생하였습니다.'); 
        }

        $storedName = uniqid() . '_' . basename($file->getOriginalName());
        $targetPath = $this->rootDir . $storedName;

        if (!move_uploaded_file($file->getTmpName(), $targetPath)) {
            throw new Exception('파일 업로드가 실패하였습니다.'); 
        }

        return [
            'success' => true,
            'original_name' => $file->getOriginalName(),
            'stored_name' => $storedName,
            'directory' => $this->rootDir,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize()
        ];
    }

    public function uploadMany(array $files) {
        $results = [];

        foreach ($files['name'] as $index => $name) {
            $fileData = [
                'name' => $files['name'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'size' => $files['size'][$index],
                'type' => $files['type'][$index],
                'error' => $files['error'][$index]
            ];
            
            $file = new File(null);
            $file->setEntity($fileData); 
    
            if ($file->isValid()) {
                try {
                    $results[] = $this->upload($file);
                } catch (Exception $e) {
                    $results[] = ['success' => false, 'error' => $e->getMessage()];
                }
            } else {
                $results[] = [
                    'success' => false,
                    'error' => '유효하지 않은 파일입니다: ' . $file->getOriginalName()
                ];
            }
        }
    
        return $results;
    }
    
}
