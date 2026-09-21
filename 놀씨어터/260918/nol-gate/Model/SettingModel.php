<?php

class SiteTagModel {

    /**
     * 태그 삽입
     */
    public static function insert($data) {
        $db = DB::getInstance();
        global $NO_SITE_UNIQUE_KEY;

        $sql = "
            INSERT INTO nb_site_tags (sitekey, title, tag_content, location, is_active)
            VALUES (:sitekey, :title, :tag_content, :location, :is_active)
        ";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':sitekey' => $NO_SITE_UNIQUE_KEY,
            ':title' => $data['title'],
            ':tag_content' => $data['tag_content'],
            ':location' => $data['location'] ?? 1,
            ':is_active' => $data['is_active'] ?? 1,
        ]);
    }


    /**
     * 태그 수정
     */
    public static function update($id, $data) {
        $db = DB::getInstance();

        $fields = [
            'title' => $data['title'],
            'tag_content' => $data['tag_content'],
            'location' => $data['location'] ?? 1,
            'is_active' => $data['is_active'] ?? 1,
        ];

        $set = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($fields)));
        $fields['id'] = $id;

        $sql = "UPDATE nb_site_tags SET $set WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($fields);
    }


    /**
     * 태그 삭제
     */
    public static function delete($id) {
        $db = DB::getInstance();

        $sql = "DELETE FROM nb_site_tags WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }



    public static function deleteMultiple(array $ids): bool
    {
        if (empty($ids)) return false;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $db = DB::getInstance();
        $stmt = $db->prepare("DELETE FROM nb_site_tags WHERE id IN ($placeholders)");

        return $stmt->execute($ids);
    }

    
}