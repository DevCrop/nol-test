<?php

class SummerNote {
    private static $UPLOAD_DIR = '/uploads/board';
    private static $UPLOAD_BASE_DIR = '/uploads/board';

    public static function save($html_content){
        if (!$html_content) return;

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . self::$UPLOAD_DIR;
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
                    $extension = explode('/', $mimeType)[1];
                    
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

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . self::$UPLOAD_DIR;
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
