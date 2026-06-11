<?php

namespace Brix\MailSpool;

use Brix\Core\Type\BrixEnv;
use Brix\MailSpool\Type\T_MailSpoolConfig;
use Brix\MailSpool\Type\T_MailSpoolConfig_Server;
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



    public function createSmtpDriver(): PhpmailerDriver
    {
        $sC = $this->config->smtp;
        return new PhpmailerDriver(
            $sC->host,
            $sC->port,
            $sC->username,
            $this->resolveSmtpSecret($sC),
            $sC->sender,
            $sC->sender_name
        );
    }

    private function resolveSmtpSecret(T_MailSpoolConfig_Server $smtpConfig): string
    {
        if ($smtpConfig->secret_name === null || trim($smtpConfig->secret_name) === "") {
            return KeyStore::Get()->getAccessKey($smtpConfig->host);
        }

        if (str_starts_with($smtpConfig->secret_name, "file://")) {
            $filename = substr($smtpConfig->secret_name, 7);
            if ($filename === "" || ! is_file($filename) || ! is_readable($filename)) {
                throw new \RuntimeException("Unable to read smtp secret file: {$smtpConfig->secret_name}");
            }
            $secret = file_get_contents($filename);
            if ($secret === false) {
                throw new \RuntimeException("Unable to read smtp secret file: {$smtpConfig->secret_name}");
            }
            return rtrim($secret, "\r\n");
        }

        return KeyStore::Get()->getAccessKey($smtpConfig->secret_name);
    }

    public function sendMail(string $mailId = null, bool $delete = true) {
        $this->mailSpooler->setDriver($this->createSmtpDriver());
        $mails = $this->mailSpooler->list();
        foreach ($mails as $mail) {
            if ($mailId !== null && $mail->getMailSpoolId() !== $mailId)
                continue;
            echo "\nSending mail: " . $mail->getMailSpoolId() . " (Delete: $delete)... ";
            $this->mailSpooler->send($mail, null, $delete);
            echo "[OK]\n";
        }
    }
    
    public function listSpooledMails()
    {
        $mails = $this->mailSpooler->list();
        $ret = [];
        foreach ($mails as $mail) {
            $ret[] = [
                "id" => $mail->getMailSpoolId(),
                "subject" => $mail->headers["subject"],
                "to" => $mail->headers["to"],
                "from" => $mail->headers["from"]
            ];
        }
        return $ret;
        
    }
    
    public function hasUnsentMails() : bool
    {
        return count($this->mailSpooler->list()) > 0;
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
