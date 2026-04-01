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
     * Format: CT[A-Z0-9]{3}-YYYYMM-XXX
     */
    public function generateReferenceNumber() {
        $yearMonth = date('Ym');

        $sql = "SELECT reference_number FROM complaints
                WHERE reference_number LIKE ?
                ORDER BY id DESC LIMIT 1";
        $result = $this->db->query($sql, ["CT___-{$yearMonth}-%"])->fetch();

        if ($result) {
            $lastSeries = intval(substr($result['reference_number'], -3));
            $nextSeries = $lastSeries + 1;
        } else {
            $nextSeries = 1;
        }

        if ($nextSeries > 999) {
            throw new Exception("Monthly reference limit reached for {$yearMonth}.");
        }

        $series = str_pad((string)$nextSeries, 3, '0', STR_PAD_LEFT);
        $randomPart = $this->generateRandomAlphaNumeric(3);

        return "CT{$randomPart}-{$yearMonth}-{$series}";
    }

    /**
     * Generate random uppercase alphanumeric string.
     */
    private function generateRandomAlphaNumeric($length = 3) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxIndex = strlen($characters) - 1;
        $random = '';

        for ($i = 0; $i < $length; $i++) {
            $random .= $characters[random_int(0, $maxIndex)];
        }

        return $random;
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
            narration_complaint, narration_complaint_page2, desired_action_relief, certification_agreed,
            printed_name_pangalan, signature_type, signature_data, date_signed,
            status, is_locked
        ) VALUES (
            ?, ?, ?, NOW(),
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            'pending', 1
        )";

        $params = [
            $referenceNumber,
            $data['referred_to'] ?? null,
            $data['referred_to_other'] ?? null,
            $data['name_pangalan'] ?? null,
            $data['address_tirahan'] ?? null,
            $data['contact_number'] ?? null,
            $data['email_address'] ?? null,
            $data['involved_full_name'] ?? null,
            $data['involved_position'] ?? null,
            $data['involved_address'] ?? null,
            $data['involved_school_office_unit'] ?? null,
            $data['narration_complaint'] ?? null,
            $data['narration_complaint_page2'] ?? null,
            $data['desired_action_relief'] ?? null,
            !empty($data['certification_agreed']) ? 1 : 0,
            $data['printed_name_pangalan'] ?? null,
            $data['signature_type'] ?? 'typed',
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
     * @param int $complaintId Complaint ID
     * @param string $fileName File name stored on disk
     * @param string $originalName Original file name
     * @param string $fileType MIME type
     * @param int $fileSize File size in bytes
     * @param string $category Category (supporting, valid_id, handwritten_form)
     * @param string $filePath Relative path to file (e.g., assets/uploads/images/complaint_1_supporting_123.jpg)
     */
    public function addDocument($complaintId, $fileName, $originalName, $fileType, $fileSize, $category = 'supporting', $filePath = '') {
        $sql = "INSERT INTO complaint_documents (complaint_id, file_name, file_path, original_name, file_type, file_size, category)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [$complaintId, $fileName, $filePath, $originalName, $fileType, $fileSize, $category]);
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
