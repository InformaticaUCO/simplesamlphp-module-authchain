# SimpleSAMLphp Module UCOFilter

This module try to identify an user with multiple AuthSources in chain. 

## Requirements

* PHP>=5.5

## Installation

Installation can be as easy as executing:

```bash
bash$ composer require informaticauco/simplesamlphp-module-authchain
```

## Usage

Edit `config/authsources.php` and add the next _authsource_:

```php
<?php

use SimpleSAML\Modules\AuthChain\Auth\Source\AuthChain;

$config['as1'] = [/*...*/];
$config['as2'] = [/*...*/];

$config['chained'] = [AuthChain::class,
    'sources' => ['as1', 'as2'],
];
```
    
_AuthSources_ defined in sources section must support `array function login(string $username, string $password)` method or will be ignored. The first AuthSource to identify the user will be used.
