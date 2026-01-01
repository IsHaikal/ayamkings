-- AyamKings Database Initialization Script
-- This script runs automatically when Docker MySQL container starts

-- Create tables if they don't exist
-- Users table for customer login
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'staff', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items table
CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'ready', 'rejected', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    items_json TEXT,
    coupon_code VARCHAR(50),
    discount_amount DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    menu_item_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (menu_item_id) REFERENCES menu(id)
);

-- Coupons table
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_percent DECIMAL(5,2),
    discount_amount DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    times_used INT DEFAULT 0,
    max_uses INT,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Daily specials table
CREATE TABLE IF NOT EXISTS daily_specials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    end_date DATETIME,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample menu items
INSERT INTO menu (name, description, price, category, image_url) VALUES
('Fried Chicken', 'Crispy golden fried chicken', 8.00, 'Chicken', 'uploads/fried_chicken.jpg'),
('Spicy Fried Chicken', 'Hot and spicy fried chicken', 9.00, 'Chicken', 'uploads/spicy_chicken.jpg'),
('Double Fried Chicken', 'Double the Finger Lickin Good', 10.00, 'Chicken', 'uploads/double_chicken.jpg'),
('Nasi Lemak', 'Traditional Malaysian coconut rice', 6.00, 'Rice', 'uploads/nasi_lemak.jpg'),
('Mashed Potato', 'Creamy mashed potato', 4.00, 'Sides', 'uploads/mashed_potato.jpg'),
('Coleslaw', 'Fresh coleslaw salad', 3.00, 'Sides', 'uploads/coleslaw.jpg'),
('Coca Cola', 'Refreshing cola drink', 3.00, 'Drinks', 'uploads/coca_cola.jpg'),
('Mineral Water', 'Pure mineral water', 2.00, 'Drinks', 'uploads/water.jpg')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@ayamkings.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Staff', 'staff@ayamkings.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'),
('Customer Test', 'test@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer')
ON DUPLICATE KEY UPDATE name=name;
