# Lock for Composer

[![Continuous Integration](https://github.com/ghostwriter/composer-lock/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ghostwriter/composer-lock/actions/workflows/continuous-integration.yml)

Install
---

``` bash  
composer require ghostwriter/composer-lock
```

Commands
---

``` bash  
composer lock
composer lock --php=7.4
composer lock -p=8.1 --dry-run
```

Todo
---

- Support multiple lock files

``` text
// find the correct lock file for this platform via config.

- if ([] === $input->getOption('php')) {
- // >> called: composer lock (without php version)

- // check: composer config platform.php
- // return: ['7.4']
- // result: ['composer_7.4.lock']

- // check: platform-php
- // return: ['7.4.24']
- // result: ['composer_7.4.24.lock']

- // check: composer config composer-lock
- // return: ['7.4','8.0','8.1']
- // result: ['composer_7.4.lock', 'composer_8.0.lock', 'composer_8.1.lock']

- // else check: composer config composer-lock
- // return: []
- // return: ['composer.lock']
```

- Load custom lock files on `composer install`
- Configurable

```json
{
    "config": {
        "optimize-autoloader": true,
        "composer-lock": {
            "enable": true,
            "directory": "./",
            "format": "composer_%s.lock",
            "php": [
                "7.4",
                "7.4.24",
                "8.0",
                "8.0.11",
                "8.1"
            ]
        },
        "platform": {
            "php": "7.4"
        },
        "sort-packages": true
    }
}
```
