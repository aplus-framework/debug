<?php
/**
 * @var Framework\Debug\ExceptionHandler $handler
 */

/**
 * @param string $line
 *
 * @return string
 */
$lang = static function (string $line) use ($handler) : string {
    return $handler->getLanguage()->render('debug', $line);
};

$log = $handler->getLog();
$isRtl = $handler->getLanguage()->getCurrentLocaleDirection() === 'rtl';
?>
<!doctype html>
<html lang="<?= $handler->getLanguage()->getCurrentLocale() ?>" dir="<?= $handler->getLanguage()
    ->getCurrentLocaleDirection() ?>">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $lang('exceptionTitle') ?></title>
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

        <?php if ($log) : ?>
        .log-id {
            background: #222;
            border-radius: 4px;
            cursor: copy;
            padding: 5px 10px;
        }

        <?php endif ?>
    </style>
</head>
<body>
<h1><?= $lang('exceptionTitle') ?></h1>
<p><?= $lang('exceptionDescription') ?></p>
<?php if ($log) : ?>
    <?php if ($isRtl) : ?>
        <p><span class="log-id"
                title="<?= htmlentities($lang('clickToCopyLogId')) ?>"
            ><?= htmlentities($log->id) ?></span> :<?= $lang('logId') ?>
        </p>
    <?php else : ?>
        <p><?= $lang('logId') ?>: <span class="log-id"
                title="<?= htmlentities($lang('clickToCopyLogId')) ?>"
            ><?= htmlentities($log->id) ?></span>
        </p>
    <?php endif ?>
    <script>
        document.querySelector('.log-id').onclick = function () {
            navigator.clipboard.writeText(this.innerText);
            alert("<?= htmlentities($lang('logIdCopied')) ?>");
        }
    </script>
<?php endif ?>
</body>
</html>
