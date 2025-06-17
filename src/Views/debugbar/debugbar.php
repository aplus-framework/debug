<?php

use Framework\Debug\Debugger;

/**
 * @var array<string,Framework\Debug\Collection> $collections
 * @var array<string,mixed> $activities
 * @var array<string,mixed> $options
 */
$infoIcon = file_get_contents(__DIR__ . '/icons/info.svg');
$hasInfoLink = isset($options['info_link']);
if ($hasInfoLink) {
    if (!isset($options['info_link']['href']) || !isset($options['info_link']['text'])) {
        throw new LogicException('Info link must contain "href" and "text" keys');
    }
}
$iconPath = __DIR__ . '/icon.png';
if (isset($options['icon_path'])) {
    $iconPath = $options['icon_path'];
}
if (!is_file($iconPath)) {
    throw new LogicException('Icon not found: ' . $iconPath);
}
?>
<!-- Aplus Framework Debugbar start -->
<style>
    <?= file_get_contents(__DIR__ . '/../assets/prism-aplus.css') ?>
</style>
<style>
    <?php
    $contents = file_get_contents(__DIR__ . '/styles.css');
if (isset($options['color'])) {
    $contents = strtr($contents, ['magenta' => $options['color']]); // @phpstan-ignore-line
}
echo $contents;
?>
</style>
<div id="debugbar" class="aplus-debug" dir="ltr">
    <div class="panels">
        <div class="panel info-collection">
            <div class="resize" title="Change panel height"></div>
            <header>
                <div class="title"><?= $infoIcon ?> Info</div>
            </header>
            <div class="contents">
                <div class="collector-default">
                    <p>Running<?=
                        class_exists('Aplus')
                            ? ' <a href="https://aplus-framework.com" target="_blank" class="aplus-link">Aplus</a> ' . Debugger::roundVersion(Aplus::VERSION)
                            : '' ?> on <?= \PHP_OS_FAMILY ?> with PHP
                        <?= Debugger::roundVersion(\PHP_VERSION) ?>.
                    </p>
                    <p>★
                        <?php if ($hasInfoLink): ?>
                            <a href="<?= htmlentities($options['info_link']['href']) ?>"
                               target="_blank"><?= htmlentities($options['info_link']['text']) ?>
                            </a>
                        <?php else: ?>
                            <a href="https://aplus-framework.com" target="_blank">aplus-framework.com</a>
                        <?php endif ?>
                    </p>
                    <?php
                $count = isset($activities['collected']) ? count($activities['collected']) : 0;
if ($count):
    ?>
                        <p><?= $count ?> activit<?= $count === 1
            ? 'y was'
            : 'ies were' ?> collected in <?= Debugger::roundSecondsToMilliseconds($activities['total']) ?> milliseconds:
                        </p>
                        <table>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Collection</th>
                                <th>Collector</th>
                                <th>Description</th>
                                <th title="Milliseconds">Runtime</th>
                                <th title="Runtime percentages">
                                    <span>10%</span><span>20%</span><span>30%</span><span>40%</span>
                                    <span>50%</span><span>60%</span><span>70%</span><span>80%</span>
                                    <span>90%</span><span>100%</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($activities['collected'] as $index => $collected): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlentities($collected['collection']) ?></td>
                                    <td title="<?= htmlentities($collected['class']) ?>"><?= htmlentities($collected['collector']) ?></td>
                                    <td><?= htmlentities($collected['description']) ?></td>
                                    <td><?= Debugger::roundSecondsToMilliseconds($collected['total']) ?></td>
                                    <td>
                                    <span style="width: <?= $collected['width'] ?>%; margin-left: <?=
                $collected['left']
                                ?>%" title="<?= $collected['width'] ?>% · From <?=
                                $collected['left'] ?>% to <?=
                                $collected['left'] + $collected['width'] ?>% · From <?=
                                round($collected['start'], 6) ?> to <?= round($collected['end'], 6) ?> second"></span>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    <?php
endif
?>
                </div>
            </div>
        </div>
        <?php foreach ($collections as $collection): ?>
            <?php if ($collection->hasCollectors()): ?>
                <div class="panel <?= $collection->getSafeName() ?>-collection">
                    <div class="resize" title="Change panel height"></div>
                    <header>
                        <div class="title">
                            <span class="title-icon"><?= $collection->getIcon() ?></span>
                            <span class="title-name"><?= $collection->getName() ?></span>
                        </div>
                        <div class="actions"><?= implode(' ', $collection->getActions()) ?></div>
                        <div class="collectors">
                            <?php
        $collectors = $collection->getCollectors();
                ?>
                            <select title="<?= $collection->getSafeName() ?> collectors"<?=
                count($collectors) === 1 ? ' disabled' : '' ?>>
                                <?php foreach ($collectors as $collector): ?>
                                    <option value="<?= $collector->getSafeName() ?>"><?= $collector->getName() ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </header>
                    <div class="contents">
                        <?php foreach ($collection->getCollectors() as $collector): ?>
                            <div class="collector-<?= $collector->getSafeName() ?>"><?= $collector->getContents() ?></div>
                        <?php endforeach ?>
                    </div>
                </div>
            <?php endif ?>
        <?php endforeach ?>
    </div>
    <div class="toolbar">
        <div class="icon">
            <img src="data:image/png;base64,<?= base64_encode((string) file_get_contents($iconPath)) ?>" alt="A+" width="32" height="32">
        </div>
        <div class="collections">
            <?php foreach ($collections as $collection): ?>
                <?php if ($collection->hasCollectors()): ?>
                    <button class="collection" id="<?= $collection->getSafeName() ?>-collection"
                        title="<?= $collection->getName() ?> Collection">
                        <span class="collection-icon"><?= $collection->getIcon() ?></span>
                        <span class="collection-name"><?= $collection->getName() ?></span>
                    </button>
                <?php endif ?>
            <?php endforeach ?>
            <div class="info">
                <button class="collection" id="info-collection" title="Info Collection">
                    <span class="collection-icon"><?= $infoIcon ?></span>
                    <span class="collection-name">Info</span>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    window.Prism = {};
    Prism.manual = true;
    <?= file_get_contents(__DIR__ . '/../assets/prism.js') ?>
    Prism.highlightAllUnder(document.querySelector('#debugbar .panels'));
</script>
<script>
    <?= file_get_contents(__DIR__ . '/scripts.js') ?>
    Debugbar.init();
</script>
<!-- Aplus Framework Debugbar end -->
