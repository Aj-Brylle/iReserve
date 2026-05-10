CREATE DATABASE IF NOT EXISTS barangay_db;
USE barangay_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Resident') NOT NULL DEFAULT 'Resident',
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS residents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT '',
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    contact_number VARCHAR(20),
    email VARCHAR(100),
    address TEXT NOT NULL,
    household_info VARCHAR(255),
    valid_id_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    author_admin_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin (password: admin123)
-- bcrypt hash for 'admin123' is $2y$10$Rz2r7O.mR4o.qI0Yx0uKFeInM.Q4U3Y0T6R2/x80l4T3.O1Z/Bf4e
-- default admin account will use email as identifier so username is set to admin@gmail.com
-- create default admin directly in admins table (password: admin123)
INSERT IGNORE INTO admins (id, email, password_hash, name) VALUES (1, 'admin@gmail.com', '$2y$10$Rz2r7O.mR4o.qI0Yx0uKFeInM.Q4U3Y0T6R2/x80l4T3.O1Z/Bf4e', 'Administrator');
