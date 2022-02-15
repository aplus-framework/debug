<?php
/**
 * @var Framework\Debug\ExceptionHandler $handler
 */
?>
<!doctype html>
<html lang="<?= $handler->getLanguage()->getCurrentLocale() ?>" dir="<?= $handler->getLanguage()
    ->getCurrentLocaleDirection() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $handler->getLanguage()->render('debug', 'exceptionTitle') ?></title>
    <style>
        body {
            background: #000;
            color: #eee;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            line-height: 24px;
            margin: 16px;
        }

        h1 {
            color: #fff;
        }
    </style>
</head>
<body>
<h1><?= $handler->getLanguage()->render('debug', 'exceptionTitle') ?></h1>
<p><?= $handler->getLanguage()->render('debug', 'exceptionDescription') ?></p>
<?php
$log = $handler->getLogger()?->getLastLog();
if ($log) {
    echo '<p>Log Id: ' . htmlentities($log->id) . '</p>';
}
?>
</body>
</html>
