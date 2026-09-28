<?php

class SummerNote {
    private static $UPLOAD_DIR = '/uploads/board';
    private static $UPLOAD_BASE_DIR = '/uploads/board';

    public static function save($html_content){
        if (!$html_content) return;

        $projectRoot = defined('ROOT') ? ROOT : dirname(__DIR__, 2);
        $uploadDir = rtrim($projectRoot, '/\\') . self::$UPLOAD_DIR;
        $baseDir = self::$UPLOAD_BASE_DIR;

        $content = htmlspecialchars_decode($html_content);
        $content = html_entity_decode($content);
        
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        
        $images = $doc->getElementsByTagName('img');

        if ($images->length > 0) {
            foreach ($images as $img) {
                $src = $img->getAttribute('src');

                if (strpos($src, 'data:image') === 0) {
                    list($info, $data) = explode(';', $src);
                    list(, $base64_data) = explode(',', $data);

                    $mimeType = explode(':', $info)[1];
                    $extension = strtolower(trim(explode('/', $mimeType)[1] ?? ''));
                    // 확장자 화이트리스트: 클라이언트 MIME 조작 시 악성 확장자 저장 방지
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if ($extension === '' || !in_array($extension, $allowed_ext, true)) {
                        continue;
                    }

                    $filename = uniqid() . '.' . $extension;
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    file_put_contents($uploadDir . '/' . $filename, base64_decode($base64_data));
                    $img->setAttribute('src', $baseDir . '/' . $filename);
                }
            }
            return $doc->saveHTML();
        }
        
        return $html_content;
    }

    public static function delete($html_content){
        if (!$html_content) return;

        $projectRoot = defined('ROOT') ? ROOT : dirname(__DIR__, 2);
        $uploadDir = rtrim($projectRoot, '/\\') . self::$UPLOAD_DIR;
        $content = html_entity_decode(htmlspecialchars_decode($html_content));

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        
        $images = $doc->getElementsByTagName('img');

        if ($images->length === 0) return;

        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            $filename = $uploadDir . basename($src);

            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }
}
