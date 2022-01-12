let Debugbar = {
    icon: null,
    collectors: null,
    panels: null,
    init: function () {
        Debugbar.icon = document.querySelector('#debugbar .icon');
        Debugbar.collectors = document.querySelectorAll('#debugbar .collector');
        Debugbar.panels = document.querySelectorAll('#debugbar .panel');
        if (Debugbar.isWide()) {
            Debugbar.showWide();
        } else {
            Debugbar.hideWide();
        }
        Debugbar.prepareIcon();
        Debugbar.prepareCollectors();
    },
    prepareIcon: function () {
        Debugbar.icon.addEventListener('click', function () {
            if (Debugbar.isWide()) {
                Debugbar.hideWide();
                Debugbar.setWide('n');
            } else {
                Debugbar.showWide();
                Debugbar.setWide('y');
            }
        });
    },
    prepareCollectors: function () {
        Debugbar.collectors.forEach(function (collector) {
            collector.addEventListener('click', function () {
                let wasActive = this.classList.contains('active');
                Debugbar.panels.forEach(function (panel) {
                    panel.style.display = 'none';
                });
                Debugbar.collectors.forEach(function (collector) {
                    collector.classList.remove('active');
                });
                if (wasActive) {
                    Debugbar.removeActivePanel();
                    return;
                }
                Debugbar.setActivePanel(this.id);
                Debugbar.activeInstance(this.id);
                let panel = document.querySelector('.' + this.id);
                panel.style.display = 'block';
                if (!wasActive) {
                    this.classList.add('active');
                }
            });
        });
    },
    setWide: function (active) {
        localStorage.setItem('debugbar-wide', active);
    },
    isWide: function () {
        return localStorage.getItem('debugbar-wide') === 'y';
    },
    showWide: function () {
        let toolbar = document.querySelector('#debugbar');
        let panelsDiv = document.querySelector('#debugbar .panels');
        let collectorsDiv = document.querySelector('#debugbar .collectors');
        toolbar.style.width = '100%';
        panelsDiv.style.display = 'block';
        collectorsDiv.style.display = 'flex';
        Debugbar.activePanel();
    },
    hideWide: function () {
        let toolbar = document.querySelector('#debugbar');
        let panelsDiv = document.querySelector('#debugbar .panels');
        let collectorsDiv = document.querySelector('#debugbar .collectors');
        toolbar.style.width = 'auto';
        panelsDiv.style.display = 'none';
        collectorsDiv.style.display = 'none';
    },
    activePanel: function () {
        let id = localStorage.getItem('debugbar-panel');
        if (id) {
            let collector = document.querySelector('#' + id);
            let panel = document.querySelector('.' + id);
            if ( ! collector || ! panel) {
                return;
            }
            collector.classList.add('active');
            Debugbar.activeInstance(id);
            panel.style.display = 'block';
        }
    },
    setActivePanel: function (id) {
        localStorage.setItem('debugbar-panel', id);
    },
    isActivePanel: function (id) {
        return localStorage.getItem('debugbar-panel') === id;
    },
    removeActivePanel: function () {
        localStorage.removeItem('debugbar-panel')
    },
    activeInstance: function (collector) {
        let contents = document.querySelector('.' + collector + ' .contents');
        for (let i = 0; i < contents.children.length; i++) {
            contents.children[i].style.display = 'none';
        }
        let instance = 'default';
        let select = document.querySelector('.' + collector + ' .instances select');
        if (select) {
            select.onchange = function () {
                Debugbar.activeInstance(collector);
            };
            instance = select.value;
        }
        document.querySelector('.' + collector + ' .instance-' + instance).style.display = 'block';
    },
};
