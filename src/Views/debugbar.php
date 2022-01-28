<?php
/**
 * @var array<string,Framework\Debug\Collection> $collections
 * @var array<string,mixed> $infos
 */
?>
<!-- Aplus Framework Debugbar start -->
<style>
    <?= file_get_contents(__DIR__ . '/assets/prism-aplus.css') ?>
</style>
<style>
    <?= file_get_contents(__DIR__ . '/debugbar/styles.css') ?>
</style>
<div id="debugbar" class="aplus-debug">
    <div class="panels">
        <div class="panel info-collection">
            <div class="resize"></div>
            <header>
                <div class="title">Info</div>
            </header>
            <div class="contents">
                <div class="collector-default">
                    <p>Running<?= class_exists('Aplus') ? ' ' . Aplus::DESCRIPTION
                            : '' ?> on <?= \PHP_OS_FAMILY ?> with PHP <?= \PHP_VERSION ?></p>
                    <p>★
                        <a href="https://aplus-framework.com" target="_blank">aplus-framework.com</a>
                    </p>
                    <?php
                    $count = isset($infos['collected']) ? count($infos['collected']) : 0;
                    if ($count):
                        ?>
                        <p><?= $count ?> activit<?= $count === 1
                                ? 'y was'
                                : 'ies were' ?> collected in <?= round($infos['total'], 6) ?> seconds:
                        </p>
                        <table>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Collection</th>
                                <th>Collector</th>
                                <th>Description</th>
                                <th title="Seconds">Runtime</th>
                                <th title="Runtime percentages">
                                    <span>10%</span><span>20%</span><span>30%</span><span>40%</span>
                                    <span>50%</span><span>60%</span><span>70%</span><span>80%</span>
                                    <span>90%</span><span>100%</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($infos['collected'] as $index => $collected): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlentities($collected['collection']) ?></td>
                                    <td title="<?= htmlentities($collected['class']) ?>"><?= htmlentities($collected['collector']) ?></td>
                                    <td><?= htmlentities($collected['description']) ?></td>
                                    <td><?= round($collected['total'], 6) ?></td>
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
                    <div class="resize"></div>
                    <header>
                        <div class="title"><?= $collection->getName() ?></div>
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
            <img src="data:image/png;base64,<?= base64_encode((string) file_get_contents(__DIR__ . '/debugbar/icon.png')) ?>" alt="A+" width="32">
        </div>
        <div class="collections">
            <?php foreach ($collections as $collection): ?>
                <?php if ($collection->hasCollectors()): ?>
                    <button class="collection" id="<?= $collection->getSafeName() ?>-collection"><?= $collection->getName() ?></button>
                <?php endif ?>
            <?php endforeach ?>
            <div class="info">
                <button class="collection" id="info-collection">Info</button>
            </div>
        </div>
    </div>
</div>
<script>
    <?= file_get_contents(__DIR__ . '/assets/prism.js') ?>
</script>
<script>
    <?= file_get_contents(__DIR__ . '/debugbar/scripts.js') ?>
    Debugbar.init();
</script>
<!-- Aplus Framework Debugbar end -->
