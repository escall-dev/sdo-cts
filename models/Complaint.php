<?php
/**
 * Complaint Model
 * Handles all complaint-related database operations
 * Field names match Official DepEd Complaint Assisted Form
 */

require_once __DIR__ . '/../config/database.php';

class Complaint {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Generate unique reference number
     * Format: CTS-YYYY-XXXXX
     */
    public function generateReferenceNumber() {
        $year = date('Y');
        $prefix = "CTS-{$year}-";
        
        $sql = "SELECT reference_number FROM complaints 
                WHERE reference_number LIKE ? 
                ORDER BY id DESC LIMIT 1";
        $result = $this->db->query($sql, [$prefix . '%'])->fetch();
        
        if ($result) {
            $lastNumber = intval(substr($result['reference_number'], -5));
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Create new complaint
     * Fields match Official Complaint Assisted Form
     */
    public function create($data) {
        $referenceNumber = $this->generateReferenceNumber();
        
        $sql = "INSERT INTO complaints (
            reference_number, referred_to, referred_to_other, date_petsa,
            name_pangalan, address_tirahan, contact_number, email_address,
            involved_full_name, involved_position, involved_address, involved_school_office_unit,
            narration_complaint, desired_action_relief, certification_agreed,
            printed_name_pangalan, signature_type, signature_data, date_signed,
            status, is_locked
        ) VALUES (
            ?, ?, ?, NOW(),
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            'pending', 1
        )";

        $params = [
            $referenceNumber,
            $data['referred_to'],
            $data['referred_to_other'] ?? null,
            $data['name_pangalan'],
            $data['address_tirahan'],
            $data['contact_number'],
            $data['email_address'],
            $data['involved_full_name'],
            $data['involved_position'],
            $data['involved_address'],
            $data['involved_school_office_unit'],
            $data['narration_complaint'],
            $data['desired_action_relief'],
            $data['certification_agreed'] ? 1 : 0,
            $data['printed_name_pangalan'],
            $data['signature_type'],
            $data['signature_data'] ?? null,
            date('Y-m-d')
        ];

        $this->db->query($sql, $params);
        $complaintId = $this->db->lastInsertId();

        // Add initial status history
        $this->addStatusHistory($complaintId, 'pending', 'Complaint submitted successfully');

        return [
            'id' => $complaintId,
            'reference_number' => $referenceNumber
        ];
    }

    /**
     * Add document to complaint
     */
    public function addDocument($complaintId, $fileName, $originalName, $fileType, $fileSize) {
        $sql = "INSERT INTO complaint_documents (complaint_id, file_name, original_name, file_type, file_size)
                VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [$complaintId, $fileName, $originalName, $fileType, $fileSize]);
        return $this->db->lastInsertId();
    }

    /**
     * Get complaint by reference number
     */
    public function getByReference($referenceNumber) {
        $sql = "SELECT * FROM complaints WHERE reference_number = ?";
        return $this->db->query($sql, [$referenceNumber])->fetch();
    }

    /**
     * Get complaint by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM complaints WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Get documents for a complaint
     */
    public function getDocuments($complaintId) {
        $sql = "SELECT * FROM complaint_documents WHERE complaint_id = ? ORDER BY upload_date ASC";
        return $this->db->query($sql, [$complaintId])->fetchAll();
    }

    /**
     * Get status history for a complaint
     */
    public function getStatusHistory($complaintId) {
        $sql = "SELECT * FROM complaint_history WHERE complaint_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$complaintId])->fetchAll();
    }

    /**
     * Add status history entry
     */
    public function addStatusHistory($complaintId, $status, $notes = null, $updatedBy = 'System') {
        $sql = "INSERT INTO complaint_history (complaint_id, status, notes, updated_by)
                VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$complaintId, $status, $notes, $updatedBy]);
    }

    /**
     * Update complaint status
     */
    public function updateStatus($id, $status, $notes = null, $updatedBy = 'System') {
        $sql = "UPDATE complaints SET status = ? WHERE id = ?";
        $this->db->query($sql, [$status, $id]);
        $this->addStatusHistory($id, $status, $notes, $updatedBy);
    }

    /**
     * Track complaint by reference number and email
     */
    public function track($referenceNumber, $email) {
        $sql = "SELECT * FROM complaints 
                WHERE reference_number = ? AND email_address = ?";
        return $this->db->query($sql, [$referenceNumber, $email])->fetch();
    }
}
