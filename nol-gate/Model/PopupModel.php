<?php

class PopupModel
{
    public static function insert($data)
    {
        $db = DB::getInstance();
        $sql = "
            INSERT INTO nb_popups (
                title, popup_type, popup_path, has_link, link_url,
                sort_no, is_active, description, start_at, end_at, popup_image,
                is_unlimited, is_target,
                created_at, updated_at
            ) VALUES (
                :title, :popup_type, :popup_path, :has_link, :link_url,
                :sort_no, :is_active, :description, :start_at, :end_at, :popup_image,
                :is_unlimited, :is_target,
                NOW(), NOW()
            )
        ";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':title'        => $data['title'],
            ':popup_type'   => $data['popup_type'] ?? 1,
            ':popup_path'   => $data['popup_path'],
            ':has_link'     => $data['has_link'],
            ':link_url'     => $data['link_url'],
            ':sort_no'      => $data['sort_no'],
            ':is_active'    => $data['is_active'],
            ':description'  => $data['description'],
            ':start_at'     => $data['start_at'],
            ':end_at'       => $data['end_at'],
            ':popup_image'  => $data['popup_image'],
            ':is_unlimited' => $data['is_unlimited'],
            ':is_target'    => $data['is_target'],
        ]);
    }

    public static function bumpSortNosOnInsert(?string $popup_path = null): void
    {
        $db = DB::getInstance();
        $where = [];
        $params = [];

        if ($popup_path !== null && $popup_path !== '') {
            $where[] = 'popup_path = :popup_path';
            $params[':popup_path'] = $popup_path;
        }

        $sql = 'UPDATE nb_popups SET sort_no = sort_no + 1'
            . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    public static function update($id, $data)
    {
        $db = DB::getInstance();

        $fields = [
            'title',
            'popup_type',
            'popup_path',
            'has_link',
            'link_url',
            'sort_no',
            'is_active',
            'description',
            'start_at',
            'end_at',
            'is_unlimited',
            'is_target'
        ];

        if (!empty($data['popup_image'])) {
            $fields[] = 'popup_image';
        }

        $setClauses = array_map(fn($f) => "$f = :$f", $fields);
        $setClauses[] = "updated_at = NOW()";

        $sql = "UPDATE nb_popups SET " . implode(", ", $setClauses) . " WHERE id = :id";
        $stmt = $db->prepare($sql);

        // 플레이스홀더에 맞게 파라미터 준비
        $params = [];
        foreach ($fields as $field) {
            if ($field === 'popup_type' && !isset($data[$field])) {
                $params[":$field"] = 1; // 기본값
            } else {
                $params[":$field"] = $data[$field] ?? null;
            }
        }
        $params[':id'] = $id;

        return $stmt->execute($params);
    }

    public static function delete($id)
    {
        $db = DB::getInstance();

        $stmt = $db->prepare("SELECT sort_no FROM nb_popups WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $sortNo = $stmt->fetchColumn();

        if ($sortNo === false) return false;

        $stmt = $db->prepare("DELETE FROM nb_popups WHERE id = :id");
        $result = $stmt->execute([':id' => $id]);

        if ($result) {
            $stmt = $db->prepare("
                UPDATE nb_popups 
                SET sort_no = sort_no - 1 
                WHERE sort_no > :sortNo
            ");
            $stmt->execute([':sortNo' => $sortNo]);
        }

        return $result;
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

        $stmt = $db->prepare("SELECT sort_no FROM nb_popups WHERE id IN (" . implode(', ', $placeholders) . ")");
        $stmt->execute($params);
        $sortNos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($sortNos)) return false;

        $minSortNo = min($sortNos);
        $deletedCount = count($sortNos);

        $sql = "DELETE FROM nb_popups WHERE id IN (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            $stmt = $db->prepare("
                UPDATE nb_popups 
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
        $stmt = $db->prepare("SELECT * FROM nb_popups WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getMaxSortNo(): int
    {
        $db = DB::getInstance();
        $stmt = $db->query("SELECT MAX(sort_no) FROM nb_popups");
        return (int) $stmt->fetchColumn();
    }

    public static function shiftSortNosForUpdate(int $oldNo, int $newNo, int $id): void
    {
        $db = DB::getInstance();

        if ($newNo === $oldNo) return;

        if ($newNo < $oldNo) {
            $sql = "UPDATE nb_popups SET sort_no = sort_no + 1 
                    WHERE sort_no >= :newNo AND sort_no < :oldNo AND id != :id";
        } else {
            $sql = "UPDATE nb_popups SET sort_no = sort_no - 1 
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
        $stmt = $db->query("SELECT MIN(sort_no) FROM nb_popups");
        return (int)$stmt->fetchColumn();
    }
}