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

## CLI queue command

You can queue a mail directly from the CLI by piping the body via stdin:

```bash
echo "Hello World" | vendor/bin/brix mailspool queue --to user@example.org --subject "Test"
```

With attachment:

```bash
cat body.txt | vendor/bin/brix mailspool queue --to user@example.org --subject "Test" --attachment ./document.pdf
```
