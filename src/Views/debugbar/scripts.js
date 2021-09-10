let Debugbar = {
    icon: null,
    collectors: null,
    panels: null,
    init: function () {
        Debugbar.icon = document.querySelector('.icon');
        Debugbar.collectors = document.querySelectorAll('.collector');
        Debugbar.panels = document.querySelectorAll('.panel');
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
        let panelsDiv = document.querySelector('.panels');
        let collectorsDiv = document.querySelector('.collectors');
        toolbar.style.width = '100%';
        panelsDiv.style.display = 'block';
        collectorsDiv.style.display = 'flex';
        Debugbar.activePanel();
    },
    hideWide: function () {
        let toolbar = document.querySelector('#debugbar');
        let panelsDiv = document.querySelector('.panels');
        let collectorsDiv = document.querySelector('.collectors');
        toolbar.style.width = 'auto';
        panelsDiv.style.display = 'none';
        collectorsDiv.style.display = 'none';
    },
    activePanel: function () {
        let id = localStorage.getItem('debugbar-panel');
        if (id) {
            let collector = document.querySelector('#' + id);
            let panel = document.querySelector('.' + id);
            collector.classList.add('active');
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
};
