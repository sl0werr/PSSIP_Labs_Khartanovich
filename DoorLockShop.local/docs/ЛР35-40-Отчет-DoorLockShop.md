# Лабораторная работа № 35–40

## Отчёт по проекту DoorLockShop (интернет-магазин электронных замков GoldenService)

**Проект:** DoorLockShop  
**Стек:** PHP, MySQL, REST API, HTML/CSS/JavaScript  
**База данных:** doorlockshop (11 таблиц: users, user_sessions, categories, brands, products, product_images, delivery_methods, payment_methods, orders, order_items, callbacks)

---

## 1. ДОБАВЛЕНИЕ ДАННЫХ (CREATE)

### 1.1. Добавление заявки на обратный звонок

Форма добавления новой заявки доступна на сайте в блоке «Обратный звонок». Пользователь вводит имя, телефон, email и сообщение.

**Рисунок 1** – Форма добавления заявки на обратный звонок  
*(Скриншот формы на главной странице или в модальном окне)*

```php
// api/callback.php — создание заявки
$stmt = $db->prepare("INSERT INTO callbacks (name, phone, email, message, status) VALUES (?, ?, ?, ?, 'new')");
$stmt->execute([$name ?: null, $phone ?: null, $email ?: null, $message ?: null]);
$id = (int)$db->lastInsertId();
```

**Рисунок 2** – Добавленная заявка в админ-панели  
*(Скриншот таблицы заявок в /admin/)*

---

### 1.2. Добавление заказа

При оформлении заказа создаётся запись в таблице `orders` и связанные позиции в `order_items`.

```php
// api/orders.php — создание заказа
$stmt = $db->prepare("INSERT INTO orders (user_id, order_number, first_name, last_name, email, phone, delivery_method_id, delivery_address, delivery_price, payment_method_id, subtotal, total, comment) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
$stmt->execute([$userId, $orderNumber, $firstName, $lastName ?: null, $email, $phone, $deliveryId, $address ?: null, $deliveryPrice, $paymentId, $subtotal, $total, $comment ?: null]);
$orderId = (int)$db->lastInsertId();

$itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_sku, price, quantity, total) VALUES (?,?,?,?,?,?,?)");
foreach ($orderItems as $oi) {
    $itemStmt->execute([$orderId, $oi['product_id'], $oi['product_name'], $oi['product_sku'], $oi['price'], $oi['quantity'], $oi['total']]);
}
```

---

### 1.3. Регистрация пользователя

```php
// api/auth.php — регистрация
$stmt = $db->prepare("INSERT INTO users (email, phone, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'customer')");
$stmt->execute([$email, $phone ?: null, $hash, $firstName, $lastName ?: null]);
$userId = (int)$db->lastInsertId();
```

---

## 2. РЕДАКТИРОВАНИЕ ДАННЫХ (UPDATE)

### 2.1. Изменение статуса заявки

В админ-панели администратор может изменить статус заявки на обратный звонок (new → processing → done).

**Рисунок 3** – Редактирование статуса заявки  
*(Скриншот админ-панели с заявками)*

```php
// api/callback.php — обновление статуса заявки
$db->prepare("UPDATE callbacks SET status = ? WHERE id = ?")->execute([$status, $id]);
```

**Рисунок 4** – Заявка после изменения статуса  
*(Скриншот обновлённой заявки)*

---

### 2.2. Обновление остатков при оформлении заказа

При создании заказа уменьшается остаток товара на складе и увеличивается счётчик продаж:

```php
// api/orders.php — обновление товара при заказе
$db->prepare("UPDATE products SET stock = stock - ?, sales_count = sales_count + ? WHERE id = ?")
   ->execute([$oi['quantity'], $oi['quantity'], $oi['product_id']]);
```

---

### 2.3. Увеличение счётчика просмотров товара

```php
// api/products.php — при просмотре карточки товара
$db->prepare("UPDATE products SET views_count = views_count + 1 WHERE id = ?")->execute([$product['id']]);
```

---

## 3. УДАЛЕНИЕ ДАННЫХ (DELETE)

### 3.1. Выход из системы (удаление сессии)

При выходе пользователя удаляется запись сессии из таблицы `user_sessions`.

**Рисунок 5** – Подтверждение выхода из системы  
*(Скриншот кнопки «Выйти» или модального окна)*

```php
// api/auth.php — выход (удаление сессии)
$stmt = $db->prepare("DELETE FROM user_sessions WHERE token = ?");
$stmt->execute([$token]);
```

**Рисунок 6** – Результат удаления сессии (пользователь разлогинен)  
*(Скриншот главной страницы после выхода)*

---

## 4. ГЕНЕРАЦИЯ ОТЧЁТА (SELECT для отчёта)

### 4.1. Получение данных заказа для отображения

При просмотре заказа выполняется SELECT с JOIN для формирования полного отчёта по заказу (аналог чека).

**Рисунок 7** – Запрос на просмотр заказа  
*(Скриншот страницы «Мои заказы» или карточки заказа)*

```php
// api/orders.php — получение данных заказа
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

$itemStmt = $db->prepare("SELECT oi.*, (SELECT pi.path FROM product_images pi WHERE pi.product_id = oi.product_id AND pi.is_main = 1 LIMIT 1) AS image FROM order_items oi WHERE oi.order_id = ?");
$itemStmt->execute([$orderId]);
$order['items'] = $itemStmt->fetchAll();
```

Данные используются для отображения «чека» заказа: номер заказа, дата, товары, суммы, способ доставки и оплаты.

**Рисунок 8** – Готовый отчёт по заказу (чек)  
*(Скриншот страницы с деталями заказа)*

---

### 4.2. Dashboard админ-панели

```php
// admin/index.php — сводная статистика
$productsCount = $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$ordersCount = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$revenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$recentOrders = $db->query("SELECT o.*, u.first_name, u.last_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();
```

---

## Выводы

В проекте DoorLockShop реализованы все основные операции CRUD:

1. **CREATE** — добавление заявок на обратный звонок, заказов, пользователей, сессий.
2. **UPDATE** — изменение статуса заявок, обновление остатков товаров и счётчиков просмотров.
3. **DELETE** — удаление сессий при выходе пользователя.
4. **SELECT** — формирование отчётов: детали заказа (чек), статистика в админ-панели.

Все запросы выполняются через подготовленные выражения (prepared statements) для защиты от SQL-инъекций. Используются транзакции при создании заказов для обеспечения целостности данных.
