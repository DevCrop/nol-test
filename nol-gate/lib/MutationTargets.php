<?php

/** Server-selected tables only; normalize bulk IDs before mutations and audit. */
final class MutationTargets
{
    private const KEYS = [
        'nb_admin'=>'no', 'nb_banners'=>'id', 'nb_popups'=>'id', 'nb_faqs'=>'id',
        'nb_site_tags'=>'id', 'nb_branch_seos'=>'id', 'nb_works'=>'id',
        'nb_privacy_policy'=>'id', 'nb_request'=>'no', 'nb_board'=>'no',
        'nb_board_manage'=>'no', 'nb_board_comment'=>'no',
    ];

    public static function existing(string $table, array $ids): array
    {
        if (!isset(self::KEYS[$table])) throw new InvalidArgumentException('Unknown mutation target');
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function($id){ return $id > 0; })));
        if (!$ids) return [];
        $column = self::KEYS[$table];
        $q = DB::getInstance()->prepare('SELECT '.$column.' FROM '.$table.' WHERE '.$column.' IN ('.implode(',', array_fill(0,count($ids),'?')).')');
        $q->execute($ids);
        return array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
    }
}
