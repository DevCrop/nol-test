<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('TRUSTED_PROXY_CIDRS', '10.0.0.0/8');
require __DIR__.'/bootstrap.php';
qa_expect(\Security\ClientIp::get(['REMOTE_ADDR'=>'10.1.1.1','HTTP_X_FORWARDED_FOR'=>'192.168.1.1, 203.0.113.7'])==='203.0.113.7','trusted ALB ignores attacker-prepended allowed IP');
qa_expect(\Security\ClientIp::get(['REMOTE_ADDR'=>'10.1.1.1','HTTP_X_FORWARDED_FOR'=>'203.0.113.7, 10.2.2.2'])==='203.0.113.7','trusted multi-proxy chain resolves nearest untrusted client');
qa_expect(\Security\ClientIp::get(['REMOTE_ADDR'=>'203.0.113.7','HTTP_X_FORWARDED_FOR'=>'192.168.1.1'])==='203.0.113.7','direct attacker cannot supply client IP');
qa_done();
