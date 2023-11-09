<?php


namespace Brix;





use Brix\Core\BrixEnvFactorySingleton;
use Brix\Core\Type\BrixEnv;
use Brix\MailSpool\Mailspool;
use Brix\MailSpool\MailSpoolFacet;
use Phore\Cli\CliDispatcher;

CliDispatcher::addClass(Mailspool::class);

// Initalize the MailSpooler
MailSpoolFacet::Initialize(BrixEnvFactorySingleton::getInstance()->getEnv());

