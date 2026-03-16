var Wishlist = {
    items: [],

    init: function() {
        this.load();
        this.updateBadges();
        this.bindGlobal();
    },

    load: function() {
        try { this.items = JSON.parse(localStorage.getItem('wishlist_items')) || []; }
        catch(e) { this.items = []; }
    },

    save: function() {
        localStorage.setItem('wishlist_items', JSON.stringify(this.items));
        this.updateBadges();
    },

    has: function(id) {
        for (var i = 0; i < this.items.length; i++) {
            if (this.items[i].id == id) return true;
        }
        return false;
    },

    toggle: function(product) {
        if (this.has(product.id)) {
            this.items = this.items.filter(function(item) { return item.id != product.id; });
            this.save();
            this.notify(product.name, false);
        } else {
            this.items.push({
                id: product.id,
                name: product.name,
                brand: product.brand || '',
                price: parseFloat(product.price),
                image: product.image || ''
            });
            this.save();
            this.notify(product.name, true);
        }
        this.updateButtons();
    },

    remove: function(id) {
        this.items = this.items.filter(function(item) { return item.id != id; });
        this.save();
        this.updateButtons();
        if (typeof renderWishlistPage === 'function') renderWishlistPage();
    },

    getCount: function() { return this.items.length; },

    updateBadges: function() {
        var badges = document.querySelectorAll('.wishlist-badge-count');
        var count = this.getCount();
        for (var i = 0; i < badges.length; i++) {
            badges[i].textContent = count;
            badges[i].style.display = count > 0 ? 'flex' : 'none';
        }
    },

    updateButtons: function() {
        var self = this;
        var btns = document.querySelectorAll('[data-wishlist-id]');
        for (var i = 0; i < btns.length; i++) {
            var id = btns[i].getAttribute('data-wishlist-id');
            var icon = btns[i].querySelector('i');
            if (self.has(id)) {
                btns[i].classList.add('active');
                if (icon) { icon.className = 'fas fa-heart'; }
                btns[i].style.color = '#e74c3c';
                btns[i].style.borderColor = '#e74c3c';
            } else {
                btns[i].classList.remove('active');
                if (icon) { icon.className = 'far fa-heart'; }
                btns[i].style.color = '';
                btns[i].style.borderColor = '';
            }
        }
    },

    notify: function(name, added) {
        var n = document.createElement('div');
        n.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1a1a2e;color:#fff;padding:14px 24px;border-radius:12px;font-size:14px;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.2);transform:translateY(20px);opacity:0;transition:all 0.3s;display:flex;align-items:center;gap:10px';
        if (added) {
            n.innerHTML = '<i class="fas fa-heart" style="color:#e74c3c;font-size:18px"></i> <span>\u0414\u043e\u0431\u0430\u0432\u043b\u0435\u043d\u043e \u0432 \u0438\u0437\u0431\u0440\u0430\u043d\u043d\u043e\u0435</span>';
        } else {
            n.innerHTML = '<i class="far fa-heart" style="color:#999;font-size:18px"></i> <span>\u0423\u0434\u0430\u043b\u0435\u043d\u043e \u0438\u0437 \u0438\u0437\u0431\u0440\u0430\u043d\u043d\u043e\u0433\u043e</span>';
        }
        document.body.appendChild(n);
        setTimeout(function() { n.style.transform = 'translateY(0)'; n.style.opacity = '1'; }, 10);
        setTimeout(function() { n.style.transform = 'translateY(20px)'; n.style.opacity = '0'; setTimeout(function() { n.remove(); }, 300); }, 2500);
    },

    bindGlobal: function() {
        var self = this;
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-wishlist-id]');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                self.toggle({
                    id: btn.getAttribute('data-wishlist-id'),
                    name: btn.dataset.name || '',
                    brand: btn.dataset.brand || '',
                    price: btn.dataset.price || 0,
                    image: btn.dataset.image || ''
                });
            }
        });

        setTimeout(function() { self.updateButtons(); }, 100);
    }
};

document.addEventListener('DOMContentLoaded', function() { Wishlist.init(); });
