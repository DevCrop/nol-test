<?php

class AclModel
{
    /** @return array<string, array{view:bool,create:bool,update:bool,delete:bool}> */
    public static function fetch(int $adminNo): array
    {
        if ($adminNo < 1) {
            return [];
        }
        try {
            $stmt = DB::getInstance()->prepare(
                'SELECT menu_key, can_view, can_create, can_update, can_delete FROM nb_admin_acl WHERE admin_no = :no'
            );
            $stmt->execute([':no' => $adminNo]);
        } catch (PDOException $e) {
            error_log('[acl] nb_admin_acl 없음. php sql/migrate.php up');
            return [];
        }

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[(string) $row['menu_key']] = [
                'view' => (int) $row['can_view'] === 1,
                'create' => (int) $row['can_create'] === 1,
                'update' => (int) $row['can_update'] === 1,
                'delete' => (int) $row['can_delete'] === 1,
            ];
        }
        return $out;
    }

    /** @param array<string, array{view?:bool,create?:bool,update?:bool,delete?:bool}> $grants */
    public static function replaceAll(int $adminNo, array $grants): void
    {
        if ($adminNo < 1) {
            return;
        }
        $db = DB::getInstance();
        $db->beginTransaction();
        try {
            $del = $db->prepare('DELETE FROM nb_admin_acl WHERE admin_no = :no');
            $del->execute([':no' => $adminNo]);
            $ins = $db->prepare(
                'INSERT INTO nb_admin_acl (admin_no, menu_key, can_view, can_create, can_update, can_delete)
                 VALUES (:admin_no, :menu_key, :can_view, :can_create, :can_update, :can_delete)'
            );
            foreach ($grants as $menu => $flags) {
                $create = !empty($flags['create']);
                $update = !empty($flags['update']);
                $delete = !empty($flags['delete']);
                $view = !empty($flags['view']) || $create || $update || $delete;
                if (!$view && !$create && !$update && !$delete) {
                    continue;
                }
                $ins->execute([
                    ':admin_no' => $adminNo,
                    ':menu_key' => (string) $menu,
                    ':can_view' => $view ? 1 : 0,
                    ':can_create' => $create ? 1 : 0,
                    ':can_update' => $update ? 1 : 0,
                    ':can_delete' => $delete ? 1 : 0,
                ]);
            }
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function deleteByAdmin(int $adminNo): void
    {
        if ($adminNo < 1) {
            return;
        }
        try {
            $stmt = DB::getInstance()->prepare('DELETE FROM nb_admin_acl WHERE admin_no = :no');
            $stmt->execute([':no' => $adminNo]);
        } catch (PDOException $e) {
            error_log('[acl] nb_admin_acl 없음. php sql/migrate.php up');
        }
    }
}
