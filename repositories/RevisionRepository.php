<?php

require_once __DIR__ . '/../models/ContentRevision.php';

/**
 * RevisionRepository
 * 
 * Data access layer for ContentRevision entities.
 * Implements CRUD operations using prepared statements for SQL injection prevention.
 * Provides revision history tracking and retrieval functionality.
 * 
 * Requirements: 7.1, 7.2, 7.9
 */
class RevisionRepository {
    private mysqli $conn;
    
    /**
     * Constructor
     * 
     * @param mysqli $connection Database connection
     */
    public function __construct(mysqli $connection) {
        $this->conn = $connection;
    }
    
    /**
     * Create a new content revision record
     * 
     * @param ContentRevision $revision The revision to create
     * @return int|false The ID of the created revision, or false on failure
     */
    public function create(ContentRevision $revision): int|false {
        $sql = "INSERT INTO content_revisions (
            country_id, content_type, content_id, field_name,
            old_value, new_value, changed_by, changed_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $changed_at = $revision->changed_at->format('Y-m-d H:i:s');
        
        $stmt->bind_param(
            'isisssis',
            $revision->country_id,
            $revision->content_type,
            $revision->content_id,
            $revision->field_name,
            $revision->old_value,
            $revision->new_value,
            $revision->changed_by,
            $changed_at
        );
        
        $result = $stmt->execute();
        if (!$result) {
            $stmt->close();
            return false;
        }
        
        $insert_id = $this->conn->insert_id;
        $stmt->close();
        
        return $insert_id;
    }
    
    /**
     * Find a revision by ID
     * 
     * @param int $id The revision ID
     * @return ContentRevision|null The revision object, or null if not found
     */
    public function findById(int $id): ?ContentRevision {
        $sql = "SELECT * FROM content_revisions WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row) {
            return null;
        }
        
