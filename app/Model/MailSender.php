<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;


class MailSender
{
    public function getMessage()
    {
        return new Message();
    }

    public function getSender()
    {
        return new SmtpMailer([
            'host' => 'smtp.mailtrap.io',
            'username' => '2fa19ae64da5dc',
            'password' => '8e5873422f32e5',
        ]);
    }

    public function sendAddPost(Message $message)
    {
        $mailer = $this->getSender();
        $mailer->send($message);
    }
}