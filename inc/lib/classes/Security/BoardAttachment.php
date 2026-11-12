<?php
namespace Security;

/** Resolve board attachments with site, visibility and filesystem boundaries. */
final class BoardAttachment
{
    private const FIELDS = [
        'thumb'=>['thumb_image','thumb_image'],
        'attach1'=>['file_attach_1','file_attach_origin_1'],
        'attach2'=>['file_attach_2','file_attach_origin_2'],
        'attach3'=>['file_attach_3','file_attach_origin_3'],
        'attach4'=>['file_attach_4','file_attach_origin_4'],
        'attach5'=>['file_attach_5','file_attach_origin_5'],
    ];
    public static function supports(string $field): bool { return isset(self::FIELDS[$field]); }
    public static function find(\PDO $db, string $directory, int $id, string $field, bool $publicOnly): ?array
    {
        if ($id < 1 || !self::supports($field)) return null;
        $stmt = $db->prepare('SELECT * FROM nb_board WHERE no=? AND sitekey=? LIMIT 1');
        $stmt->execute([$id,'BLUESQ']); $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || ($publicOnly && (($row['is_view']??'N')!=='Y' || ($row['is_secret']??'N')==='Y'))) return null;
        [$storedKey,$originKey] = self::FIELDS[$field]; $stored = (string)($row[$storedKey]??'');
        if ($stored === '' || basename($stored)!==$stored || strpos($stored,'\\')!==false) return null;
        $path = UploadGuard::pathInside($directory,$stored);
        if ($path === null || !is_readable($path)) return null;
        $name = (string)($row[$originKey]??'');
        return ['path'=>$path,'name'=>$name!==''?$name:$stored];
    }
}
