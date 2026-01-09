<?php
/**
 * Migration script to add category column to complaint_documents table
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    
    // Check if column already exists
    $result = $db->query("SHOW COLUMNS FROM complaint_documents LIKE 'category'")->fetch();
    
    if (!$result) {
        $db->query("ALTER TABLE complaint_documents ADD COLUMN category VARCHAR(50) DEFAULT 'supporting' AFTER file_size");
        echo "SUCCESS: Category column added to complaint_documents table.\n";
    } else {
        echo "INFO: Category column already exists.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
