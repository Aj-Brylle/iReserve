USE barangay_db;
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    total_stock INT NOT NULL DEFAULT 0,
    available_stock INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS equipment_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    equipment_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    claimant_name VARCHAR(255) DEFAULT NULL,
    status ENUM('Pending', 'Approved', 'Borrowed', 'Returned', 'Rejected') DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    process_date TIMESTAMP NULL,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
);
