<?php
namespace Security;

final class MfaDelivery
{
    private $transport;

    public function __construct(MailTransport $transport)
    {
        $this->transport = $transport;
    }

    public function send(string $to, string $code, int $expires): void
    {
        $message = MfaEmail::compose('블루스퀘어', $code, $expires);
        $this->transport->send($to, $message);
    }
}
