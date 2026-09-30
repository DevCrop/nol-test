<?php
namespace Security;

final class MfaEmail
{
    public static function compose(string $platform, string $code, int $expires): array
    {
        if (!preg_match('/^[0-9]{6}$/D', $code)) throw new \InvalidArgumentException('Invalid MFA code');
        $safe = htmlspecialchars($platform, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $deadline = (new \DateTimeImmutable('@'.$expires))->setTimezone(new \DateTimeZone('Asia/Seoul'))->format('Y-m-d H:i:s');
        $text = "[Web발신]\n플랫폼: {$platform}\n관리자 로그인 인증\n\n인증번호: {$code}\n유효기한: {$deadline} (한국시간)\n\n인증 화면에 위 번호를 입력해 주세요. 재발송해도 유효기한은 연장되지 않습니다.\n인증번호를 다른 사람에게 공유하지 마세요. 본인이 요청하지 않았다면 입력하지 말고 관리자에게 문의해 주세요.\n본 메일은 자동 발송되었습니다.";
        $html = '<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            .'<body style="margin:0;padding:0;background:#f3f5f8;color:#172033;font-family:Arial,\'Malgun Gothic\',sans-serif">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding:24px 12px">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#ffffff;border:1px solid #e3e7ef;border-radius:16px">'
            .'<tr><td style="padding:28px 24px 20px;border-bottom:1px solid #e3e7ef">'
            .'<p style="margin:0 0 10px;font-size:12px;color:#667085">[Web발신] · 관리자 보안 인증</p>'
            .'<p style="margin:0 0 12px;font-size:14px;color:#475467">플랫폼: <strong>'.$safe.'</strong></p>'
            .'<h1 style="margin:0;font-size:24px;line-height:1.4">로그인 인증번호 안내</h1></td></tr>'
            .'<tr><td style="padding:24px"><p style="margin:0 0 16px;font-size:15px;line-height:1.7">관리자 로그인을 완료하려면<br>인증 화면에 아래 번호를 입력해 주세요.</p>'
            .'<div style="padding:20px 12px;text-align:center;background:#eef3ff;border:1px solid #d7e2ff;border-radius:12px">'
            .'<p style="margin:0 0 10px;font-size:13px;color:#475467">인증번호</p>'
            .'<p style="margin:0;font-family:Consolas,monospace;font-size:32px;font-weight:700;letter-spacing:6px;line-height:1.3;color:#173d8f">'.$code.'</p></div>'
            .'<p style="margin:18px 0 0;font-size:13px;line-height:1.7"><strong>유효기한</strong><br>'.$deadline.' (한국시간)</p>'
            .'<p style="margin:6px 0 0;font-size:12px;line-height:1.7;color:#667085">재발송해도 유효기한은 연장되지 않습니다.</p>'
            .'<p style="margin:22px 0 0;padding-top:18px;border-top:1px solid #e3e7ef;font-size:13px;line-height:1.8;color:#475467">인증번호를 다른 사람에게 공유하지 마세요.<br>본인이 요청하지 않았다면 입력하지 말고 관리자에게 문의해 주세요.</p>'
            .'</td></tr><tr><td style="padding:16px 24px;background:#f8fafc;font-size:12px;line-height:1.7;color:#667085">'
            .$safe.' · 본 메일은 자동 발송되었습니다.</td></tr></table></td></tr></table></body></html>';
        return ['subject'=>'[Web발신] '.$platform.' 관리자 로그인 인증번호', 'text'=>$text, 'html'=>$html];
    }
}
