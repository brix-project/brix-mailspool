# Mailspool - Send outgoing mails using the mailspool api

Initialization is done if mailspool is required via composer. It uses `lack/mailspool` to spool and for 
mail interfaces and templating. See details there.

To send a outgoing mail call

How to use templates:

```php
$mail = OutgoingMail::FromTemplate(__DIR__ . "./mailtemplate.txt", [
    "email" => $email,
    "password" => $password
]);
MailSpoolFacet::getInstance()->spoolMail($mail);
```

## Using Templates

Mail Templates are regular frontmatter files with a yaml section on top and a markdown section below. Make
Sure to start and end the header with ```---```.
