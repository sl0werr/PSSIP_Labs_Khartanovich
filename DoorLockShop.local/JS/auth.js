var AUTH_API = '/api/auth.php';

var Auth = {
    user: null,
    token: null,

    init: function() {
        this.token = localStorage.getItem('auth_token');
        var saved = localStorage.getItem('auth_user');
        if (saved) {
            try { this.user = JSON.parse(saved); } catch(e) { this.user = null; }
        }
        this.renderUI();
        if (this.token) this.checkSession();
        this.bindEvents();
    },

    checkSession: function() {
        var self = this;
        fetch(AUTH_API + '?action=me', {
            headers: { 'Authorization': 'Bearer ' + this.token }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data && data.data.user) {
                self.user = data.data.user;
                localStorage.setItem('auth_user', JSON.stringify(self.user));
            } else {
                self.clearAuth();
            }
            self.renderUI();
        })
        .catch(function() { self.renderUI(); });
    },

    login: function(email, password) {
        var self = this;
        return fetch(AUTH_API + '?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: password })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) throw new Error(data.message);
            self.user = data.data.user;
            self.token = data.data.token;
            localStorage.setItem('auth_token', self.token);
            localStorage.setItem('auth_user', JSON.stringify(self.user));
            document.cookie = 'auth_token=' + self.token + ';path=/;max-age=' + (30*24*3600);
            self.renderUI();
            return data;
        });
    },

    register: function(fields) {
        var self = this;
        return fetch(AUTH_API + '?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(fields)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) throw new Error(data.message);
            self.user = data.data.user;
            self.token = data.data.token;
            localStorage.setItem('auth_token', self.token);
            localStorage.setItem('auth_user', JSON.stringify(self.user));
            document.cookie = 'auth_token=' + self.token + ';path=/;max-age=' + (30*24*3600);
            self.renderUI();
            return data;
        });
    },

    logout: function() {
        var self = this;
        fetch(AUTH_API + '?action=logout', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + this.token }
        }).catch(function(){});
        this.clearAuth();
        this.renderUI();
    },

    clearAuth: function() {
        this.user = null;
        this.token = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        document.cookie = 'auth_token=;path=/;max-age=0';
    },

    isLoggedIn: function() { return !!this.user; },
    isAdmin: function() { return this.user && this.user.role === 'admin'; },

    getInitials: function() {
        if (!this.user) return '?';
        var f = (this.user.first_name || '').charAt(0);
        var l = (this.user.last_name || '').charAt(0);
        return (f + l).toUpperCase() || '?';
    },

    renderUI: function() {
        var container = document.getElementById('authContainer');
        if (!container) return;
        var self = this;

        if (this.isLoggedIn()) {
            var name = this.user.first_name || '';
            var full = name + ' ' + (this.user.last_name || '');
            var email = this.user.email || '';
            var role = this.user.role || 'customer';
            var roleLabel = role === 'admin' ? 'Admin' : role === 'manager' ? 'Manager' : '';
            var roleClass = role === 'admin' ? ' pd-role-admin' : '';
            var adminLink = this.isAdmin()
                ? '<div class="profile-dropdown-divider"></div><li class="pd-admin"><a href="/admin/"><i class="fas fa-cog"></i> \u0410\u0434\u043c\u0438\u043d-\u043f\u0430\u043d\u0435\u043b\u044c</a></li>'
                : '';
            var roleBadge = roleLabel
                ? '<span class="pd-role' + roleClass + '">' + roleLabel + '</span>'
                : '';

            container.innerHTML =
                '<button class="user-profile-btn" id="profileBtn">' +
                    '<div class="user-avatar">' + this.getInitials() + '</div>' +
                    '<span class="user-name">' + name + '</span>' +
                    '<i class="fas fa-chevron-down chevron"></i>' +
                    '<div class="profile-dropdown" id="profileDropdown">' +
                        '<div class="profile-dropdown-header">' +
                            '<div class="pd-avatar">' + this.getInitials() + '</div>' +
                            '<div class="pd-info">' +
                                '<div class="pd-name">' + full.trim() + '</div>' +
                                '<div class="pd-email">' + email + '</div>' +
                                roleBadge +
                            '</div>' +
                        '</div>' +
                        '<ul class="profile-dropdown-menu">' +
                            '<li><a href="/profile.php"><i class="fas fa-user-circle"></i> \u041c\u043e\u0439 \u043f\u0440\u043e\u0444\u0438\u043b\u044c</a></li>' +
                            '<li><a href="#"><i class="fas fa-box"></i> \u041c\u043e\u0438 \u0437\u0430\u043a\u0430\u0437\u044b</a></li>' +
                            '<li><a href="#"><i class="far fa-heart"></i> \u0418\u0437\u0431\u0440\u0430\u043d\u043d\u043e\u0435</a></li>' +
                            adminLink +
                            '<div class="profile-dropdown-divider"></div>' +
                            '<li class="pd-logout"><button id="logoutBtn"><i class="fas fa-sign-out-alt"></i> \u0412\u044b\u0439\u0442\u0438</button></li>' +
                        '</ul>' +
                    '</div>' +
                '</button>';

            var profileBtn = document.getElementById('profileBtn');
            if (profileBtn) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var dd = document.getElementById('profileDropdown');
                    if (dd) dd.classList.toggle('active');
                });
            }
            var logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    self.logout();
                });
            }
        } else {
            container.innerHTML = '<button class="auth-btn" id="openAuthBtn"><i class="fas fa-user"></i> \u0412\u043e\u0439\u0442\u0438</button>';
            var openBtn = document.getElementById('openAuthBtn');
            if (openBtn) {
                openBtn.addEventListener('click', function() { self.openModal('login'); });
            }
        }
    },

    openModal: function(tab) {
        tab = tab || 'login';
        var overlay = document.getElementById('authOverlay');
        var self = this;

        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'authOverlay';
            overlay.className = 'auth-overlay';
            overlay.innerHTML =
                '<div class="auth-modal">' +
                    '<div class="auth-modal-header">' +
                        '<h2>\u0414\u043e\u0431\u0440\u043e \u043f\u043e\u0436\u0430\u043b\u043e\u0432\u0430\u0442\u044c</h2>' +
                        '<button class="auth-close" id="authClose">&times;</button>' +
                    '</div>' +
                    '<div class="auth-tabs">' +
                        '<button class="auth-tab active" data-tab="login">\u0412\u0445\u043e\u0434</button>' +
                        '<button class="auth-tab" data-tab="register">\u0420\u0435\u0433\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u044f</button>' +
                    '</div>' +
                    '<form class="auth-form active" id="loginForm" data-tab="login">' +
                        '<div class="field"><label>Email</label><input type="email" name="email" required placeholder="your@email.com"></div>' +
                        '<div class="field"><label>\u041f\u0430\u0440\u043e\u043b\u044c</label><input type="password" name="password" required placeholder="\u041c\u0438\u043d\u0438\u043c\u0443\u043c 6 \u0441\u0438\u043c\u0432\u043e\u043b\u043e\u0432"></div>' +
                        '<div class="auth-error" id="loginError"></div>' +
                        '<button type="submit" class="auth-submit">\u0412\u043e\u0439\u0442\u0438</button>' +
                    '</form>' +
                    '<form class="auth-form" id="registerForm" data-tab="register">' +
                        '<div class="field"><label>\u0418\u043c\u044f *</label><input type="text" name="first_name" required placeholder="\u0418\u0432\u0430\u043d"></div>' +
                        '<div class="field"><label>\u0424\u0430\u043c\u0438\u043b\u0438\u044f</label><input type="text" name="last_name" placeholder="\u041f\u0435\u0442\u0440\u043e\u0432"></div>' +
                        '<div class="field"><label>Email *</label><input type="email" name="email" required placeholder="your@email.com"></div>' +
                        '<div class="field"><label>\u0422\u0435\u043b\u0435\u0444\u043e\u043d</label><input type="tel" name="phone" placeholder="+7 (999) 123-45-67"></div>' +
                        '<div class="field"><label>\u041f\u0430\u0440\u043e\u043b\u044c *</label><input type="password" name="password" required placeholder="\u041c\u0438\u043d\u0438\u043c\u0443\u043c 6 \u0441\u0438\u043c\u0432\u043e\u043b\u043e\u0432"></div>' +
                        '<div class="auth-error" id="registerError"></div>' +
                        '<div class="auth-success" id="registerSuccess"></div>' +
                        '<button type="submit" class="auth-submit">\u0421\u043e\u0437\u0434\u0430\u0442\u044c \u0430\u043a\u043a\u0430\u0443\u043d\u0442</button>' +
                    '</form>' +
                '</div>';
            document.body.appendChild(overlay);

            overlay.addEventListener('click', function(e) { if (e.target === overlay) self.closeModal(); });
            document.getElementById('authClose').addEventListener('click', function() { self.closeModal(); });

            var tabs = overlay.querySelectorAll('.auth-tab');
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].addEventListener('click', function() {
                    var allTabs = overlay.querySelectorAll('.auth-tab');
                    var allForms = overlay.querySelectorAll('.auth-form');
                    for (var j = 0; j < allTabs.length; j++) allTabs[j].classList.remove('active');
                    for (var j = 0; j < allForms.length; j++) allForms[j].classList.remove('active');
                    this.classList.add('active');
                    overlay.querySelector('.auth-form[data-tab="' + this.dataset.tab + '"]').classList.add('active');
                });
            }

            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var form = e.target;
                var btn = form.querySelector('.auth-submit');
                var errEl = document.getElementById('loginError');
                errEl.classList.remove('visible');
                btn.disabled = true;
                btn.textContent = '\u0412\u0445\u043e\u0434...';
                self.login(form.email.value, form.password.value)
                    .then(function() { self.closeModal(); })
                    .catch(function(err) { errEl.textContent = err.message; errEl.classList.add('visible'); })
                    .finally(function() { btn.disabled = false; btn.textContent = '\u0412\u043e\u0439\u0442\u0438'; });
            });

            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var form = e.target;
                var btn = form.querySelector('.auth-submit');
                var errEl = document.getElementById('registerError');
                errEl.classList.remove('visible');
                btn.disabled = true;
                btn.textContent = '\u0420\u0435\u0433\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u044f...';
                self.register({
                    first_name: form.first_name.value,
                    last_name: form.last_name.value,
                    email: form.email.value,
                    phone: form.phone.value,
                    password: form.password.value
                })
                .then(function() { self.closeModal(); })
                .catch(function(err) { errEl.textContent = err.message; errEl.classList.add('visible'); })
                .finally(function() { btn.disabled = false; btn.textContent = '\u0421\u043e\u0437\u0434\u0430\u0442\u044c \u0430\u043a\u043a\u0430\u0443\u043d\u0442'; });
            });
        }

        var allTabs = overlay.querySelectorAll('.auth-tab');
        var allForms = overlay.querySelectorAll('.auth-form');
        for (var i = 0; i < allTabs.length; i++) allTabs[i].classList.remove('active');
        for (var i = 0; i < allForms.length; i++) allForms[i].classList.remove('active');
        overlay.querySelector('.auth-tab[data-tab="' + tab + '"]').classList.add('active');
        overlay.querySelector('.auth-form[data-tab="' + tab + '"]').classList.add('active');
        overlay.classList.add('active');
    },

    closeModal: function() {
        var ov = document.getElementById('authOverlay');
        if (ov) ov.classList.remove('active');
    },

    bindEvents: function() {
        var self = this;
        document.addEventListener('click', function(e) {
            var dd = document.getElementById('profileDropdown');
            if (dd && dd.classList.contains('active') && !e.target.closest('.user-profile-btn')) {
                dd.classList.remove('active');
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                self.closeModal();
                var dd = document.getElementById('profileDropdown');
                if (dd) dd.classList.remove('active');
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', function() { Auth.init(); });
