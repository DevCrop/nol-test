<?php

class BannerModel
{
    public static function insert($data)
    {
        $db = DB::getInstance();
        $sql = "
            INSERT INTO nb_banners (
                title, banner_type, hall_id, has_link, link_url,
                sort_no, is_active, description, start_at, end_at,
                display_start_at, display_end_at,
                banner_image, banner_image_mobile,
                is_unlimited, is_target,
                created_at, updated_at
            ) VALUES (
                :title, :banner_type, :hall_id, :has_link, :link_url,
                :sort_no, :is_active, :description, :start_at, :end_at,
                :display_start_at, :display_end_at,
                :banner_image, :banner_image_mobile,
                :is_unlimited, :is_target,
                NOW(), NOW()
            )
        ";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':title'        => $data['title'],
            ':banner_type'  => $data['banner_type'],
            ':hall_id'      => $data['hall_id'] ?? null,
            ':has_link'     => $data['has_link'],
            ':link_url'     => $data['link_url'],
            ':sort_no'      => $data['sort_no'],
            ':is_active'    => $data['is_active'],
            ':description'  => $data['description'],
            ':start_at'     => $data['start_at'],
            ':end_at'       => $data['end_at'],
            ':display_start_at' => $data['display_start_at'],
            ':display_end_at'   => $data['display_end_at'],
            ':banner_image' => $data['banner_image'],
            ':banner_image_mobile' => $data['banner_image_mobile'] ?? null,
            ':is_unlimited' => $data['is_unlimited'],
            ':is_target'    => $data['is_target'],
        ]);
    }

    public static function bumpSortNosOnInsert(?int $banner_type = null): void
    {
        $db = DB::getInstance();
        $where = [];
        $params = [];
        if ($banner_type !== null) {
            $where[] = 'banner_type = :banner_type';
            $params[':banner_type'] = $banner_type;
        }
        $sql = 'UPDATE nb_banners SET sort_no = sort_no + 1' . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    public static function update($id, $data)
    {
        $db = DB::getInstance();
        $fields = [
            'title',
            'banner_type',
            'hall_id',
            'has_link',
            'link_url',
            'sort_no',
            'is_active',
            'description',
            'start_at',
            'end_at',
            'display_start_at',
            'display_end_at',
            'is_unlimited',
            'is_target'
        ];
        if (!empty($data['banner_image'])) {
            $fields[] = 'banner_image';
        }
        if (!empty($data['banner_image_mobile'])) {
            $fields[] = 'banner_image_mobile';
        }
        $setClauses = array_map(fn($f) => "$f = :$f", $fields);
        $setClauses[] = "updated_at = NOW()";
        $sql = "UPDATE nb_banners SET " . implode(", ", $setClauses) . " WHERE id = :id";
        $stmt = $db->prepare($sql);

        // 플레이스홀더에 맞게 파라미터 준비
        $params = [];
        foreach ($fields as $field) {
            $params[":$field"] = $data[$field] ?? null;
        }
        $params[':id'] = $id;

        return $stmt->execute($params);
    }

    public static function delete($id)
    {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT sort_no FROM nb_banners WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $sortNo = $stmt->fetchColumn();
        if ($sortNo === false) return false;
        $stmt = $db->prepare("DELETE FROM nb_banners WHERE id = :id");
        $deleted = $stmt->execute([':id' => $id]);
        if ($deleted) {
            $stmt = $db->prepare("UPDATE nb_banners SET sort_no = sort_no - 1 WHERE sort_no > :sortNo");
            $stmt->execute([':sortNo' => $sortNo]);
        }
        return $deleted;
    }

    public static function deleteMultiple(array $ids)
    {
        if (empty($ids)) return false;
        $db = DB::getInstance();
        $placeholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $key = ":id$index";
            $placeholders[] = $key;
            $params[$key] = (int)$id;
        }
        $stmt = $db->prepare("SELECT sort_no FROM nb_banners WHERE id IN (" . implode(', ', $placeholders) . ")");
        $stmt->execute($params);
        $sortNos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($sortNos)) return false;
        $minSortNo = min($sortNos);
        $deletedCount = count($sortNos);
        $sql = "DELETE FROM nb_banners WHERE id IN (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        if ($result) {
            $stmt = $db->prepare("
                UPDATE nb_banners 
                SET sort_no = sort_no - :deletedCount 
                WHERE sort_no > :minSortNo
            ");
            $stmt->execute([
                ':deletedCount' => $deletedCount,
                ':minSortNo'    => $minSortNo
            ]);
        }
        return $result;
    }

    public static function find($id)
    {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM nb_banners WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getMaxSortNo(): int
    {
        $db = DB::getInstance();
        $stmt = $db->query("SELECT MAX(sort_no) FROM nb_banners");
        return (int) $stmt->fetchColumn();
    }

    public static function shiftSortNosForUpdate(int $oldNo, int $newNo, int $id): void
    {
        $db = DB::getInstance();
        if ($newNo === $oldNo) return;
        if ($newNo < $oldNo) {
            $sql = "UPDATE nb_banners SET sort_no = sort_no + 1 
                    WHERE sort_no >= :newNo AND sort_no < :oldNo AND id != :id";
        } else {
            $sql = "UPDATE nb_banners SET sort_no = sort_no - 1 
                    WHERE sort_no > :oldNo AND sort_no <= :newNo AND id != :id";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':newNo' => $newNo,
            ':oldNo' => $oldNo,
            ':id'    => $id,
        ]);
    }

    public static function getMinSortNo(): int
    {
        $db = DB::getInstance();
        $stmt = $db->query("SELECT MIN(sort_no) FROM nb_banners");
        return (int)$stmt->fetchColumn();
    }
}
