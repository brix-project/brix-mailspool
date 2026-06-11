<?php

namespace Brix\MailSpool;

use Brix\Core\AbstractBrixCommand;
use Lack\MailSpool\OutgoingMail;
use Lack\MailSpool\OutgoingMailAttachment;
use Phore\Cli\Annotation\CliParameter;
use Phore\Cli\Exception\CliException;
use Phore\Cli\Output\Out;

class Mailspool extends AbstractBrixCommand
{

    private MailSpoolFacet $facet;

    public function __construct()
    {
        $this->facet = MailSpoolFacet::getInstance();
    }

    public function send(array $argv = [], bool $all = false, string $delete = "yes") {
        $mails = $this->facet->mailSpooler->list();
        if (count($argv) === 0)
            $all = true;

        if ($all === false) {
            $mailNo = ((int)$argv[0])-1;
            if ( ! isset($mails[$mailNo]))
                 throw new CliException("Mail No '$mailNo' not found - provide mail number from list.");
            $mails = [$mails[$mailNo]];

        }
        $this->facet->mailSpooler->setDriver($this->facet->createSmtpDriver());
        foreach ($mails as $mail) {
            echo "\nSending mail: " . $mail->getMailSpoolId() . " (Delete: $delete)\n";
            $this->facet->mailSpooler->send($mail, null, $delete === "yes");
            echo "[OK]\n";

        }

    }


    public function list() {
        $mails = $this->facet->mailSpooler->list();
        Out::Table($mails);
    }


    public function queue(
        #[CliParameter("to", "Recipient email address")]
        string $to,
        #[CliParameter("subject", "Mail subject")]
        string $subject,
        #[CliParameter("attachment", "Optional file to attach")]
        ?string $attachment = null
    ) : void {


        $body = stream_get_contents(STDIN);
        if ($body === false) {
            throw new CliException("Unable to read mail body from stdin.");
        }

        $mail = new OutgoingMail();
        $mail->setTo($to);
        $mail->setSubject($subject); // From is set by SMTP driver
        $mail->textBody = $body;

        if ($attachment !== null) {
            if ( ! is_file($attachment) || ! is_readable($attachment)) {
                throw new CliException("Attachment not readable: $attachment");
            }
            $data = file_get_contents($attachment);
            if ($data === false) {
                throw new CliException("Unable to read attachment: $attachment");
            }
            $mail->attachments[] = new OutgoingMailAttachment($data, basename($attachment));
        }

        $mailId = $this->facet->spoolMail($mail);
        Out::TextSuccess("Mail queued: **{$mailId}**");
    }

}
