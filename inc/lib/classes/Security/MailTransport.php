<?php
namespace Security;

interface MailTransport
{
    /** @param array{subject:string,text:string,html:string} $message */
    public function send(string $to, array $message): void;
}
