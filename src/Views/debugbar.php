<?php
/**
 * @var array<int,\Framework\Debug\Collection> $collections
 */
?>
<!-- Aplus Framework Debugbar start -->
<style>
    <?= file_get_contents(__DIR__ . '/debugbar/styles.css') ?>
</style>
<div id="debugbar">
    <div class="panels">
        <div class="panel info-collector">
            <div class="resize"></div>
            <header>
                <div class="title">Info</div>
            </header>
            <div class="contents">
                <div class="instance-default">
                    <p>Running<?= class_exists('Aplus') ? ' ' . Aplus::DESCRIPTION
                            : '' ?> on <?= \PHP_OS_FAMILY ?> with PHP <?= \PHP_VERSION ?></p>
                    <h3>Links</h3>
                    <ul>
                        <li><a href="https://docs.aplus-framework.com" target="_blank">Docs</a></li>
                        <li>
                            <a href="https://packages.aplus-framework.com" target="_blank">Packages</a>
                        </li>
                        <li><a href="https://status.aplus-framework.com" target="_blank">Status</a>
                        </li>
                        <li>
                            <a href="https://aplus-framework.com" target="_blank">aplus-framework.com</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <?php foreach ($collections as $collection): ?>
            <?php if ($collection->hasCollectors()): ?>
                <div class="panel <?= $collection->getSafeName() ?>-collector">
                    <div class="resize"></div>
                    <header>
                        <div class="title"><?= $collection->getName() ?></div>
                        <div class="actions"><?= implode(' ', $collection->getActions()) ?></div>
                        <div class="instances">
                            <select title="<?= $collection->getSafeName() ?> instances">
                                <?php foreach ($collection->getCollectors() as $collector): ?>
                                    <option value="<?= $collector->getSafeName() ?>"><?= $collector->getName() ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </header>
                    <div class="contents">
                        <?php foreach ($collection->getCollectors() as $collector): ?>
                            <div class="instance-<?= $collector->getSafeName() ?>"><?= $collector->getContents() ?></div>
                        <?php endforeach ?>
                    </div>
                </div>
            <?php endif ?>
        <?php endforeach ?>
    </div>
    <div class="toolbar">
        <div class="icon">
            <img src="data:image/png;base64,<?= base64_encode((string) file_get_contents(__DIR__ . '/debugbar/icon.png')) ?>" alt="A+" width="32">
        </div>
        <div class="collectors">
            <?php foreach ($collections as $collection): ?>
                <?php if ($collection->hasCollectors()): ?>
                    <button class="collector" id="<?= $collection->getSafeName() ?>-collector"><?= $collection->getName() ?></button>
                <?php endif ?>
            <?php endforeach ?>
            <div class="info">
                <button class="collector" id="info-collector">Info</button>
            </div>
        </div>
    </div>
</div>
<script>
    <?= file_get_contents(__DIR__ . '/debugbar/scripts.js') ?>
    Debugbar.init();
</script>
<!-- Aplus Framework Debugbar end -->
