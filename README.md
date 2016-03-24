=====
tld
=====

Extracts the top level domain (TLD) from the URL given.
List of TLD names is taken from Public suffix: <https://publicsuffix.org/list/effective_tld_names.dat>


Usage:

```php

$extract = \Amlun\TLD\Extract::instance();
$tld = $extract->domain('www.amlun.com'); // this will return *amlun.com*

```