<?php $f=$logData['filters']; ?>
<form class="no-card admin-log-filters" method="get" action="<?=$e($logPath)?>" aria-label="<?=$e($logTitle)?> 검색">
    <div class="no-card-header"><h2 class="no-card-title">기록 검색</h2></div>
    <div class="no-card-body admin-log-filter-grid">
        <label class="admin-log-field">시작일<input type="date" name="from" value="<?=$e($f['from'])?>" class="no-input--detail"></label>
        <label class="admin-log-field">종료일<input type="date" name="to" value="<?=$e($f['to'])?>" class="no-input--detail"></label>
        <label class="admin-log-field">대상<select name="entity" class="no-input--detail"><option value="">전체 대상</option><?php foreach(\Security\AdminLogReader::entities($logKind)as $key=>$label):?><option value="<?=$e($key)?>"<?=$f['entity']===$key?' selected':''?>><?=$e($label)?></option><?php endforeach;?></select></label>
        <label class="admin-log-field">유형<select name="action" class="no-input--detail"><option value="">전체 유형</option><?php foreach(\Security\AdminLogReader::actions($logKind)as $key=>$label):?><option value="<?=$e($key)?>"<?=$f['action']===$key?' selected':''?>><?=$e($label)?></option><?php endforeach;?></select></label>
        <label class="admin-log-field admin-log-query">검색어<input type="search" name="q" value="<?=$e($f['q'])?>" maxlength="200" placeholder="<?=$logKind==='audit'?'작업자 ID, IP, 대상 또는 작업 내용':'작업자 ID, IP, 마스킹된 이름, 업무 또는 사유'?>" class="no-input--detail"></label>
        <label class="admin-log-field">페이지당 건수<select name="perpage" class="no-input--detail"><?php foreach([20,50,100]as $size):?><option value="<?=$size?>"<?=$f['perpage']===$size?' selected':''?>><?=$size?>건</option><?php endforeach;?></select></label>
        <div class="admin-log-filter-actions"><button type="submit" class="no-btn no-btn--main">검색</button><a class="no-btn no-btn--normal" href="<?=$e($logPath)?>">초기화</a></div>
    </div>
</form>
