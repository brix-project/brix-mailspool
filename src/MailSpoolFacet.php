<?php

namespace Brix\MailSpool;

use Brix\Core\Type\BrixEnv;
use Brix\Mailer\Type\T_MailerConfig;
use Brix\MailSpool\Type\T_MailSpoolConfig;
use http\Exception\UnexpectedValueException;
use Lack\Keystore\KeyStore;
use Lack\MailSpool\Driver\PhpmailerDriver;
use Lack\MailSpool\MailSpooler;
use Lack\MailSpool\OutgoingMail;

class MailSpoolFacet
{

    public MailSpooler $mailSpooler;
    private function __construct(public BrixEnv $brixEnv, public T_MailSpoolConfig $config)
    {
        $sentDir = null;
        if ($this->config->sent_dir !== null) {
            $sentDir = $this->brixEnv->rootDir->withRelativePath(
                $this->config->sent_dir,
            )->assertDirectory(true);
        }

        $this->mailSpooler = new MailSpooler(
            $this->brixEnv->rootDir->withRelativePath(
                $this->config->spool_dir,
            )->assertDirectory(true),
            $sentDir
        );
    }


    /**
     * Return the mail spool id
     * 
     * @param OutgoingMail $outgoingMail
     * @return string
     */
    public function spoolMail(OutgoingMail $outgoingMail) : string {
        $this->mailSpooler->spoolMail($outgoingMail);
        return $outgoingMail->getMailSpoolId();
    }


    /**
     * Returns the mailSpoolId
     * 
     * @param string $template
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function spoolMailFromTemplate(string $template, array $data) : string {
        $mail = OutgoingMail::FromTemplate($template, $data);
        $this->spoolMail($mail);
        return $mail->getMailSpoolId();
    }



    public function sendMail(string $mailId = null, bool $delete = true) {
        $sC = $this->config->smtp;
        $this->mailSpooler->setDriver(new PhpmailerDriver($sC->host, $sC->port, $sC->username, KeyStore::Get()->getAccessKey($sC->host), $sC->sender, $sC->sender_name));
        $mails = $this->mailSpooler->list();
        foreach ($mails as $mail) {
            if ($mailId !== null && $mail->getMailSpoolId() !== $mailId)
                continue;
            echo "\nSending mail: " . $mail->getMailSpoolId() . " (Delete: $delete)... ";
            $this->mailSpooler->send($mail, null, $delete);
            echo "[OK]\n";
        }
    }
    


    private static $instance;
    public static function getInstance() : self {
        if (self::$instance === null) {
            throw new UnexpectedValueException("MailSpoolFacet not initialized");
        }
        return self::$instance;
    }


    public static function Initialize(BrixEnv $brixEnv) {
        $config = $brixEnv->brixConfig->get(
            "mailspool",
            T_MailSpoolConfig::class,
            file_get_contents(__DIR__ . "/config_tpl.yml")
        );
        self::$instance = new self($brixEnv, $config);
    }
}
