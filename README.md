# PhpStan extenstion for Twig templates

Inspired by blog series by @TomasVortuba - https://tomasvotruba.com/blog/stamp-1-how-to-compile-twig-to-php/

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev driveto/phpstan-twig
```

Include extension.neon in your project's PHPStan config:

```
includes:
    - vendor/phpstan/phpstan-twig/extension.neon
```

Add path to Twig service:

```
parameters:
    twig:
        twigEnvironmentLoader: tests/TwigEnvironmentLoader.php
```

Example of Twig loader:
```php
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
return $kernel->getContainer()->get('twig');
```
