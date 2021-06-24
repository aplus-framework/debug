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
	<title><?= $handler->getLanguage()->render('debug', 'exceptionTitle') ?></title>
	<style>
		body {
			font-family: monospace, sans-serif;
			background: black;
			color: white;
		}
	</style>
</head>
<body>
<h1><?= $handler->getLanguage()->render('debug', 'exceptionTitle') ?></h1>
<p><?= $handler->getLanguage()->render('debug', 'exceptionDescription') ?></p>
<?php if ($handler->getLogger() && $handler->getLogger()->getLastLog()?->written): ?>
	<p>Log Id: <?= $handler->getLogger()->getLastLog()->id ?></p>
<?php endif ?>
</body>
</html>
