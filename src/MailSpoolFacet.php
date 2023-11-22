<?php

namespace Brix\MailSpool;

use Brix\Core\Type\BrixEnv;
use Brix\Mailer\Type\T_MailerConfig;
use Brix\MailSpool\Type\T_MailSpoolConfig;
use http\Exception\UnexpectedValueException;
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




    public function spoolMail(OutgoingMail $outgoingMail) {
        $this->mailSpooler->spoolMail($outgoingMail);
    }



    public function spoolMailFromTemplate(string $template, array $data) {
        $mail = OutgoingMail::FromTemplate($template, $data);
        $this->spoolMail($mail);
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
