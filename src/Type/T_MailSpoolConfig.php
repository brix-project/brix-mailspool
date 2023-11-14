<?php

namespace Brix\MailSpool\Type;

class T_MailSpoolConfig
{
    public string $spool_dir = "./_mailspool";
    /**
     * @var string|null
     */
    public string|null $sent_dir = null;

    /**
     * @var T_MailSpoolConfig_Server|null
     */
    public T_MailSpoolConfig_Server|null $smtp;


    // Smtp Password is received from Keystore


}
