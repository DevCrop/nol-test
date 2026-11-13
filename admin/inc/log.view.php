<?php include __DIR__.'/log.filters.php'; ?>
<div class="no-card admin-log-results" data-pii-lock>
    <div class="no-card-header"><h2 class="no-card-title"><?=$e($logTitle)?></h2><p class="admin-log-help"><?=$logKind==='audit'?'성공한 생성·수정·삭제 작업을 최신순으로 확인합니다.':'개인정보 열람·입력·변경·삭제·다운로드 기록을 최신순으로 확인합니다.'?></p></div>
    <div class="no-card-body">
        <?php if($logData['errors']):?><div class="admin-log-error" role="alert"><?php foreach($logData['errors']as $message):?><p><?=$e($message)?></p><?php endforeach;?></div><?php endif;?>
        <p class="admin-log-total" role="status">총 <strong><?=number_format($logData['total'])?></strong>건<?php if($logData['total']):?> · <?=$logData['start']?>-<?=$logData['end']?>건 · <?=$logData['page']?>/<?=$logData['pages']?>페이지<?php endif;?></p>
        <div class="no-table-responsive"><table class="no-table admin-log-table" aria-label="<?=$e($logTitle)?>"><thead><tr><th scope="col">일시</th><th scope="col">작업자 / 접속 IP</th><th scope="col">유형</th><th scope="col"><?=$logKind==='audit'?'작업 대상':'접근 대상 / 정보주체'?></th><th scope="col"><?=$logKind==='audit'?'작업 내용':'수행업무 / 다운로드 사유'?></th></tr></thead><tbody>
        <?php foreach($logData['rows']as $row): $actorUid=trim((string)($row['actor_uid']??'')); $actorDisplay=$actorUid===''?'작업자 정보 없음':($actorUid==='비회원'?'비회원':\Security\PiiMask::identifier($actorUid)); ?><tr>
            <td data-label="일시"><time><?=$e($row['created_at'])?></time></td>
            <td data-label="작업자 / 접속 IP"><strong><?=$e($actorDisplay)?></strong><span class="admin-log-secondary"><?=$e($row['actor_ip']?:'-')?></span></td>
            <td data-label="유형"><span class="admin-log-badge admin-log-badge--<?=\Security\AdminLogView::badge((string)$row['action'])?>"><?=$e(\Security\AdminLogView::action($logKind,(string)$row['action']))?></span></td>
            <td data-label="<?=$logKind==='audit'?'작업 대상':'접근 대상 / 정보주체'?>"><strong><?=$e(\Security\AdminLogView::entity($logKind,(string)$row['entity']))?></strong><?php if($logKind==='audit'):?><span class="admin-log-secondary"><?=$e(($row['target_label']??'')?:((int)$row['target_no']>0?'번호 '.(int)$row['target_no']:'대상 정보 없음'))?></span><?php else:?><span class="admin-log-secondary"><?=$e($row['subject_label']?:'대상 정보 없음')?></span><?php endif;?></td>
            <td data-label="<?=$logKind==='audit'?'작업 내용':'수행업무 / 다운로드 사유'?>"><?php if($logKind==='audit'): $lines=\Security\AdminLogView::details($row);?><p class="admin-log-description"><?=$e(\Security\AdminLogView::summary($row))?></p><?php if($lines):?><details class="admin-log-detail"><summary><?=$row['entity']==='account'?'계정·권한 변경 상세':'처리 상세'?></summary><ul><?php foreach($lines as $line):?><li><?=$e($line)?></li><?php endforeach;?></ul></details><?php endif;?><?php else:?><p class="admin-log-description"><?=$e($row['task']?:'업무 정보 없음')?></p><span class="admin-log-secondary"><?=$row['reason']!==''?'사유: '.$e($row['reason']):($row['action']==='download'?'사유 정보 없음':'다운로드 사유 해당 없음')?></span><?php endif;?></td>
        </tr><?php endforeach;?>
        <?php if(!$logData['rows']):?><tr><td colspan="5" class="admin-log-empty"><?=$logData['errors']?'검색 조건을 확인한 후 다시 조회해주세요.':'조회 조건에 맞는 기록이 없습니다.'?></td></tr><?php endif;?>
        </tbody></table></div>
        <?php include __DIR__.'/log.pager.php'; ?>
    </div>
</div>
