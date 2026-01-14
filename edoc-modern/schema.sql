-- Edoc Modern E-Channeling System - Database Schema
-- MySQL 5.7+

-- Create database
CREATE DATABASE IF NOT EXISTS edoc_modern;
USE edoc_modern;

-- Users table (core authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'patient') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Doctors table
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    specialty VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    bio TEXT,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_specialty (specialty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Patients table
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sessions table (doctor availability)
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME NOT NULL,
    max_bookings INT DEFAULT 50,
    current_bookings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX idx_doctor_date (doctor_id, scheduled_date),
    INDEX idx_scheduled_date (scheduled_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    session_id INT NOT NULL,
    appointment_number INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_session (session_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user
-- Password: 123 (hashed with password_hash in PHP)
INSERT INTO users (email, password, role) VALUES 
('admin@edoc.com', '$2y$10$5f5Dw4J4y.YcC0xhWqZ6EO8Y7X4xGnN9Y7Y9YqZ6EO8Y7X4xGnN9Y', 'admin');

-- Insert test doctor user
INSERT INTO users (email, password, role) VALUES 
('doctor@edoc.com', '$2y$10$5f5Dw4J4y.YcC0xhWqZ6EO8Y7X4xGnN9Y7Y9YqZ6EO8Y7X4xGnN9Y', 'doctor');

INSERT INTO doctors (user_id, name, specialty, phone, bio) VALUES 
(2, 'Test Doctor', 'Accident and emergency', '+1234567890', 'Experienced emergency medicine specialist with over 10 years of practice.');

-- Insert test patient user  
INSERT INTO users (email, password, role) VALUES 
('patient@edoc.com', '$2y$10$5f5Dw4J4y.YcC0xhWqZ6EO8Y7X4xGnN9Y7Y9YqZ6EO8Y7X4xGnN9Y', 'patient');

INSERT INTO patients (user_id, name, phone, address, date_of_birth) VALUES 
(3, 'Test Patient', '+0987654321', '123 Main Street', '1990-01-01');

-- Insert test session
INSERT INTO sessions (doctor_id, title, scheduled_date, scheduled_time, max_bookings) VALUES 
(1, 'Test Session', '2050-01-01', '18:00:00', 50);
