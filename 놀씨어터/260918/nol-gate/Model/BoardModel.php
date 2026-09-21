<?php
class BoardModel
{
    // 최소 sort_no 가져오기
    public static function getMinSortNo()
    {
        $db = DB::getInstance();
        $stmt = $db->query("SELECT MIN(sort_no) AS min_no FROM nb_board");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['min_no'] ?? 0);
    }

    // 최대 sort_no 가져오기
    public static function getMaxSortNo()
    {
        $db = DB::getInstance();
        $stmt = $db->query("SELECT MAX(sort_no) AS max_no FROM nb_board");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['max_no'] ?? 0);
    }

    // 순서 변경 시 다른 데이터들 sort_no 밀기/당기기
    public static function shiftSortNosForUpdate($oldNo, $newNo, $no)
    {
        $db = DB::getInstance();

        if ($newNo < $oldNo) {
            // 앞으로 이동 → 기존 사이 값들 뒤로 +1
            $stmt = $db->prepare("
                UPDATE nb_board 
                SET sort_no = sort_no + 1 
                WHERE sort_no >= :new_no AND sort_no < :old_no AND no != :no
            ");
        } else {
            // 뒤로 이동 → 기존 사이 값들 앞으로 -1
            $stmt = $db->prepare("
                UPDATE nb_board 
                SET sort_no = sort_no - 1 
                WHERE sort_no <= :new_no AND sort_no > :old_no AND no != :no
            ");
        }

        $stmt->execute([
            ':new_no' => $newNo,
            ':old_no' => $oldNo,
            ':no'     => $no
        ]);
    }

    // 게시물 수정 (순서 변경 후)
    public static function update($no, $data)
    {
        $db = DB::getInstance();

        $fields = [];
        $params = [':no' => $no];

        foreach ($data as $key => $value) {
            $fields[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }

        $sql = "UPDATE nb_board SET " . implode(', ', $fields) . " WHERE no = :no";
        $stmt = $db->prepare($sql);

        return $stmt->execute($params);
    }
}