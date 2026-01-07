<?php
/**
 * ComplaintAdmin Model
 * Extended complaint model for admin operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

class ComplaintAdmin {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all complaints with filters and pagination
     */
    public function getAll($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT c.*, 
                       au.full_name as handled_by_name
                FROM complaints c
                LEFT JOIN admin_users au ON c.handled_by = au.id
                WHERE 1=1";
        $params = [];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['referred_to'])) {
            $sql .= " AND c.referred_to = ?";
            $params[] = $filters['referred_to'];
        }

        if (!empty($filters['assigned_unit'])) {
            $sql .= " AND c.assigned_unit = ?";
            $params[] = $filters['assigned_unit'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(c.date_petsa) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(c.date_petsa) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.reference_number LIKE ? OR c.name_pangalan LIKE ? OR c.email_address LIKE ? OR c.narration_complaint LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get total count with filters
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM complaints c WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['referred_to'])) {
            $sql .= " AND c.referred_to = ?";
            $params[] = $filters['referred_to'];
        }

        if (!empty($filters['assigned_unit'])) {
            $sql .= " AND c.assigned_unit = ?";
            $params[] = $filters['assigned_unit'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(c.date_petsa) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(c.date_petsa) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.reference_number LIKE ? OR c.name_pangalan LIKE ? OR c.email_address LIKE ? OR c.narration_complaint LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'];
    }

    /**
     * Get complaint by ID with full details
     */
    public function getById($id) {
        $sql = "SELECT c.*, 
                       au_handled.full_name as handled_by_name,
                       au_accepted.full_name as accepted_by_name,
                       au_returned.full_name as returned_by_name
                FROM complaints c
                LEFT JOIN admin_users au_handled ON c.handled_by = au_handled.id
                LEFT JOIN admin_users au_accepted ON c.accepted_by = au_accepted.id
                LEFT JOIN admin_users au_returned ON c.returned_by = au_returned.id
                WHERE c.id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Get complaint by reference number
     */
    public function getByReference($referenceNumber) {
        $sql = "SELECT * FROM complaints WHERE reference_number = ?";
        return $this->db->query($sql, [$referenceNumber])->fetch();
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
        $sql = "SELECT ch.*, au.full_name as admin_name
                FROM complaint_history ch
                LEFT JOIN admin_users au ON ch.admin_user_id = au.id
                WHERE ch.complaint_id = ?
                ORDER BY ch.created_at DESC";
        return $this->db->query($sql, [$complaintId])->fetchAll();
    }

    /**
     * Get complaint assignments history
     */
    public function getAssignments($complaintId) {
        $sql = "SELECT ca.*, au.full_name as assigned_by_name
                FROM complaint_assignments ca
                JOIN admin_users au ON ca.assigned_by = au.id
                WHERE ca.complaint_id = ?
                ORDER BY ca.created_at DESC";
        return $this->db->query($sql, [$complaintId])->fetchAll();
    }

    /**
     * Update complaint status
     */
    public function updateStatus($id, $status, $notes, $adminUserId, $adminName) {
        // Check workflow validity
        $complaint = $this->getById($id);
        if (!$complaint) {
            throw new Exception('Complaint not found');
        }

        $allowedTransitions = STATUS_WORKFLOW[$complaint['status']] ?? [];
        if (!in_array($status, $allowedTransitions)) {
            throw new Exception('Invalid status transition');
        }

        // Update complaint
        $sql = "UPDATE complaints SET status = ?, handled_by = ? WHERE id = ?";
        $this->db->query($sql, [$status, $adminUserId, $id]);

        // Add history entry
        $this->addStatusHistory($id, $status, $notes, $adminName, $adminUserId);

        return true;
    }

    /**
     * Accept complaint
     */
    public function accept($id, $notes, $adminUserId, $adminName) {
        $complaint = $this->getById($id);
        if (!$complaint || $complaint['status'] !== 'pending') {
            throw new Exception('Complaint cannot be accepted');
        }

        $sql = "UPDATE complaints SET status = 'accepted', accepted_at = NOW(), accepted_by = ?, handled_by = ? WHERE id = ?";
        $this->db->query($sql, [$adminUserId, $adminUserId, $id]);

        $this->addStatusHistory($id, 'accepted', $notes ?: 'Complaint accepted for processing', $adminName, $adminUserId);

        return true;
    }

    /**
     * Return complaint
     */
    public function returnComplaint($id, $reason, $adminUserId, $adminName) {
        $complaint = $this->getById($id);
        if (!$complaint || !in_array($complaint['status'], ['pending', 'accepted'])) {
            throw new Exception('Complaint cannot be returned');
        }

        $sql = "UPDATE complaints SET status = 'returned', returned_at = NOW(), returned_by = ?, return_reason = ? WHERE id = ?";
        $this->db->query($sql, [$adminUserId, $reason, $id]);

        $this->addStatusHistory($id, 'returned', $reason, $adminName, $adminUserId);

        return true;
    }

    /**
     * Forward complaint to unit
     */
    public function forward($id, $unit, $notes, $adminUserId, $adminName) {
        $complaint = $this->getById($id);
        if (!$complaint) {
            throw new Exception('Complaint not found');
        }

        // Update assigned unit
        $sql = "UPDATE complaints SET assigned_unit = ? WHERE id = ?";
        $this->db->query($sql, [$unit, $id]);

        // Add assignment record
        $sql = "INSERT INTO complaint_assignments (complaint_id, assigned_to_unit, assigned_by, notes) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$id, $unit, $adminUserId, $notes]);

        // Add history
        $unitName = UNITS[$unit] ?? $unit;
        $this->addStatusHistory($id, $complaint['status'], "Forwarded to $unitName: $notes", $adminName, $adminUserId);

        return true;
    }

    /**
     * Add status history entry
     */
    private function addStatusHistory($complaintId, $status, $notes, $updatedBy, $adminUserId = null) {
        $sql = "INSERT INTO complaint_history (complaint_id, status, notes, updated_by, admin_user_id)
                VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [$complaintId, $status, $notes, $updatedBy, $adminUserId]);
    }

    /**
     * Get dashboard statistics
     */
    public function getStatistics() {
        $stats = [];

        // Total complaints
        $result = $this->db->query("SELECT COUNT(*) as total FROM complaints")->fetch();
        $stats['total'] = $result['total'];

        // By status
        $result = $this->db->query("SELECT status, COUNT(*) as count FROM complaints GROUP BY status")->fetchAll();
        $stats['by_status'] = [];
        foreach ($result as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }

        // This month
        $result = $this->db->query("SELECT COUNT(*) as total FROM complaints WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch();
        $stats['this_month'] = $result['total'];

        // This week
        $result = $this->db->query("SELECT COUNT(*) as total FROM complaints WHERE YEARWEEK(created_at) = YEARWEEK(CURRENT_DATE())")->fetch();
        $stats['this_week'] = $result['total'];

        // By referred unit
        $result = $this->db->query("SELECT referred_to, COUNT(*) as count FROM complaints GROUP BY referred_to ORDER BY count DESC")->fetchAll();
        $stats['by_unit'] = $result;

        // Recent trends (last 7 days)
        $result = $this->db->query("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM complaints 
            WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ")->fetchAll();
        $stats['daily_trend'] = $result;

        return $stats;
    }

    /**
     * Get recent complaints for dashboard
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT c.id, c.reference_number, c.name_pangalan, c.referred_to, 
                       c.status, c.created_at, c.narration_complaint
                FROM complaints c
                ORDER BY c.created_at DESC
                LIMIT ?";
        return $this->db->query($sql, [$limit])->fetchAll();
    }

    /**
     * Get count of new complaints since a given timestamp
     */
    public function getNewComplaintsSince($timestamp) {
        $sql = "SELECT COUNT(*) as count FROM complaints WHERE created_at > ?";
        $result = $this->db->query($sql, [$timestamp])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get the latest complaint timestamp
     */
    public function getLatestComplaintTime() {
        $sql = "SELECT MAX(created_at) as latest FROM complaints";
        $result = $this->db->query($sql)->fetch();
        return $result['latest'];
    }
}

