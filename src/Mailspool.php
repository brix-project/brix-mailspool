<?php

namespace Brix\MailSpool;

use Brix\Core\AbstractBrixCommand;
use Lack\Keystore\KeyStore;
use Lack\MailSpool\Driver\PhpmailerDriver;
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
        $sC = $this->facet->config->smtp;
        $this->facet->mailSpooler->setDriver(new PhpmailerDriver($sC->host, $sC->port, $sC->username, KeyStore::Get()->getAccessKey($sC->host), $sC->sender, $sC->sender_name));
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


}
