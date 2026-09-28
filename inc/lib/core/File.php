<?php 

class File {
    /**
     * 최대 파일 크기 (10MB)
     */
    public static $MAX_SIZE = 1024 * 1024 * 10; // 10MB

    /**
     * 파일 확장자 그룹 (이미지, 문서, 오디오 등)
     */
    public const EXTENSION_IMAGE = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
    public const EXTENSION_DOCUMENT = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'];
    public const EXTENSION_ARCHIVE = ['zip', 'rar', '7z', 'tar', 'gz', 'iso', 'dmg'];
    public const EXTENSION_AUDIO = ['mp3', 'wav', 'ogg', 'flac', 'aac'];
    public const EXTENSION_VIDEO = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'webm'];
    public const EXTENSION_DATA = ['csv', 'json', 'xml'];

    public $key = null;
    public $entity = [];
    public $extensions = [];

    public function __construct($key = null, $extensions = []) {
        $this->key = $key; 
        $this->entity = $_FILES[$this->key] ?? null;
        $this->extensions = is_array($extensions) && !empty($extensions) ? $extensions : self::getAllExtensions();
    }

    /**
     * 모든 확장자를 하나의 배열로 반환
     */
    public static function getAllExtensions() {
        return array_merge(
            self::EXTENSION_IMAGE,
            self::EXTENSION_DOCUMENT,
            self::EXTENSION_ARCHIVE,
            self::EXTENSION_AUDIO,
            self::EXTENSION_VIDEO,
            self::EXTENSION_DATA
        );
    }

    public function setEntity($fileData)
    {
        $this->entity = $fileData; 
    }

    /**
     * 파일이 존재하는지 확인
     */
    public function has() {
        return !is_null($this->entity);
    }

    /**
     * 파일이 비어있는지 확인
     */
    public function empty() {
        return isset($this->entity['size']) && $this->entity['size'] === 0;
    }

    /**
     * 파일이 최대 크기를 초과했는지 확인
     */
    public function isExceedMaximumSize() {
        return isset($this->entity['size']) && $this->entity['size'] > self::$MAX_SIZE;
    }

    /**
     * 파일 확장자 검증
     */
    public function isValidExtension() {
        $ext = pathinfo($this->entity['name'], PATHINFO_EXTENSION);
        return in_array(strtolower($ext), $this->extensions, true);
    }

    /**
     * 파일이 업로드 가능한지 검증
     */
    public function isValid() {
        return $this->has() && !$this->empty() && !$this->isExceedMaximumSize() && $this->isValidExtension();
    }

    /**
     * 원본 파일명 반환
     */
    public function getOriginalName() {
        return $this->entity['name'] ?? null;
    }

    /**
     * MIME 타입 반환
     */
    public function getMimeType() {
        return $this->entity['type'] ?? null;
    }

    /**
     * 파일 크기 반환
     */
    public function getSize() {
        return $this->entity['size'] ?? 0;
    }

    /**
     * 파일의 임시 저장소 경로 반환
     */
    public function getTmpName() {
        return $this->entity['tmp_name'] ?? null;
    }
}

