# Debug Library *documentation*

```php
use Framework\Debug\ExceptionHandler;

(new ExceptionHandler())->initialize();
```

```php
use Framework\Debug\ExceptionHandler;
use Framework\Language\Language;
use Framework\Log\Logger;

$logger = new Logger('/tmp');
$language = new Language('en-us');
$language->setSupportedLocales(['en','es','pt-br']);
$language->setFallbackLevel(Language::FALLBACK_DEFAULT);
$exceptions = new ExceptionHandler(ExceptionHandler::ENV_PROD, $logger, $language);
$exceptions->setViewsDir(__DIR__ . '/Views');
$exceptions->initialize();
```