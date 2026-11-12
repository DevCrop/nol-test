<?php
require __DIR__.'/bootstrap.php';
foreach (['PARK YESEUL'=>'P*** Y*****','Lionel Andres Messi'=>'L***** A***** M****','Lionel'=>'L*****','Élodie Dupont'=>'É***** D*****','  PARK   YESEUL  '=>'P*** Y*****','A B'=>'* *','홍길동'=>'홍*동','남궁민수'=>'남**수','김철'=>'김*'] as $raw=>$expected) {
 qa_expect(\Security\PiiMask::name($raw)===$expected,'name masking boundary '.bin2hex($raw));
}
qa_done();
