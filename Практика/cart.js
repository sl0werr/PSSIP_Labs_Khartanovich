// === ОБЩИЙ КОД ДЛЯ ВСЕХ СТРАНИЦ ===

// Класс для работы с корзиной
class CartManager {
    constructor() {
        this.cartKey = 'goldenSoftCart';
        this.cart = this.loadCart();
    }
    
    // Загрузка корзины из localStorage
    loadCart() {
        const cartData = localStorage.getItem(this.cartKey);
        return cartData ? JSON.parse(cartData) : [];
    }
    
    // Сохранение корзины в localStorage
    saveCart() {
        localStorage.setItem(this.cartKey, JSON.stringify(this.cart));
    }
    
    // Добавление товара в корзину
    addToCart(product) {
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                id: product.id,
                title: product.title,
                price: product.price,
                oldPrice: product.oldPrice,
                quantity: 1,
                badges: product.badges,
                icon: product.icon
            });
        }
        
        this.saveCart();
        return this.cart;
    }
    
    // Удаление товара из корзины
    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
        return this.cart;
    }
    
    // Обновление количества
    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                return this.removeFromCart(productId);
            }
            item.quantity = quantity;
            this.saveCart();
        }
        return this.cart;
    }
    
    // Получение всей корзины
    getCart() {
        return this.cart;
    }
    
    // Получение общего количества товаров
    getTotalCount() {
        return this.cart.reduce((total, item) => total + item.quantity, 0);
    }
    
    // Получение общей суммы
    getTotalPrice() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    // Очистка корзины
    clearCart() {
        this.cart = [];
        this.saveCart();
        return this.cart;
    }
}

// Создаем глобальный экземпляр менеджера корзины
window.cartManager = new CartManager();

// Функция обновления счетчика корзины во всех местах
function updateCartCounter() {
    const totalCount = cartManager.getTotalCount();
    
    // Обновляем счетчик в хедере
    const cartCounters = document.querySelectorAll('#cartCount, .cart-count');
    cartCounters.forEach(counter => {
        if (counter) {
            counter.textContent = totalCount;
            counter.style.display = totalCount > 0 ? 'flex' : 'none';
        }
    });
    
    // Обновляем счетчик в корзине (если открыта)
    const cartProductCount = document.querySelector('.cart-product-count');
    if (cartProductCount) {
        cartProductCount.textContent = totalCount;
    }
}

// Функция для рендеринга корзины
function renderCartItems() {
    const cart = cartManager.getCart();
    const cartContent = document.querySelector('.cart-content');
    
    if (!cartContent) return;
    
    if (cart.length === 0) {
        cartContent.innerHTML = `
            <div style="text-align: center; padding: 40px 0;">
                <i class="fas fa-shopping-basket" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                <h3 style="color: #666; margin-bottom: 10px;">Корзина пуста</h3>
                <p style="color: #888;">Добавьте товары из каталога</p>
            </div>
        `;
        
        // Добавляем секцию "С этим покупают" если она есть
        const alsoBuySection = document.querySelector('.also-buy-section');
        if (alsoBuySection) {
            cartContent.appendChild(alsoBuySection);
        }
        
        return;
    }
    
    // Рендерим товары
    const itemsHTML = cart.map(item => `
        <div class="cart-item" data-id="${item.id}">
            <div class="cart-item-header">
                <div class="cart-item-title">${item.title}</div>
                <div class="cart-item-price">${formatPrice(item.price)} ₽</div>
            </div>
            <div class="cart-item-quantity">
                <button class="quantity-btn minus" data-id="${item.id}">-</button>
                <span class="quantity">${item.quantity} шт</span>
                <button class="quantity-btn plus" data-id="${item.id}">+</button>
            </div>
            <div class="cart-item-subtotal">
                Сумма: ${formatPrice(item.price * item.quantity)} ₽
            </div>
            <div class="cart-item-actions">
                <button class="delete-item" data-id="${item.id}">
                    <i class="fas fa-trash"></i>
                    Удалить
                </button>
            </div>
        </div>
    `).join('');
    
    cartContent.innerHTML = itemsHTML;
    
    // Добавляем секцию "С этим покупают" если она есть
    const alsoBuySection = document.querySelector('.also-buy-section');
    if (alsoBuySection && !cartContent.querySelector('.also-buy-section')) {
        cartContent.appendChild(alsoBuySection.cloneNode(true));
    }
    
    // Назначаем обработчики
    setupCartEventHandlers();
}

