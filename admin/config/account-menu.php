<?php
// NOL menu.config.php와 같은 단일 정의 방식. 블루의 실제 경로만 연결.
return [
    'title' => '계정 및 권한 관리',
    'subs' => [
        ['title'=>'계정 관리', 'url'=>'index.php', 'active'=>['index.php','edit.php']],
        ['title'=>'계정 생성', 'url'=>'new.php', 'active'=>['new.php']],
        ['title'=>'작업 이력', 'url'=>'audit.php', 'active'=>['audit.php']],
        ['title'=>'개인정보 접속기록', 'url'=>'access.php', 'active'=>['access.php']],
    ],
];