        return ContentRevision::fromArray($row);
    }
    
    /**
     * Get revision history for a specific country
     * 
     * @param int $country_id The country ID
     * @param int $limit Maximum number of revisions to return (default: 50)
     * @param int $offset Pagination offset (default: 0)
     * @return array Array of ContentRevision objects
     */
    public function getRevisionHistory(int $country_id, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT * FROM content_revisions 
                WHERE country_id = ? 
                ORDER BY changed_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param('iii', $country_id, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $revisions = [];
        while ($row = $result->fetch_assoc()) {
            $revisions[] = ContentRevision::fromArray($row);
        }
        
        $stmt->close();
        
        return $revisions;
    }
    
    /**
     * Get revision history for a specific content item
     * 
     * @param string $content_type The content type (country, overview, regulatory_framework, documentation_card)
     * @param int $content_id The content item ID
     * @param int $limit Maximum number of revisions to return (default: 50)
     * @param int $offset Pagination offset (default: 0)
     * @return array Array of ContentRevision objects
     */
    public function getContentRevisions(string $content_type, int $content_id, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT * FROM content_revisions 
                WHERE content_type = ? AND content_id = ? 
                ORDER BY changed_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param('siii', $content_type, $content_id, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $revisions = [];
        while ($row = $result->fetch_assoc()) {
            $revisions[] = ContentRevision::fromArray($row);
        }
        
        $stmt->close();
        
        return $revisions;
    }
    
    /**
     * Get revision history for a specific field
     * 
     * @param string $content_type The content type
     * @param int $content_id The content item ID
     * @param string $field_name The field name
     * @param int $limit Maximum number of revisions to return (default: 50)
     * @param int $offset Pagination offset (default: 0)
     * @return array Array of ContentRevision objects
     */
    public function getFieldRevisions(string $content_type, int $content_id, string $field_name, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT * FROM content_revisions 
                WHERE content_type = ? AND content_id = ? AND field_name = ? 
                ORDER BY changed_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param('sisii', $content_type, $content_id, $field_name, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $revisions = [];
        while ($row = $result->fetch_assoc()) {
            $revisions[] = ContentRevision::fromArray($row);
        }
        
        $stmt->close();
        
        return $revisions;
    }
    
    /**
     * Get revisions by user
     * 
     * @param int $user_id The user ID
     * @param int $limit Maximum number of revisions to return (default: 50)
     * @param int $offset Pagination offset (default: 0)
     * @return array Array of ContentRevision objects
     */
    public function getRevisionsByUser(int $user_id, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT * FROM content_revisions 
                WHERE changed_by = ? 
                ORDER BY changed_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $revisions = [];
        while ($row = $result->fetch_assoc()) {
            $revisions[] = ContentRevision::fromArray($row);
        }
        
        $stmt->close();
        
        return $revisions;
    }
    
    /**
     * Get revisions within a date range
     * 
     * @param int $country_id The country ID
     * @param DateTime $start_date Start date
     * @param DateTime $end_date End date
     * @param int $limit Maximum number of revisions to return (default: 50)
     * @param int $offset Pagination offset (default: 0)
     * @return array Array of ContentRevision objects
     */
    public function getRevisionsByDateRange(int $country_id, DateTime $start_date, DateTime $end_date, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT * FROM content_revisions 
                WHERE country_id = ? AND changed_at BETWEEN ? AND ? 
                ORDER BY changed_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $start = $start_date->format('Y-m-d H:i:s');
        $end = $end_date->format('Y-m-d H:i:s');
        
        $stmt->bind_param('issii', $country_id, $start, $end, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $revisions = [];
        while ($row = $result->fetch_assoc()) {
            $revisions[] = ContentRevision::fromArray($row);
        }
        
        $stmt->close();
        
        return $revisions;
    }
    
    /**
     * Get count of revisions for a country
     * 
     * @param int $country_id The country ID
     * @return int The count of revisions
     */
    public function getRevisionCount(int $country_id): int {
        $sql = "SELECT COUNT(*) as count FROM content_revisions WHERE country_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        
        $stmt->bind_param('i', $country_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['count'];
    }
    
    /**
     * Get count of revisions for a specific content item
     * 
     * @param string $content_type The content type
     * @param int $content_id The content item ID
     * @return int The count of revisions
     */
    public function getContentRevisionCount(string $content_type, int $content_id): int {
        $sql = "SELECT COUNT(*) as count FROM content_revisions 
                WHERE content_type = ? AND content_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        
        $stmt->bind_param('si', $content_type, $content_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['count'];
    }
    
    /**
     * Delete old revisions (for cleanup/archival)
     * 
     * @param DateTime $before_date Delete revisions older than this date
     * @return int Number of revisions deleted
     */
    public function deleteOldRevisions(DateTime $before_date): int {
        $sql = "DELETE FROM content_revisions WHERE changed_at < ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        
        $date = $before_date->format('Y-m-d H:i:s');
        $stmt->bind_param('s', $date);
        $stmt->execute();
        
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        return $affected_rows;
    }
    
    /**
     * Get the most recent revision for a specific content item
     * 
     * @param string $content_type The content type
     * @param int $content_id The content item ID
     * @return ContentRevision|null The most recent revision, or null if none found
     */
    public function getLatestRevision(string $content_type, int $content_id): ?ContentRevision {
        $sql = "SELECT * FROM content_revisions 
                WHERE content_type = ? AND content_id = ? 
                ORDER BY changed_at DESC 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param('si', $content_type, $content_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row) {
            return null;
        }
        
        return ContentRevision::fromArray($row);
    }
    
    /**
     * Get revisions with user information (JOIN with users table)
     * 
     * @param int $country_id The country ID
     * @param int $limit Maximum number of revisions to return (default: 50)
     * @param int $offset Pagination offset (default: 0)
     * @return array Array of associative arrays with revision and user data
     */
    public function getRevisionsWithUserInfo(int $country_id, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT cr.*, u.username, u.email, u.role 
                FROM content_revisions cr 
                INNER JOIN users u ON cr.changed_by = u.id 
                WHERE cr.country_id = ? 
                ORDER BY cr.changed_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param('iii', $country_id, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $revisions = [];
        while ($row = $result->fetch_assoc()) {
            $revisions[] = $row;
        }
        
        $stmt->close();
        
        return $revisions;
    }
}
