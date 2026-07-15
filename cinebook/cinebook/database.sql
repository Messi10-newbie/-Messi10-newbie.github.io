-- ============================================================
-- Campus Bites — Campus Food Court Pre-Order System
-- XAMPP / MySQL 5.7+
--
-- SETUP (for a new machine):
--   1. Start Apache + MySQL in the XAMPP Control Panel.
--   2. Open http://localhost/phpmyadmin
--   3. Click "Import" → choose this file → Go.
--      (This creates the `food_court` database and fills it with
--      sample stalls, menu items, and pickup slots automatically.)
--   4. Open the app, e.g. http://localhost/WIS-01/app/index.php
-- ============================================================

CREATE DATABASE IF NOT EXISTS food_court CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE food_court;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS pickup_slots;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS stalls;
SET FOREIGN_KEY_CHECKS = 1;

-- ─── Stalls ─────────────────────────────────────────────────────────────────
CREATE TABLE stalls (
    stall_id INT AUTO_INCREMENT PRIMARY KEY,
    stall_name VARCHAR(120) NOT NULL,
    cuisine VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    starting_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sort_order INT NOT NULL DEFAULT 0,
    is_open TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ─── Menu Items ─────────────────────────────────────────────────────────────
CREATE TABLE menu_items (
    menu_item_id INT AUTO_INCREMENT PRIMARY KEY,
    stall_id INT NOT NULL,
    item_name VARCHAR(120) NOT NULL,
    item_desc VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_popular TINYINT(1) NOT NULL DEFAULT 0,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    image_path VARCHAR(255) NULL,
    rating DECIMAL(2,1) NULL,
    rating_count INT NULL,
    FOREIGN KEY (stall_id) REFERENCES stalls(stall_id)
) ENGINE=InnoDB;

-- ─── Pickup Slots ───────────────────────────────────────────────────────────
CREATE TABLE pickup_slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    slot_date DATE NOT NULL,
    slot_time VARCHAR(20) NOT NULL,
    capacity INT NOT NULL DEFAULT 20,
    remaining INT NOT NULL DEFAULT 20,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ─── Orders ─────────────────────────────────────────────────────────────────
-- stall_id is NULL when the order mixes items from several stalls; the stalls
-- involved are always derivable from order_items → menu_items → stalls.
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_reference VARCHAR(15) NOT NULL UNIQUE,
    stall_id INT NULL,
    slot_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    notes VARCHAR(255) NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_status ENUM('pending','preparing','delayed','ready','collected','cancelled') NOT NULL DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stall_id) REFERENCES stalls(stall_id),
    FOREIGN KEY (slot_id) REFERENCES pickup_slots(slot_id)
) ENGINE=InnoDB;

-- ─── Order Items ────────────────────────────────────────────────────────────
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    options VARCHAR(255) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(menu_item_id)
) ENGINE=InnoDB;

-- ─── Seed: Stalls (one closed, to show the "Closed" badge) ─────────────────
INSERT INTO stalls (stall_name, cuisine, description, starting_price, sort_order, is_open, is_active) VALUES
('Noodle Corner', 'Chinese', 'Wok noodles, rice bowls, and quick lunch sets', 6.50, 1, 1, 1),
('Mamak Express', 'Malay', 'Rice, roti, and local comfort food', 5.00, 2, 1, 1),
('Rice Bowl Lab', 'Fusion', 'Custom rice bowls with proteins and toppings', 8.90, 3, 1, 1),
('Snack Station', 'Snacks', 'Light bites, drinks, and grab-and-go items', 2.50, 4, 1, 1),
('Veggie Fresh', 'Vegetarian', 'Salads, wraps, and lighter meals', 7.50, 5, 0, 1),
('Sweet Stop', 'Dessert', 'Desserts, pastries, and coffee', 3.20, 6, 1, 1);

-- ─── Seed: Menu Items ────────────────────────────────────────────────────────
INSERT INTO menu_items (stall_id, item_name, item_desc, price, sort_order, is_popular, is_available) VALUES
(1, 'Chicken Char Kuey Teow', 'Stir-fried noodles with chicken and prawns', 9.90, 1, 1, 1),
(1, 'Soy Garlic Udon', 'Thick noodles tossed in a soy garlic sauce', 8.50, 2, 0, 1),
(1, 'Beef Fried Rice', 'Classic fried rice with sliced beef', 10.50, 3, 1, 1),
(2, 'Nasi Lemak Ayam', 'Fragrant rice with crispy chicken', 7.90, 1, 1, 1),
(2, 'Roti Canai Set', 'Two roti canai with dhal and curry', 5.50, 2, 0, 1),
(2, 'Mee Goreng Mamak', 'Spicy fried noodles with egg and vegetables', 6.90, 3, 1, 1),
(2, 'Mee Curry', 'Curry noodle soup with chicken, tofu puffs and bean sprouts', 7.50, 4, 1, 1),
(3, 'Teriyaki Chicken Bowl', 'Rice bowl with teriyaki chicken and veg', 11.90, 1, 1, 1),
(3, 'Spicy Tofu Bowl', 'Protein-rich tofu bowl with sambal glaze', 9.90, 2, 0, 1),
(4, 'Iced Lemon Tea', 'Refreshing campus favorite', 2.50, 1, 1, 1),
(4, 'Chicken Samosa', 'Crispy snack packs of 3 pieces', 4.20, 2, 0, 1),
(5, 'Grilled Chicken Wrap', 'Wrap with greens and house sauce', 10.90, 1, 1, 1),
(5, 'Garden Salad', 'Fresh salad with citrus dressing', 7.50, 2, 0, 1),
(6, 'Brownie Fudge', 'Warm chocolate brownie slice', 4.80, 1, 1, 1),
(6, 'Iced Latte', 'Smooth coffee served chilled', 5.50, 2, 1, 1);

-- ─── Seed: Pickup Slots ──────────────────────────────────────────────────────
-- Dates are relative to today so the slots are always valid on import day.
-- (The app also auto-shifts these forward if they ever go stale — see
-- ensureUpcomingSlots() in _functions.php — but fresh dates avoid the wait.)
INSERT INTO pickup_slots (slot_date, slot_time, capacity, remaining, is_active) VALUES
(CURDATE(), '11:30 AM', 20, 20, 1),
(CURDATE(), '12:00 PM', 20, 20, 1),
(CURDATE(), '12:30 PM', 20, 12, 1),
(CURDATE(), '01:00 PM', 20, 20, 1),
(CURDATE(), '01:30 PM', 20, 3, 1),
(CURDATE(), '02:00 PM', 20, 20, 1),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:30 AM', 20, 20, 1),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00 PM', 20, 20, 1),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:30 PM', 20, 20, 1);
