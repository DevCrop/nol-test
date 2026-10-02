<?php
namespace Security;

final class MainBanner
{
    public static function visible(\PDO $db, string $site, string $today): array
    {
        // Compare legacy zero dates as text; MySQL strict mode rejects a zero DATE literal.
        $stmt = $db->prepare("SELECT * FROM nb_banner WHERE sitekey = :site AND b_loc = 'site_main'
            AND b_view = 'Y' AND (b_none_view = 'Y'
                OR (b_sdate_view <= :start_day AND b_edate_view >= :end_day)
                OR (CAST(b_sdate_view AS CHAR) = '0000-00-00' AND CAST(b_edate_view AS CHAR) = '0000-00-00'))
            ORDER BY b_idx ASC, no ASC");
        $stmt->execute(['site' => $site, 'start_day' => $today, 'end_day' => $today]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
