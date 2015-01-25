EscoMail
=======
[![Build Status](https://travis-ci.org/guliano/esco-mail.svg)](https://travis-ci.org/guliano/esco-mail)
[![Coverage Status](https://coveralls.io/repos/guliano/esco-mail/badge.svg?branch=master)](https://coveralls.io/r/guliano/esco-mail?branch=master)

Introduction
------------

This module wraps ZF2 mail functionality. It supports file attachments, template email composition and extra `test mode` for sending e-mails to defined root e-mail address instead of real recipient.

Requirements
------------

Please see the [composer.json](composer.json) file.

Installation
------------

Run the following `composer` command:

```console
$ composer require guliano/esco-mail
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return array(
    /* ... */
    'modules' => array(
        /* ... */
        'EscoMail',
    ),
    /* ... */
);
```

Usage
=====
TODO

Configuration
=============

### User Configuration

This module utilizes the top level key `esco-mail` for user configuration.

#### Key: `mail_test_mode`

TODO
