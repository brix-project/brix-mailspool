<?php

namespace Brix\MailSpool;

use Brix\Core\Type\BrixEnv;
use http\Exception\UnexpectedValueException;

class MailSpoolFacet
{

    private function __construct(public BrixEnv $brixEnv)
    {
    }








    private static $instance;
    public static function getInstance() : self {
        if (self::$instance === null) {
            throw new UnexpectedValueException("MailSpoolFacet not initialized");
        }
        return self::$instance;
    }


    public static function Initialize(BrixEnv $env) {
        self::$instance = new self($env);
    }
}
