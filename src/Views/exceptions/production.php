<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?= $this->language->render('debug', 'exceptionTitle'); ?></title>
	<style>
		body {
			font-family: monospace, sans-serif;
			background: black;
			color: white;
		}
	</style>
</head>
<body>
<h1><?= $this->language->render('debug', 'exceptionTitle'); ?></h1>
<p><?= $this->language->render('debug', 'exceptionDescription'); ?></p>
</body>
</html>
