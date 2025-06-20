<?php
/**
 * @var Framework\Debug\ExceptionHandler $handler
 */
$lang = $handler->getLanguage();
?>
<!doctype html>
<html lang="<?= $lang->getCurrentLocale() ?>" dir="<?= $lang->getCurrentLocaleDirection() ?>">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $lang->render('debug', 'exceptionTitle') ?></title>
    <link rel="shortcut icon" href="data:image/png;base64,<?= base64_encode((string) file_get_contents(__DIR__ . '/favicons/production.png')) ?>">
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

        .log-id {
            background: #222;
            border-radius: 4px;
            cursor: copy;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
<h1><?= $lang->render('debug', 'exceptionTitle') ?></h1>
<p><?= $lang->render('debug', 'exceptionDescription') ?></p>

<?php
$log = $handler->getLog();
if ($log):
    ?>
    <p><?= $lang->render('debug', 'logId') ?>: <span class="log-id"
            title="<?= $lang->render('debug', 'clickToCopyLogId') ?>"
        ><?= htmlentities($log->id) ?></span>
    </p>
    <script>
        document.querySelector('.log-id').onclick = function () {
            navigator.clipboard.writeText(this.innerText);
            alert("<?= $lang->render('debug', 'logIdCopied') ?>");
        }
    </script>
<?php
endif;
?>

</body>
</html>
