var Cart = {
    items: [],

    init: function() {
        this.load();
        this.renderSidebar();
        this.updateBadge();
        this.bindGlobal();
    },

    load: function() {
        try { this.items = JSON.parse(localStorage.getItem('cart_items')) || []; }
        catch(e) { this.items = []; }
    },

    save: function() {
        localStorage.setItem('cart_items', JSON.stringify(this.items));
        this.updateBadge();
    },

    add: function(product) {
        var existing = null;
        for (var i = 0; i < this.items.length; i++) {
            if (this.items[i].id == product.id) { existing = this.items[i]; break; }
        }
        if (existing) {
            existing.qty++;
        } else {
            this.items.push({
                id: product.id,
                name: product.name,
                brand: product.brand || '',
                price: parseFloat(product.price),
                image: product.image || '',
                qty: 1
            });
        }
        this.save();
        this.renderSidebar();
        this.open();
        this.notify(product.name);
    },

    remove: function(id) {
        this.items = this.items.filter(function(item) { return item.id != id; });
        this.save();
        this.renderSidebar();
    },

    updateQty: function(id, delta) {
        for (var i = 0; i < this.items.length; i++) {
            if (this.items[i].id == id) {
                this.items[i].qty += delta;
                if (this.items[i].qty < 1) this.items[i].qty = 1;
                break;
            }
        }
        this.save();
        this.renderSidebar();
    },

    getTotal: function() {
        var total = 0;
        for (var i = 0; i < this.items.length; i++) total += this.items[i].price * this.items[i].qty;
        return total;
    },

    getCount: function() {
        var count = 0;
        for (var i = 0; i < this.items.length; i++) count += this.items[i].qty;
        return count;
    },

    updateBadge: function() {
        var badges = document.querySelectorAll('.cart-badge-count');
        var count = this.getCount();
        for (var i = 0; i < badges.length; i++) {
            badges[i].textContent = count;
            badges[i].style.display = count > 0 ? 'flex' : 'none';
        }
    },

    open: function() {
        var overlay = document.getElementById('cartOverlay');
        var sidebar = document.getElementById('cartSidebar');
        if (overlay) overlay.classList.add('open');
        if (sidebar) sidebar.classList.add('open');
        document.body.style.overflow = 'hidden';
    },

    close: function() {
        var overlay = document.getElementById('cartOverlay');
        var sidebar = document.getElementById('cartSidebar');
        if (overlay) overlay.classList.remove('open');
        if (sidebar) sidebar.classList.remove('open');
        document.body.style.overflow = '';
    },

    formatPrice: function(n) {
        return new Intl.NumberFormat('ru-RU').format(Math.round(n)) + ' BYN';
    },

    renderSidebar: function() {
        var body = document.getElementById('cartSidebarBody');
        var foot = document.getElementById('cartSidebarFoot');
        if (!body) return;

        if (this.items.length === 0) {
            body.innerHTML = '<div class="cart-empty"><i class="fas fa-shopping-bag"></i><p>\u041a\u043e\u0440\u0437\u0438\u043d\u0430 \u043f\u0443\u0441\u0442\u0430</p><span>\u0414\u043e\u0431\u0430\u0432\u044c\u0442\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u0438\u0437 \u043a\u0430\u0442\u0430\u043b\u043e\u0433\u0430</span></div>';
            if (foot) foot.style.display = 'none';
            return;
        }

        var html = '';
        for (var i = 0; i < this.items.length; i++) {
            var item = this.items[i];
            var imgTag = item.image ? '<img src="' + item.image + '" alt="">' : '<i class="fas fa-lock" style="font-size:24px;color:#ccc"></i>';
            html += '<div class="cart-item-card">' +
                '<div class="cart-item-img">' + imgTag + '</div>' +
                '<div class="cart-item-info">' +
                    '<div class="cart-item-name">' + item.name + '</div>' +
                    (item.brand ? '<div class="cart-item-brand">' + item.brand + '</div>' : '') +
                    '<div class="cart-item-bottom">' +
                        '<div class="cart-item-price">' + this.formatPrice(item.price * item.qty) + '</div>' +
                        '<div class="cart-qty">' +
                            '<button onclick="Cart.updateQty(' + item.id + ',-1)">&minus;</button>' +
                            '<span>' + item.qty + '</span>' +
                            '<button onclick="Cart.updateQty(' + item.id + ',1)">+</button>' +
                        '</div>' +
                        '<button class="cart-item-remove" onclick="Cart.remove(' + item.id + ')" title="\u0423\u0434\u0430\u043b\u0438\u0442\u044c"><i class="fas fa-trash-alt"></i></button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }
        body.innerHTML = html;

        if (foot) {
            foot.style.display = '';
            var totalEl = foot.querySelector('.cart-total-value');
            if (totalEl) totalEl.textContent = this.formatPrice(this.getTotal());
        }
    },

    notify: function(name) {
        var n = document.createElement('div');
        n.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1a1a2e;color:#fff;padding:14px 24px;border-radius:12px;font-size:14px;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.2);transform:translateY(20px);opacity:0;transition:all 0.3s;display:flex;align-items:center;gap:10px';
        n.innerHTML = '<i class="fas fa-check-circle" style="color:#27ae60;font-size:18px"></i> <span>\u0414\u043e\u0431\u0430\u0432\u043b\u0435\u043d\u043e \u0432 \u043a\u043e\u0440\u0437\u0438\u043d\u0443</span>';
        document.body.appendChild(n);
        setTimeout(function() { n.style.transform = 'translateY(0)'; n.style.opacity = '1'; }, 10);
        setTimeout(function() { n.style.transform = 'translateY(20px)'; n.style.opacity = '0'; setTimeout(function() { n.remove(); }, 300); }, 2500);
    },

    bindGlobal: function() {
        var self = this;
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-add-cart]');
            if (btn) {
                e.preventDefault();
                self.add({
                    id: btn.dataset.id || btn.dataset.addCart,
                    name: btn.dataset.name || '',
                    brand: btn.dataset.brand || '',
                    price: btn.dataset.price || 0,
                    image: btn.dataset.image || ''
                });
            }
            if (e.target.closest('.cart-open-btn')) { self.open(); }
            if (e.target.closest('.cart-close-btn') || e.target.id === 'cartOverlay') { self.close(); }
        });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') self.close(); });
    }
};

document.addEventListener('DOMContentLoaded', function() { Cart.init(); });
