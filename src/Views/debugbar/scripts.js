let Debugbar = {
    icon: null,
    collections: null,
    panels: null,
    init: function () {
        Debugbar.icon = document.querySelector('#debugbar .icon');
        Debugbar.collections = document.querySelectorAll('#debugbar .collection');
        Debugbar.panels = document.querySelectorAll('#debugbar .panel');
        if (Debugbar.isWide()) {
            Debugbar.showWide();
        } else {
            Debugbar.hideWide();
        }
        Debugbar.prepareIcon();
        Debugbar.prepareCollections();
        Debugbar.prepareKeys();
    },
    prepareIcon: function () {
        Debugbar.icon.addEventListener('click', function () {
            Debugbar.toggleWide();
        });
    },
    prepareCollections: function () {
        Debugbar.collections.forEach(function (collector) {
            collector.addEventListener('click', function () {
                let wasActive = this.classList.contains('active');
                Debugbar.panels.forEach(function (panel) {
                    panel.style.display = 'none';
                });
                Debugbar.collections.forEach(function (collector) {
                    collector.classList.remove('active');
                });
                if (wasActive) {
                    Debugbar.removeActivePanel();
                    return;
                }
                Debugbar.setActivePanel(this.id);
                Debugbar.activeCollector(this.id);
                let panel = document.querySelector('.' + this.id);
                panel.style.display = 'block';
                if (!wasActive) {
                    this.classList.add('active');
                }
            });
        });
    },
    prepareKeys: function () {
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.code === 'F12') {
                Debugbar.toggleWide();
            }
        });
    },
    setWide: function (active) {
        localStorage.setItem('debugbar-wide', active);
    },
    isWide: function () {
        return localStorage.getItem('debugbar-wide') === 'y';
    },
    showWide: function () {
        let debugbar = document.querySelector('#debugbar');
        let panelsDiv = document.querySelector('#debugbar .panels');
        let collectionsDiv = document.querySelector('#debugbar .collections');
        let toolbar = document.querySelector('#debugbar .toolbar');
        debugbar.style.width = '100%';
        panelsDiv.style.display = 'block';
        collectionsDiv.style.display = 'flex';
        toolbar.style.borderRightWidth = '0';
        Debugbar.activePanel();
    },
    hideWide: function () {
        let debugbar = document.querySelector('#debugbar');
        let panelsDiv = document.querySelector('#debugbar .panels');
        let collectionsDiv = document.querySelector('#debugbar .collections');
        let toolbar = document.querySelector('#debugbar .toolbar');
        debugbar.style.width = 'auto';
        panelsDiv.style.display = 'none';
        collectionsDiv.style.display = 'none';
        toolbar.style.borderRightWidth = '1px';
    },
    toggleWide: function () {
        if (Debugbar.isWide()) {
            Debugbar.hideWide();
            Debugbar.setWide('n');
            return;
        }
        Debugbar.showWide();
        Debugbar.setWide('y');
    },
    activePanel: function () {
        let id = localStorage.getItem('debugbar-panel');
        if (id) {
            let collection = document.querySelector('#' + id);
            let panel = document.querySelector('.' + id);
            if (!collection || !panel) {
                return;
            }
            collection.classList.add('active');
            Debugbar.activeCollector(id);
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
        localStorage.removeItem('debugbar-panel');
    },
    activeCollector: function (collection) {
        let contents = document.querySelector('.' + collection + ' .contents');
        for (let i = 0; i < contents.children.length; i++) {
            contents.children[i].style.display = 'none';
        }
        Debugbar.makeResizable(contents);
        let collector = 'default';
        let select = document.querySelector('.' + collection + ' .collectors select');
        if (select) {
            select.onchange = function () {
                Debugbar.activeCollector(collection);
            };
            collector = select.value;
        }
        document.querySelector('.' + collection + ' .collector-' + collector).style.display = 'block';
    },
    makeResizable: function (contents) {
        if (!contents.style.height) {
            contents.style.height = '250px';
        }
        const storageKey = 'debugbar-panel-contents-height';
        let storedHeight = localStorage.getItem(storageKey);
        if (storedHeight) {
            contents.style.height = storedHeight;
        }
        let resizer = contents.parentElement.querySelector('.resize');
        resizer.addEventListener('mousedown', function (e) {
            e.preventDefault();
            window.addEventListener('mousemove', resize);
            window.addEventListener('mouseup', stopResize);
            window.addEventListener('dblclick', autoHeight);
        });
        let header = contents.parentElement.querySelector('header');
        let toolbar = document.querySelector('#debugbar .toolbar');

        function resize(e) {
            let move = resizer.getBoundingClientRect().top - e.clientY;
            let height = contents.offsetHeight - 20 + move;
            let toRemove = header.clientHeight + toolbar.clientHeight + 25;
            let maxHeight = window.innerHeight - toRemove;
            if (height < 0) {
                height = 0;
            } else if (height > maxHeight) {
                height = maxHeight;
            }
            contents.style.height = height + 'px';
            localStorage.setItem(storageKey, height + 'px');
        }

        function stopResize() {
            window.removeEventListener('mousemove', resize);
        }

        function autoHeight() {
            let toRemove = header.clientHeight + toolbar.clientHeight + 25;
            let height = window.innerHeight - toRemove + 'px';
            let current = localStorage.getItem(storageKey);
            if (current === height) {
                height = '250px';
            }
            contents.style.height = height;
            localStorage.setItem(storageKey, height);
        }
    },
};
