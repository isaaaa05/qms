-- Create clients table to store all client information
CREATE TABLE IF NOT EXISTS clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id VARCHAR(50) NOT NULL UNIQUE,
    surname VARCHAR(255) NOT NULL,
    full_name VARCHAR(500),
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_surname (surname),
    INDEX idx_client_id (client_id),
    FULLTEXT INDEX ft_surname (surname),
    FULLTEXT INDEX ft_full_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create queue_entries table to track current queue status
CREATE TABLE IF NOT EXISTS queue_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    counter_number INT NOT NULL,
    purpose VARCHAR(500),
    status ENUM('waiting', 'in_service', 'completed', 'no_show') DEFAULT 'waiting',
    called_at TIMESTAMP NULL,
    service_started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_counter (counter_number),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create call_history table to track all service history
CREATE TABLE IF NOT EXISTS call_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    counter_number INT NOT NULL,
    purpose VARCHAR(500),
    called_at TIMESTAMP,
    service_started_at TIMESTAMP,
    service_duration INT COMMENT 'Duration in seconds',
    completed_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_client_id (client_id),
    INDEX idx_counter (counter_number),
    INDEX idx_called_at (called_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
