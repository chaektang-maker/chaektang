-- Create database if not exists
CREATE DATABASE IF NOT EXISTS luckyd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lottobackdata;

-- Create table for lotto data
CREATE TABLE IF NOT EXISTS lotto_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lotto_id VARCHAR(50) NOT NULL UNIQUE,
    url VARCHAR(255) NOT NULL,
    date_text VARCHAR(255) NOT NULL,
    is_fetched TINYINT(1) DEFAULT 0 COMMENT '0=ยังไม่ได้ดึงข้อมูล, 1=ดึงข้อมูลแล้ว',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lotto_id (lotto_id),
    INDEX idx_date_text (date_text),
    INDEX idx_is_fetched (is_fetched)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for lotto details
CREATE TABLE IF NOT EXISTS lotto_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lotto_id VARCHAR(50) NOT NULL UNIQUE,
    date VARCHAR(255) NOT NULL,
    endpoint VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lotto_id) REFERENCES lotto_data(lotto_id) ON DELETE CASCADE,
    INDEX idx_lotto_id (lotto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for lotto prizes
CREATE TABLE IF NOT EXISTS lotto_prizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lotto_id VARCHAR(50) NOT NULL,
    prize_id VARCHAR(50) NOT NULL,
    prize_name VARCHAR(255) NOT NULL,
    reward VARCHAR(50) NOT NULL,
    amount INT NOT NULL,
    numbers JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lotto_id) REFERENCES lotto_data(lotto_id) ON DELETE CASCADE,
    INDEX idx_lotto_id (lotto_id),
    INDEX idx_prize_id (prize_id),
    UNIQUE KEY unique_lotto_prize (lotto_id, prize_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for lotto running numbers
CREATE TABLE IF NOT EXISTS lotto_running_numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lotto_id VARCHAR(50) NOT NULL,
    running_id VARCHAR(50) NOT NULL,
    running_name VARCHAR(255) NOT NULL,
    reward VARCHAR(50) NOT NULL,
    amount INT NOT NULL,
    numbers JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lotto_id) REFERENCES lotto_data(lotto_id) ON DELETE CASCADE,
    INDEX idx_lotto_id (lotto_id),
    INDEX idx_running_id (running_id),
    UNIQUE KEY unique_lotto_running (lotto_id, running_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
