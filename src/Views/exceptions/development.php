<?php
/**
 * @var Exception $exception
 * @var Framework\Debug\ExceptionHandler $handler
 */

use Framework\Helpers\ArraySimple;

?>
<!doctype html>
<html lang="<?= $handler->getLanguage()->getCurrentLocale() ?>" dir="<?= $handler->getLanguage()
    ->getCurrentLocaleDirection() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $handler->getLanguage()->render('debug', 'exception') ?>: <?=
        htmlentities($exception->getMessage()) ?></title>
    <style>
        <?= file_get_contents(__DIR__ . '/../assets/prism-aplus.css') ?>
    </style>
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            margin: 0 0 20px;
        }

        header {
            background: #f00;
            border: solid #222;
            border-width: 1px 0;
            color: #fff;
            padding: 20px 20px;
        }

        h1, h2, h3 {
            margin: 0 0 5px;
        }

        section {
            border-bottom: 1px solid #222;
            padding: 10px 20px;
        }

        section:last-of-type {
            border-bottom: 0;
        }

        section .header {
            background: #f00;
            border: 1px solid red;
            color: #fff;
            font-weight: bold;
            padding: 10px;
        }

        .top {
            border-top: 0;
        }

        .file {
            background: #111;
        }

        .file div {
            display: inline-block;
        }

        .file div.line {
            margin-left: 10px;
        }

        dl {
            background-color: #000;
            border: 1px solid #222;
            font-family: monospace;
            font-size: 14px;
            margin: 10px 0 0;
        }

        dt {
            background: #111;
            border-bottom: 1px solid #222;
            color: #fff;
            padding: 1%;
            width: 98%;
        }

        dd {
            background: #000;
            margin: 0;
            overflow-x: auto;
        }

        pre.code {
            display: inline-block;
            float: left;
            line-height: 20px;
            margin: 0;
            padding: 5px;
            width: 80%;
        }

        dd div {
            background: #111;
            border-right: 1px solid #222;
            display: inline-block;
            float: left;
            line-height: 20px;
            min-width: 25px;
            padding: 5px;
            text-align: right;
            white-space: pre;
        }

        dd div span {
            color: #f00;
            font-weight: bold;
        }

        dt span {
            background: #f00;
            padding: 2px 6px;
        }

        table {
            border: 1px solid #222;
            border-bottom: 0;
            border-spacing: 0;
            margin-top: 10px;
            width: 100%;
        }

        th {
            border-right: 2px solid #222;
        }

        th, td {
            border-bottom: 1px solid #222;
            padding: 5px;
        }

        td pre code {
            margin: 0;
            white-space: pre-wrap !important;
        }

        tr:hover {
            background: #111;
        }

        thead th {
            background: #111;
            border-bottom: 2px solid #222;
            border-right: 0;
            color: #fff;
            font-size: 110%;
            text-align: left;
        }

        tbody th {
            background: #111;
            min-width: 40%;
            text-align: right;
        }
    </style>
</head>
<body class="aplus-debug">
<header class="top">
    <small><?= $handler->getLanguage()->render('debug', 'exception') ?>:</small>
    <h1><?= $exception::class ?></h1>
    <small><?= $handler->getLanguage()->render('debug', 'message') ?>:</small>
    <h2><?= htmlentities($exception->getMessage()) ?></h2>
</header>
<section class="file">
    <div>
        <small><?= $handler->getLanguage()->render('debug', 'file') ?>:</small>
        <h3><?= htmlentities($exception->getFile()) ?></h3>
    </div>
    <div class="line">
        <small><?= $handler->getLanguage()->render('debug', 'line') ?>:</small>
        <h3><?= $exception->getLine() ?></h3>
    </div>
</section>
<section class="trace">
    <div class="header"><?= $handler->getLanguage()->render('debug', 'trace') ?>:</div>
    <?php
    $traces = $exception->getTrace();
