<?php
require_once dirname(__DIR__) . '/lib/GroupedOrder.php';

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
        $order = new GroupedOrder($db, 'nb_popups');
        return $order->locked(function () use ($db, $sql, $data, $order) {
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
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
        if ($result) $order->head(['popup_path'=>$data['popup_path']], (int)$db->lastInsertId());
        return $result;
        });
    }

    public static function bumpSortNosOnInsert(?string $popup_path = null): void
    {
        if ($popup_path === null) throw new InvalidArgumentException('Popup path is required');
        (new GroupedOrder(DB::getInstance(), 'nb_popups'))->head(['popup_path'=>$popup_path]);
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

        $order = new GroupedOrder($db, 'nb_popups');
        return $order->locked(function () use ($id, $data, $params, $stmt, $order) {
            $current = self::find($id);
            if (!$current) return false;
            $params[':sort_no'] = $order->move($current, ['popup_path'=>$data['popup_path']], (int)$data['sort_no']);
            return $stmt->execute($params);
        });
    }

    public static function delete($id)
    {
        return (new GroupedOrder(DB::getInstance(), 'nb_popups'))->remove([(int)$id]);
    }

    public static function deleteMultiple(array $ids)
    {
        return (new GroupedOrder(DB::getInstance(), 'nb_popups'))->remove($ids);
    }

    public static function find($id)
    {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM nb_popups WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getMaxSortNo(string $path): int
    {
        return (new GroupedOrder(DB::getInstance(), 'nb_popups'))->maximum(['popup_path'=>$path]);
    }

    // Compatibility entry: selected-row updates now own the entire grouped move.
    public static function shiftSortNosForUpdate(int $oldNo, int $newNo, int $id): void
    {
        $row = self::find($id);
        if (!$row) return;
        $row['sort_no'] = $newNo;
        self::update($id, $row);
    }

    public static function getMinSortNo(string $path): int
    {
        $s = DB::getInstance()->prepare('SELECT COALESCE(MIN(sort_no),1) FROM nb_popups WHERE popup_path <=> ?');
        $s->execute([$path]); return (int)$s->fetchColumn();
    }

}
