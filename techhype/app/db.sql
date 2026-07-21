-- ============================================================================
-- TechHype Database
-- ============================================================================

CREATE DATABASE IF NOT EXISTS techhype;
USE techhype;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    photo VARCHAR(255) DEFAULT 'default.png',
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    reset_token VARCHAR(100) DEFAULT NULL,
    email_verified TINYINT(1) DEFAULT 0,
    verify_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    category ENUM('mobile', 'tablet', 'laptop', 'console', 'audio', 'watch', 'accessory') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    specs VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    specifications JSON DEFAULT NULL,
    photo VARCHAR(255) DEFAULT 'default-product.png',
    colors JSON DEFAULT NULL,
    gallery JSON DEFAULT NULL,
    stock INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_name VARCHAR(100) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    payment_method ENUM('card', 'bank', 'cod', 'ewallet') DEFAULT 'cod',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    reviewer_name VARCHAR(100) NOT NULL,
    rating INT NOT NULL DEFAULT 5,
    title VARCHAR(255) DEFAULT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Wishlist table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wish (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Default admin user (password: 12345678)
INSERT INTO users (name, email, password, role, email_verified) VALUES
('Admin', 'admin@techhype.com', '$2y$10$/NX6bQf7Mwpx29INh.xwxuo/iq8b9RJCnJNxraVf6t446gYKl9Zm2', 'admin', 1);

-- Sample products
INSERT INTO products (name, brand, category, price, sale_price, specs, description, stock) VALUES
('Galaxy S25 Ultra', 'Samsung', 'mobile', 5299.00, 3999.00, '256GB | 12GB RAM | 200MP', 'The ultimate Samsung flagship with S Pen, titanium frame, and AI features.', 50),
('Galaxy Z Fold 6', 'Samsung', 'mobile', 7499.00, NULL, '512GB | 12GB RAM | Foldable', 'Samsung foldable phone with multitasking capabilities.', 30),
('Galaxy Tab S9 Ultra', 'Samsung', 'tablet', 4799.00, NULL, '256GB | 12GB RAM | 14.6"', 'Premium Android tablet with AMOLED display.', 25),
('iPhone 16 Pro Max', 'Apple', 'mobile', 5799.00, NULL, '256GB | 8GB RAM | A18 Pro', 'Apple flagship with titanium design and Action button.', 60),
('iPhone 16', 'Apple', 'mobile', 3799.00, 3499.00, '128GB | 8GB RAM | 48MP', 'Powerful iPhone with Camera Control button.', 80),
('iPad Pro M4', 'Apple', 'tablet', 5499.00, NULL, '256GB | 16GB RAM | 13"', 'Thinnest Apple product ever with M4 chip.', 40),
('MacBook Pro M4', 'Apple', 'laptop', 7999.00, NULL, 'M4 | 18GB | 512GB SSD', 'Pro laptop for creative professionals.', 35),
('MacBook Air M3', 'Apple', 'laptop', 5199.00, 4799.00, 'M3 | 16GB | 256GB SSD', 'Thin and light laptop with all-day battery.', 45),
('PlayStation 5 Pro', 'Sony', 'console', 3199.00, NULL, '2TB | 4K 120fps | Ray Tracing', 'Next-gen gaming console with enhanced graphics.', 30),
('PlayStation 5 Slim', 'Sony', 'console', 2199.00, 1869.00, '1TB | Digital Edition', 'Slimmer PS5 design with digital game library.', 50),
('WH-1000XM5', 'Sony', 'audio', 1499.00, 1199.00, 'ANC | 30hr | LDAC | Hi-Res', 'Industry-leading noise cancelling headphones.', 100),
('Pixel 9 Pro XL', 'Google', 'mobile', 4599.00, NULL, '256GB | 16GB RAM | Tensor G4', 'Google AI-first smartphone with best camera.', 40),
('Legion Pro 7i', 'Lenovo', 'laptop', 9999.00, NULL, 'i9 | RTX 4080 | 32GB | 16"', 'High-end gaming laptop for serious gamers.', 15),
('Xiaomi 14 Ultra', 'Xiaomi', 'mobile', 4999.00, NULL, '512GB | 16GB RAM | Leica', 'Xiaomi flagship with Leica professional optics.', 35),
('Nothing Phone (2a) Plus', 'Nothing', 'mobile', 1699.00, NULL, '256GB | 12GB RAM | Glyph', 'Unique transparent design with Glyph interface.', 45),
('Poco F6 Pro', 'Poco', 'mobile', 2199.00, NULL, '256GB | 12GB RAM | SD 8s Gen 3', 'Flagship killer with top-tier performance.', 55),
('Vivo X200 Pro', 'Vivo', 'mobile', 3999.00, NULL, '256GB | 16GB RAM | Zeiss', 'Camera-centric flagship with Zeiss optics.', 30),
('Oppo Find X7 Ultra', 'Oppo', 'mobile', 4499.00, NULL, '256GB | 16GB RAM | Hasselblad', 'Premium phone with Hasselblad camera system.', 25),
('iQOO 15', 'iQOO', 'mobile', 3499.00, NULL, '256GB | 12GB RAM | SD 8 Elite', 'iQOO flagship with Snapdragon 8 Elite, 6000mAh battery, and 120W ultra-fast charging.', 40),
('iQOO Neo 10 5G', 'iQOO', 'mobile', 2099.00, NULL, '256GB | 12GB RAM | SD 8s Gen 4', 'Performance-focused mid-ranger with 6400mAh battery and 120W fast charging.', 50),
('iQOO Z10 5G', 'iQOO', 'mobile', 1299.00, NULL, '128GB | 8GB RAM | SD 7s Gen 3', 'Affordable 5G phone with 6000mAh battery and smooth 120Hz AMOLED display.', 60);
