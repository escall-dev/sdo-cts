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
                       au.full_name as handled_by_name,
                       docs.doc_count
                FROM complaints c
                LEFT JOIN admin_users au ON c.handled_by = au.id
                LEFT JOIN (
                    SELECT complaint_id, COUNT(*) AS doc_count
                    FROM complaint_documents
                    GROUP BY complaint_id
                ) docs ON docs.complaint_id = c.id
                WHERE 1=1";
        $params = [];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['assigned_unit'])) {
            $sql .= " AND c.assigned_unit = ?";
            $params[] = $filters['assigned_unit'];
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND c.involved_school_office_unit = ?";
            $params[] = $filters['unit'];
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

        if (!empty($filters['assigned_unit'])) {
            $sql .= " AND c.assigned_unit = ?";
            $params[] = $filters['assigned_unit'];
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND c.involved_school_office_unit = ?";
            $params[] = $filters['unit'];
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

        // Filed and received by unit totals
        $filedResult = $this->db->query(
            "SELECT COUNT(*) as total
             FROM complaints
             WHERE involved_school_office_unit IS NOT NULL
             AND involved_school_office_unit <> ''"
        )->fetch();
        $stats['filed_by_unit_total'] = $filedResult['total'] ?? 0;

        $receivedResult = $this->db->query(
            "SELECT COUNT(*) as total
             FROM complaints
             WHERE COALESCE(NULLIF(assigned_unit, ''), referred_to) IS NOT NULL
             AND COALESCE(NULLIF(assigned_unit, ''), referred_to) <> ''"
        )->fetch();
        $stats['received_by_unit_total'] = $receivedResult['total'] ?? 0;

        // Top offices/units involved and received (for dashboard highlights)
        $stats['filed_by_unit_breakdown'] = $this->db->query(
            "SELECT involved_school_office_unit as unit, COUNT(*) as total
             FROM complaints
             WHERE involved_school_office_unit IS NOT NULL
             AND involved_school_office_unit <> ''
             GROUP BY involved_school_office_unit
             ORDER BY total DESC
             LIMIT 5"
        )->fetchAll();

        $stats['received_by_unit_breakdown'] = $this->db->query(
            "SELECT COALESCE(NULLIF(assigned_unit, ''), referred_to) as unit, COUNT(*) as total
             FROM complaints
             WHERE COALESCE(NULLIF(assigned_unit, ''), referred_to) IS NOT NULL
             AND COALESCE(NULLIF(assigned_unit, ''), referred_to) <> ''
             GROUP BY unit
             ORDER BY total DESC
             LIMIT 5"
        )->fetchAll();

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
        $sql = "SELECT c.id, c.reference_number, c.name_pangalan, 
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

    /**
     * Build analytics WHERE clause and params
     */
    private function buildAnalyticsWhere($filters) {
        $where = " WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $where .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['unit'])) {
            $where .= " AND c.assigned_unit = ?";
            $params[] = $filters['unit'];
        }

        if (!empty($filters['department'])) {
            $where .= " AND c.referred_to = ?";
            $params[] = $filters['department'];
        }

        if (!empty($filters['school'])) {
            $where .= " AND c.involved_school_office_unit = ?";
            $params[] = $filters['school'];
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(c.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(c.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        return [$where, $params];
    }

    /**
     * Build SQL CASE expression for complaint type
     */
    private function buildComplaintTypeCase() {
        $keywordConditions = [];
        foreach (COMPLAINT_TYPE_KEYWORDS as $keyword) {
            $keyword = strtolower($keyword);
            $keywordConditions[] = "LOWER(c.referred_to_other) LIKE '%" . $keyword . "%'";
        }
        $keywordSql = implode(' OR ', $keywordConditions);

        $case = "CASE ";
        if (!empty($keywordSql)) {
            $case .= "WHEN c.referred_to = 'Others' AND (" . $keywordSql . ") THEN 'it' ";
        }

        foreach (COMPLAINT_TYPE_MAP as $referredTo => $type) {
            $case .= "WHEN c.referred_to = '" . $referredTo . "' THEN '" . $type . "' ";
        }

        $case .= "ELSE 'admin' END";
        return $case;
    }

    /**
     * Build a keyword frequency list from narration
     */
    private function buildKeywordFrequency($rows, $limit = 10) {
        $stopwords = [
            'the','and','for','with','that','this','have','has','had','not','you','your','yours','are','was','were',
            'from','their','they','them','she','his','her','its','into','out','about','there','here','what','when',
            'where','which','who','whom','will','would','shall','should','can','could','may','might','been','being',
            'also','because','while','upon','after','before','than','then','just','only','other','some','more',
            'complaint','complaints','please','attach','attached','documents','document','certified','true','copies',
            'evidence','affidavits','witnesses','privacy','notice','personal','information','deped','office','school'
        ];
        $stopwords = array_flip($stopwords);
        $counts = [];

        foreach ($rows as $row) {
            $text = strtolower((string)($row['narration_complaint'] ?? ''));
            if ($text === '') {
                continue;
            }
            $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
            $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($words as $word) {
                if (strlen($word) < 3) {
                    continue;
                }
                if (isset($stopwords[$word])) {
                    continue;
                }
                $counts[$word] = ($counts[$word] ?? 0) + 1;
            }
        }

        arsort($counts);
        $result = [];
        foreach (array_slice($counts, 0, $limit, true) as $word => $count) {
            $result[] = ['keyword' => $word, 'count' => $count];
        }

        return $result;
    }

    /**
     * Get analytics payload for dashboard
     */
    public function getAnalytics($filters = []) {
        [$whereSql, $params] = $this->buildAnalyticsWhere($filters);
        $typeCase = $this->buildComplaintTypeCase();
        $unitReceivedExpr = "COALESCE(NULLIF(c.assigned_unit, ''), c.referred_to)";

        $daily = $this->db->query(
            "SELECT DATE(c.created_at) as period, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY DATE(c.created_at)
             ORDER BY period ASC",
            $params
        )->fetchAll();

        $weekly = $this->db->query(
            "SELECT YEARWEEK(c.created_at, 3) as period, MIN(DATE(c.created_at)) as start_date, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY YEARWEEK(c.created_at, 3)
             ORDER BY period ASC",
            $params
        )->fetchAll();

        $monthly = $this->db->query(
            "SELECT DATE_FORMAT(c.created_at, '%Y-%m') as period, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY DATE_FORMAT(c.created_at, '%Y-%m')
             ORDER BY period ASC",
            $params
        )->fetchAll();

        $statusTotals = $this->db->query(
            "SELECT c.status, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY c.status",
            $params
        )->fetchAll();

        $statusAvgTimes = $this->db->query(
            "SELECT ch.status, AVG(TIMESTAMPDIFF(HOUR, c.created_at, ch.status_at)) as avg_hours
             FROM complaints c
             JOIN (
                 SELECT complaint_id, status, MIN(created_at) as status_at
                 FROM complaint_history
                 GROUP BY complaint_id, status
             ) ch ON ch.complaint_id = c.id
             {$whereSql}
             GROUP BY ch.status",
            $params
        )->fetchAll();

        $firstResponse = $this->db->query(
            "SELECT AVG(TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.accepted_at, fr.first_response_at))) as avg_hours
             FROM complaints c
             LEFT JOIN (
                 SELECT complaint_id, MIN(created_at) as first_response_at
                 FROM complaint_history
                 WHERE status IN ('accepted','in_progress','resolved','closed')
                 GROUP BY complaint_id
             ) fr ON fr.complaint_id = c.id
             {$whereSql}
             AND (c.accepted_at IS NOT NULL OR fr.first_response_at IS NOT NULL)",
            $params
        )->fetch();

        $resolutionTime = $this->db->query(
            "SELECT AVG(TIMESTAMPDIFF(HOUR, c.created_at, r.resolved_at)) as avg_hours
             FROM complaints c
             JOIN (
                 SELECT complaint_id, MIN(created_at) as resolved_at
                 FROM complaint_history
                 WHERE status IN ('resolved','closed')
                 GROUP BY complaint_id
             ) r ON r.complaint_id = c.id
             {$whereSql}",
            $params
        )->fetch();

        $targetDays = max(1, intval($filters['target_days'] ?? 14));
        $overdueParams = array_merge($params, [$targetDays]);
        $overdueCount = $this->db->query(
            "SELECT COUNT(*) as total
             FROM complaints c {$whereSql}
             AND c.status NOT IN ('resolved','closed')
             AND DATEDIFF(CURRENT_DATE(), DATE(c.created_at)) > ?",
            $overdueParams
        )->fetch();

        $overdueList = $this->db->query(
            "SELECT c.id, c.reference_number, c.status,
                    DATEDIFF(CURRENT_DATE(), DATE(c.created_at)) as days_open
             FROM complaints c {$whereSql}
             AND c.status NOT IN ('resolved','closed')
             AND DATEDIFF(CURRENT_DATE(), DATE(c.created_at)) > ?
             ORDER BY days_open DESC
             LIMIT 10",
            $overdueParams
        )->fetchAll();

        $typeDistribution = $this->db->query(
            "SELECT {$typeCase} as type, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY type
             ORDER BY total DESC",
            $params
        )->fetchAll();

        $typeTrends = $this->db->query(
            "SELECT DATE_FORMAT(c.created_at, '%Y-%m') as period, {$typeCase} as type, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY period, type
             ORDER BY period ASC",
            $params
        )->fetchAll();

        $filedByUnit = $this->db->query(
            "SELECT c.involved_school_office_unit as unit, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY c.involved_school_office_unit
             ORDER BY total DESC",
            $params
        )->fetchAll();

        $receivedByUnit = $this->db->query(
            "SELECT {$unitReceivedExpr} as unit, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY unit
             ORDER BY total DESC",
            $params
        )->fetchAll();

        $resolutionRateByUnit = $this->db->query(
            "SELECT {$unitReceivedExpr} as unit,
                    COUNT(*) as total,
                    SUM(CASE WHEN c.status IN ('resolved','closed') THEN 1 ELSE 0 END) as resolved_total
             FROM complaints c {$whereSql}
             GROUP BY unit
             ORDER BY total DESC",
            $params
        )->fetchAll();

        $handlingTimeByUnit = $this->db->query(
            "SELECT {$unitReceivedExpr} as unit,
                    AVG(TIMESTAMPDIFF(HOUR, COALESCE(c.accepted_at, c.created_at), r.resolved_at)) as avg_hours
             FROM complaints c
             JOIN (
                 SELECT complaint_id, MIN(created_at) as resolved_at
                 FROM complaint_history
                 WHERE status IN ('resolved','closed')
                 GROUP BY complaint_id
             ) r ON r.complaint_id = c.id
             {$whereSql}
             GROUP BY unit
             ORDER BY avg_hours ASC",
            $params
        )->fetchAll();

        $repeatComplainants = $this->db->query(
            "SELECT c.email_address, c.name_pangalan, COUNT(*) as total
             FROM complaints c {$whereSql}
             AND c.email_address <> ''
             GROUP BY c.email_address, c.name_pangalan
             HAVING COUNT(*) > 1
             ORDER BY total DESC
             LIMIT 10",
            $params
        )->fetchAll();

        $narrations = $this->db->query(
            "SELECT c.narration_complaint FROM complaints c {$whereSql}",
            $params
        )->fetchAll();
        $keywordFrequency = $this->buildKeywordFrequency($narrations, 12);

        $locationCounts = $this->db->query(
            "SELECT c.involved_school_office_unit as location, COUNT(*) as total
             FROM complaints c {$whereSql}
             GROUP BY c.involved_school_office_unit
             ORDER BY total DESC
             LIMIT 12",
            $params
        )->fetchAll();

        return [
            'volume' => [
                'daily' => $daily,
                'weekly' => $weekly,
                'monthly' => $monthly
            ],
            'status' => [
                'totals' => $statusTotals,
                'avg_times' => $statusAvgTimes
            ],
            'response' => [
                'avg_first_response_hours' => $firstResponse['avg_hours'] ?? null,
                'avg_resolution_hours' => $resolutionTime['avg_hours'] ?? null,
                'overdue_count' => $overdueCount['total'] ?? 0,
                'overdue_list' => $overdueList,
                'target_days' => $targetDays
            ],
            'types' => [
                'distribution' => $typeDistribution,
                'trends' => $typeTrends
            ],
            'units' => [
                'filed' => $filedByUnit,
                'received' => $receivedByUnit,
                'resolution' => $resolutionRateByUnit,
                'handling_time' => $handlingTimeByUnit
            ],
            'users' => [
                'repeat' => $repeatComplainants,
                'keywords' => $keywordFrequency
            ],
            'locations' => $locationCounts
        ];
    }
}

