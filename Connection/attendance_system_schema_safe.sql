-- Barangay Officials Attendance Checker System Database Schema
-- Safe version that can be run multiple times

-- Drop existing tables to recreate them cleanly
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS officials;
DROP TABLE IF EXISTS attendance_settings;

-- Create officials table to store barangay officials information
CREATE TABLE officials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    position ENUM('Captain', 'Kagawad', 'Secretary', 'Treasurer', 'Tanod', 'Barangay Health Worker', 'Barangay Nutrition Scholar', 'Barangay Day Care Worker', 'SK Chairman', 'SK Kagawad', 'Other') NOT NULL,
    gmail VARCHAR(255) NOT NULL UNIQUE,
    contact_number VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_position (position),
    INDEX idx_gmail (gmail)
);

-- Create attendance_settings table for system configuration
CREATE TABLE attendance_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create attendance table to store attendance records
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    official_id INT NOT NULL,
    date DATE NOT NULL,
    time_in TIME,
    time_out TIME,
    status ENUM('Present', 'Late', 'Absent', 'On Leave') NOT NULL DEFAULT 'Present',
    scheduled_time_in TIME DEFAULT '08:00:00',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (official_id) REFERENCES officials(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (official_id, date) -- Prevent duplicate entries for same official on same date
);

-- Insert default attendance settings
INSERT INTO attendance_settings (setting_name, setting_value, description) VALUES
('scheduled_time_in', '08:00:00', 'Default scheduled time in for officials'),
('grace_period_minutes', '15', 'Grace period in minutes before marking as late'),
('work_days', 'Monday,Tuesday,Wednesday,Thursday,Friday', 'Regular working days'),
('auto_time_out', '17:00:00', 'Automatic time out if not manually recorded');

-- Insert sample data for barangay officials
INSERT INTO officials (full_name, position, gmail, contact_number) VALUES
('Juan Dela Cruz', 'Captain', 'juan.delacruz@gmail.com', '09123456789'),
('Maria Santos', 'Kagawad', 'maria.santos@gmail.com', '09234567890'),
('Antonio Reyes', 'Secretary', 'antonio.reyes@gmail.com', '09345678901'),
('Josefina Martinez', 'Treasurer', 'josefina.martinez@gmail.com', '09456789012'),
('Roberto Garcia', 'Tanod', 'roberto.garcia@gmail.com', '09567890123');

-- Create indexes for better performance
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_attendance_status ON attendance(status);
CREATE INDEX idx_attendance_official_date ON attendance(official_id, date);
