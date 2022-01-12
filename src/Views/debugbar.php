<!-- Aplus Framework Debugbar start -->
<style>
    <?= file_get_contents(__DIR__ . '/debugbar/styles.css') ?>
</style>
<div id="debugbar">
    <div class="panels">
        <div class="panel info-collector">
            <header>
                <div class="title">Info</div>
            </header>
            <div class="contents">
                <div class="instance-default">
                    <p>Running<?= class_exists('Aplus') ? ' ' . Aplus::DESCRIPTION
                            : '' ?> on <?= PHP_OS_FAMILY ?> with PHP <?= PHP_VERSION ?></p>
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
        <div class="panel cache-collector">
            <header>
                <div class="title">Cache</div>
                <div class="actions">
                    <button>Foo</button>
                    <button>bazz</button>
                    <a href="aa">aaa</a>
                    <button>bazz</button>
                </div>
                <div class="instances">
                    <select title="Cache instances">
                        <option value="default" selected>default</option>
                        <option value="mars">mars</option>
                        <option value="venus">venus</option>
                    </select>
                </div>
            </header>
            <div class="contents">
                <div class="instance-default">Default</div>
                <div class="instance-mars">Mars</div>
                <div class="instance-venus">Vênus</div>
            </div>
        </div>
        <div class="panel database-collector">
            <header>
                <div class="title">Database</div>
                <div class="actions">def</div>
                <div class="instances">
                    <select title="Database instances">
                        <option value="default">default</option>
                        <option value="jupiter" selected>jupiter</option>
                        <option value="xxx">xxx</option>
                    </select>
                </div>
            </header>
            <div class="contents">
                <div class="instance-default">Default</div>
                <div class="instance-jupiter">
                    <p>Foo</p>
                    <table>
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>Query</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 1; $i < 21; $i++): ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td>
                                <pre class="language-sql"><code>SELECT * FROM `Users`
WHERE `id` = <?= $i ?></code></pre>
                                </td>
                            </tr>
                        <?php endfor ?>
                        </tbody>
                    </table>
                    <p>bar</p>
                </div>
                <div class="instance-xxx">xxxxx</div>
            </div>
        </div>
    </div>
    <div class="toolbar">
        <div class="icon">
            <img src="data:image/png;base64,<?= base64_encode(file_get_contents(__DIR__ . '/debugbar/icon.png')) ?>" alt="A+" width="32">
        </div>
        <div class="collectors">
            <button class="collector" id="cache-collector">Cache</button>
            <button class="collector" id="database-collector">Database</button>
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
