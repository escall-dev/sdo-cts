-- SDO CTS Database Schema
-- San Pedro Division Office Complaint Tracking System
-- Based on Official DepEd Complaint Assisted Form

CREATE DATABASE IF NOT EXISTS sdo_cts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sdo_cts;

-- Complaints Table (Fields match Official Complaint Assisted Form)
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(20) UNIQUE NOT NULL,
    
    -- Referred to (indicate unit/section)
    referred_to ENUM('OSDS', 'SGOD', 'CID', 'Others') NOT NULL,
    referred_to_other VARCHAR(255) DEFAULT NULL,
    date_petsa DATETIME NOT NULL,
    
    -- Complainant/Requestor Information
    name_pangalan VARCHAR(255) NOT NULL,
    address_tirahan TEXT NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email_address VARCHAR(255) NOT NULL,
    
    -- Office/School/Person Involved
    involved_full_name VARCHAR(255) NOT NULL,
    involved_position VARCHAR(255) NOT NULL,
    involved_address TEXT NOT NULL,
    involved_school_office_unit VARCHAR(255) NOT NULL,
    
    -- Narration of Complaint/Inquiry and Relief
    narration_complaint TEXT NOT NULL,
    desired_action_relief TEXT NOT NULL,
    
    -- Certification on Non-Forum Shopping
    certification_agreed TINYINT(1) NOT NULL DEFAULT 0,
    
    -- Name and Signature / Pangalan at Lagda
    printed_name_pangalan VARCHAR(255) NOT NULL,
    signature_type ENUM('digital', 'typed') NOT NULL DEFAULT 'typed',
    signature_data TEXT DEFAULT NULL,
    date_signed DATE NOT NULL,
    
    -- Status Tracking
    status ENUM('pending', 'under_review', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'pending',
    is_locked TINYINT(1) NOT NULL DEFAULT 1,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_reference (reference_number),
    INDEX idx_status (status),
    INDEX idx_date_petsa (date_petsa)
) ENGINE=InnoDB;

-- Supporting Documents Table
CREATE TABLE IF NOT EXISTS complaint_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    INDEX idx_complaint_id (complaint_id)
) ENGINE=InnoDB;

-- Complaint Status History Table
CREATE TABLE IF NOT EXISTS complaint_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    status ENUM('pending', 'under_review', 'in_progress', 'resolved', 'closed') NOT NULL,
    notes TEXT DEFAULT NULL,
    updated_by VARCHAR(255) DEFAULT 'System',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    INDEX idx_complaint_id (complaint_id)
) ENGINE=InnoDB;
