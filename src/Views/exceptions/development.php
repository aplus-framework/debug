<?php
/**
 * @var Exception $exception
 * @var Framework\Debug\ExceptionHandler $handler
 */

use Framework\Helpers\ArraySimple;

$lang = static function (string $line, array $args = []) use ($handler) : string {
    return $handler->getLanguage()->render('debug', $line, $args);
}

?>
<!doctype html>
<html lang="<?= $handler->getLanguage()->getCurrentLocale() ?>" dir="<?= $handler->getLanguage()
    ->getCurrentLocaleDirection() ?>">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $lang('exception') ?>: <?=
        htmlentities($exception->getMessage()) ?></title>
    <link rel="shortcut icon" href="data:image/png;base64,<?= base64_encode((string) file_get_contents(__DIR__ . '/favicons/development.png')) ?>">
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

        h2 a {
            color: inherit;
            text-decoration: none;
        }

        h2 a:hover {
            color: inherit;
            text-decoration: underline;
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

        .input .info {
            padding: 10px;
            background: #111;
            border: 1px solid #222;
            margin-top: 10px
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

        .rtl th {
            border-left: 2px solid #222;
            border-right: 0;
        }

        th, td {
            border-bottom: 1px solid #222;
            padding: 5px;
        }

        td pre code {
            margin: 0;
            white-space: pre-wrap !important;
        }

        thead th {
            background: #111;
            border-bottom: 2px solid #222;
            border-right: 0;
            color: #fff;
            font-size: 110%;
            text-align: left;
        }

        .rtl thead th {
            border-left: 0;
            text-align: right;
        }

        tbody th {
            background: #111;
            min-width: 40%;
            text-align: right;
        }

        .rtl tbody th {
            text-align: left;
        }

        tbody tr:hover th,
        tr:hover {
            background: #333;
            cursor: text;
        }

        .search-button {
            font-size: 14px;
            border: 1px solid #fff;
            border-radius: 4px;
            padding: 5px 10px;
            margin-top: 20px;
            display: block;
            width: fit-content;
            text-align: center;
        }

        .search-button:hover {
            text-decoration: none;
            background: #762121;
        }

        .search-button-icon {
            vertical-align: middle
        }

        .search-button-icon svg {
            height: 14px;
        }

        .powered-by {
            text-align: center;
            margin: 40px auto;
            font-size: 14px;
        }

        .powered-by a {
            color: inherit;
            text-decoration: none;
        }

        .powered-by a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="aplus-debug <?= $handler->getLanguage()->getCurrentLocaleDirection() ?>">
<header class="top">
    <small><?= $lang('exception') ?>:</small>
    <h1><?= $exception::class ?></h1>
    <small><?= $lang('message') ?>:</small>
    <h2>
        <?= htmlentities($exception->getMessage()) ?>
        <a href="<?= $handler->getSearchEngines()->makeLink(
            $exception::class . ': ' . $exception->getMessage()
        ) ?>" rel="noreferrer" target="_blank" class="search-button">
            <span class="search-button-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                </svg>
            </span> <?= $lang('search') ?>
        </a>
    </h2>
</header>
<section class="file">
    <div>
        <small><?= $lang('file') ?>:</small>
        <h3><?= htmlentities($exception->getFile()) ?></h3>
    </div>
    <div class="line">
        <small><?= $lang('line') ?>:</small>
        <h3><?= $exception->getLine() ?></h3>
    </div>
</section>
<section class="trace">
    <div class="header"><?= $lang('trace') ?>:</div>
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
                <dl dir="ltr">
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
                while ($handle && !feof($handle)) {
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
    <div class="header"><?= $lang('input') ?>:</div>
    <?php
    $input = [
        '$_ENV' => ArraySimple::convert($_ENV),
        '$_SERVER' => ArraySimple::convert($_SERVER),
        '$_GET' => ArraySimple::convert($_GET),
        '$_POST' => ArraySimple::convert($_POST),
        '$_FILES' => ArraySimple::convert(ArraySimple::files()),
        '$_COOKIE' => ArraySimple::convert($_COOKIE),
    ];
    foreach ($input as &$item) {
        ksort($item);
    }
    unset($item);

    $hidden = [];
    foreach ($input as $key => $value) {
        if ($handler->isHiddenInput($key) && !empty($value)) {
            $hidden[] = "<strong>{$key}</strong>";
        }
    }
    if ($hidden) :
        ?>
        <div class="info"><?= $lang('inputVarsHidden', [implode(', ', $hidden)]) ?></div>
    <?php
    endif;

    foreach ($input as $key => $values) : ?>
        <?php
        if (empty($values)) {
            continue;
        }
        if ($handler->isHiddenInput($key)) {
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
                    <td><?= htmlentities((string) $value) ?></td>
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
        <div class="header"><?= $lang('log') ?>:</div>
        <table>
            <tr>
                <th><?= $lang('date') ?></th>
                <td><?= date('Y-m-d', $log->time) ?></td>
            </tr>
            <tr>
                <th><?= $lang('time') ?></th>
                <td><?= date('H:i:s', $log->time) ?></td>
            </tr>
            <tr>
                <th><?= $lang('level') ?></th>
                <td><?= htmlentities($log->level->name) ?></td>
            </tr>
            <tr>
                <th><?= $lang('id') ?></th>
                <td><?= htmlentities($log->id) ?></td>
            </tr>
            <tr>
                <th><?= $lang('message') ?></th>
                <td dir="ltr">
                    <pre><code class="language-log"><?= htmlentities($log->message) ?></code></pre>
                </td>
            </tr>
        </table>
    </section>
<?php
endif ?>
<p class="powered-by">Powered by
    <a href="https://aplus-framework.com/packages/debug" target="_blank">Aplus Framework Debug Library</a>
</p>
<script>
    <?= file_get_contents(__DIR__ . '/../assets/prism.js') ?>
</script>
</body>
</html>
