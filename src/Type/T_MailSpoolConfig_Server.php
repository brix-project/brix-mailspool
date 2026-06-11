<?php

namespace Brix\MailSpool\Type;

class T_MailSpoolConfig_Server
{
    public string $host;
    public int $port;
    public string $username;
    public string $sender;
    public string $sender_name;

    /**
     * By default it checks the hostname in keystore. But here you can set either a name to lookup
     * in keystore or provide a path to a file (prefixed with file:///path/to/file) that contains the password.
     *
     * @var string|null
     */
    public ?string $secret_name = null;

}
