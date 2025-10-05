-- Create Database 
CREATE DATABASE IF NOT EXISTS arch_nergiz; 
USE arch_nergiz; 

-- Table: categories 
CREATE TABLE categories ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL 
); 

-- Table: services 
CREATE TABLE services ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL, 
    image VARCHAR(255), 
    description TEXT 
); 

-- Table: contacts 
CREATE TABLE contacts ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    address VARCHAR(255), 
    email VARCHAR(255), 
    phone VARCHAR(50) 
); 

-- Table: portfolio 
CREATE TABLE portfolio ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL, 
    work VARCHAR(255), 
    type ENUM('interior', 'exterior') NOT NULL, 
    description TEXT, 
    date DATE, 
    category_id INT, 
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL 
); 

-- Table: portfolio_images 
CREATE TABLE portfolio_images ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    portfolio_id INT, 
    image VARCHAR(255) NOT NULL, 
    is_primary BOOLEAN DEFAULT FALSE, 
    FOREIGN KEY (portfolio_id) REFERENCES portfolio(id) ON DELETE CASCADE 
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