if ($traces
    && isset($traces[0]['file'])
    && ($traces[0]['file'] !== $exception->getFile()
        || (isset($traces[0]['line']) && $traces[0]['line'] !== $exception->getLine()))
) {
    $traces = array_reverse($traces);
    $traces[] = [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ];
    $traces = array_reverse($traces);
}
?>
    <?php foreach ($traces as $key => $trace) : ?>
        <?php if (isset($trace['file'])) : ?>
            <?php if (is_readable($trace['file'])) : ?>
                <dl>
                    <dt>
                        <span><?= count($traces) - $key ?></span>
                        <?= $trace['file'] ?><?=
                    isset($trace['line']) ? ':' . $trace['line'] : ''
                ?>
                    </dt>
                    <dd>
                        <?php
                $lines = [];
                $pre = '';
                $handle = fopen($trace['file'], 'rb');
                $line = 1;
                while ($handle && ! feof($handle)) {
                    $code = fgets($handle);
                    if (isset($trace['line'])
                        && $line >= ($trace['line'] - 10)
                        && $line <= ($trace['line'] + 10)
                    ) {
                        $pre .= rtrim((string) $code) . \PHP_EOL;
                        $lines[] = $line;
                    }
                    $line++;
                }
                if ($handle) {
                    fclose($handle);
                }
                ?>
                        <div><?php
                    foreach ($lines as $line) {
                        if (isset($trace['line']) && $line === $trace['line']) {
                            echo '<span>';
                            echo $line . \PHP_EOL;
                            echo '</span>';
                        } else {
                            echo $line . \PHP_EOL;
                        }
                    }
            ?></div>
                        <pre class="code"><code class="language-php"><?= htmlentities($pre) ?></code></pre>
                    </dd>
                </dl>
            <?php else : ?>
                <dl>
                    <dt>
                        <span><?= $key ?></span> File
                        <em><?= $trace['file'] ?></em> is not readable.
                    </dt>
                </dl>
            <?php endif ?>
        <?php endif ?>
    <?php endforeach ?>
</section>
<section class="input">
    <div class="header">Input:</div>
    <?php
    $input = [
        'ENV' => filter_input_array(\INPUT_ENV) ?: [],
        'SERVER' => filter_input_array(\INPUT_SERVER) ?: [],
        'GET' => ArraySimple::convert(filter_input_array(\INPUT_GET) ?: []),
        'POST' => ArraySimple::convert(filter_input_array(\INPUT_POST) ?: []),
        'COOKIE' => filter_input_array(\INPUT_COOKIE) ?: [],
    ];
foreach ($input as &$item) {
    ksort($item);
}
unset($item);
?>

    <?php foreach ($input as $key => $values) : ?>
        <?php
    if (empty($values)) {
        continue;
    }
        ?>
        <table>
            <thead>
            <tr>
                <th colspan="2"><?= $key ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($values as $field => $value) : ?>
                <tr>
                    <th><?= htmlentities($field) ?></th>
                    <td><?= htmlentities(is_array($value) ? print_r($value, true) : $value) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    <?php endforeach ?>
</section>
<?php
$log = $handler->getLogger()?->getLastLog();
if ($log): ?>
    <section class="log">
        <div class="header">Log:</div>
        <table>
            <tr>
                <th>Date</th>
                <td><?= date('Y-m-d', $log->time) ?></td>
            </tr>
            <tr>
                <th>Time</th>
                <td><?= date('H:i:s', $log->time) ?></td>
            </tr>
            <tr>
                <th>Level</th>
                <td><?= htmlentities($log->level->name) ?></td>
            </tr>
            <tr>
                <th>ID</th>
                <td><?= htmlentities($log->id) ?></td>
            </tr>
            <tr>
                <th>Message</th>
                <td>
                    <pre><code class="language-log"><?= htmlentities($log->message) ?></code></pre>
                </td>
            </tr>
        </table>
    </section>
<?php
endif ?>
<script>
    <?= file_get_contents(__DIR__ . '/../assets/prism.js') ?>
</script>
</body>
</html>