// Функция для обновления итоговой суммы
function updateCartTotal() {
    const totalPrice = cartManager.getTotalPrice();
    const totalPriceElement = document.querySelector('.total-price');
    
    if (totalPriceElement) {
        totalPriceElement.textContent = formatPrice(totalPrice) + ' ₽';
    }
}

// Функция для настройки обработчиков событий в корзине
function setupCartEventHandlers() {
    // Кнопки удаления
    document.querySelectorAll('.delete-item').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.getAttribute('data-id'));
            if (confirm('Удалить товар из корзины?')) {
                cartManager.removeFromCart(productId);
                renderCartItems();
                updateCartCounter();
                updateCartTotal();
            }
        });
    });
    
    // Кнопки изменения количества
    document.querySelectorAll('.quantity-btn.minus').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.getAttribute('data-id'));
            const cartItem = cartManager.getCart().find(item => item.id === productId);
            
            if (cartItem && cartItem.quantity > 1) {
                cartManager.updateQuantity(productId, cartItem.quantity - 1);
                renderCartItems();
                updateCartCounter();
                updateCartTotal();
            }
        });
    });
    
    document.querySelectorAll('.quantity-btn.plus').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.getAttribute('data-id'));
            const cartItem = cartManager.getCart().find(item => item.id === productId);
            
            if (cartItem) {
                cartManager.updateQuantity(productId, cartItem.quantity + 1);
                renderCartItems();
                updateCartCounter();
                updateCartTotal();
            }
        });
    });
}

// Функция добавления товара в корзину (общая для всех страниц)
function addToCart(productId, productData = null) {
    let product;
    
    // Если переданы данные товара, используем их
    if (productData) {
        product = productData;
    } else {
        // Ищем товар в списке товаров страницы
        product = window.products ? window.products.find(p => p.id === productId) : null;
    }
    
    if (!product) {
        console.error('Товар не найден:', productId);
        return;
    }
    
    cartManager.addToCart(product);
    updateCartCounter();
    
    // Если корзина открыта, обновляем ее содержимое
    if (document.querySelector('.cart-modal.active')) {
        renderCartItems();
        updateCartTotal();
    }
    
    // Показываем уведомление
    showNotification(`Товар "${product.title}" добавлен в корзину!`);
    
    return cartManager.getCart();
}

// Функция показа уведомления
function showNotification(message, type = 'success') {
    // Удаляем старое уведомление если есть
    const oldNotification = document.querySelector('.cart-notification');
    if (oldNotification) {
        oldNotification.remove();
    }
    
    // Создаем новое уведомление
    const notification = document.createElement('div');
    notification.className = `cart-notification cart-notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    // Анимация появления
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Закрытие по кнопке
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });
    
    // Автозакрытие через 3 секунды
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

// Функция форматирования цены
function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Обновляем счетчик корзины
    updateCartCounter();
    
    // Если на странице есть модальное окно корзины, рендерим товары
    if (document.querySelector('.cart-modal')) {
        renderCartItems();
        updateCartTotal();
    }
    
    // Назначаем глобальные обработчики для кнопок корзины
    document.querySelectorAll('.add-to-cart, .buy-button, .add-to-cart-small').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.getAttribute('data-product-id') || this.closest('[data-id]')?.getAttribute('data-id'));
            if (productId) {
                addToCart(productId);
            }
        });
    });
});

// Стили для уведомлений
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .cart-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        z-index: 9999;
        border-left: 4px solid #2ecc71;
    }
    
    .cart-notification.show {
        transform: translateX(0);
    }
    
    .cart-notification-error {
        border-left-color: #e74c3c;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }
    
    .notification-content i {
        color: #2ecc71;
        font-size: 20px;
    }
    
    .cart-notification-error .notification-content i {
        color: #e74c3c;
    }
    
    .notification-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #666;
        margin-left: 10px;
    }
    
    .notification-close:hover {
        color: #333;
    }
    
    /* Стили для количества в корзине */
    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 10px 0;
    }
    
    .quantity-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #ddd;
        background: white;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .quantity-btn:hover {
        background: #f5f5f5;
    }
    
    .quantity {
        min-width: 40px;
        text-align: center;
    }
    
    .cart-item-subtotal {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }
`;

document.head.appendChild(notificationStyles);