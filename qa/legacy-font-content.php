<?php
require __DIR__ . '/bootstrap.php';
$legacy = '<div><h2><span><font face="Arial" color="red" onclick="alert(1)">채용 안내</font></span></h2><p><font size="3">지원 방법</font></p><ul><li><font>제출 서류</font></li></ul></div>';
$clean = \Security\HtmlSanitizer::clean(htmlspecialchars($legacy, ENT_QUOTES, 'UTF-8'));
qa_expect(strpos($clean, '채용 안내') !== false && strpos($clean, '지원 방법') !== false && strpos($clean, '제출 서류') !== false, 'legacy heading paragraph and list text preserved');
qa_expect(!preg_match('/(?:face|color|onclick|size)=/i', $clean), 'legacy font attributes removed');
$roundTrip = \Security\HtmlSanitizer::clean(htmlspecialchars($clean, ENT_QUOTES, 'UTF-8'));
qa_expect($roundTrip === $clean, 'editor escape save and reopen roundtrip stable');
$dirty = '<font onmouseover="alert(1)" style="background:url(javascript:alert(2))">before<script>badScript()</script><iframe>badFrame</iframe><svg><text>badSvg</text></svg><a href="javascript:alert(3)">link</a><img src="javascript:alert(4)" onerror="alert(5)">after</font>';
$safe = \Security\HtmlSanitizer::clean($dirty);
qa_expect(strpos($safe, 'before') !== false && strpos($safe, 'after') !== false, 'safe neighboring font text retained');
qa_expect(!preg_match('/badScript|badFrame|badSvg|javascript:|onmouseover|onerror|style=|<script|<iframe|<svg/i', $safe), 'nested active content and dangerous attributes blocked');
qa_done();
