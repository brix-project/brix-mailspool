<?php

namespace Brix\MailSpool;

use Brix\Core\AbstractBrixCommand;
use Lack\Keystore\KeyStore;
use Lack\MailSpool\Driver\PhpmailerDriver;
use Phore\Cli\Output\Out;

class Mailspool extends AbstractBrixCommand
{

    private MailSpoolFacet $facet;

    public function __construct()
    {
        $this->facet = MailSpoolFacet::getInstance();
    }

    public function send(array $argv = [], bool $all = false) {
        $mails = $this->facet->mailSpooler->list();
        if ($all === false) {
            $mails = [$mails[((int)$argv[0])-1]];
        }
        $sC = $this->facet->config->smtp;
        $this->facet->mailSpooler->setDriver(new PhpmailerDriver($sC->host, $sC->port, $sC->username, KeyStore::Get()->getAccessKey($sC->host), $sC->sender, $sC->sender_name));
        foreach ($mails as $mail) {
            echo "\nSending mail: " . $mail->getMailSpoolId() . "\n";
            $this->facet->mailSpooler->send($mail);
            echo "[OK]\n";
        }

    }


    public function list() {
        $mails = $this->facet->mailSpooler->list();
        Out::Table($mails);
    }


}
