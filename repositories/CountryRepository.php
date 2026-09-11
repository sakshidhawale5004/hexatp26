<?php

require_once __DIR__ . '/../models/Country.php';
require_once __DIR__ . '/../models/CountryOverview.php';
require_once __DIR__ . '/../models/RegulatoryFramework.php';
require_once __DIR__ . '/../models/DocumentationCard.php';
require_once __DIR__ . '/../models/CountryService.php';

/**
 * CountryRepository
 * 
 * Data access layer for Country entities and related content.
 */
class CountryRepository {
    private mysqli $conn;
    
    public function __construct(mysqli $connection) {
        $this->conn = $connection;
    }

    public function getConnection(): mysqli {
        return $this->conn;
    }
    
    /**
     * Transaction Handling
     */
    public function beginTransaction(): bool {
        return $this->conn->begin_transaction();
    }
    
    public function commit(): bool {
        return $this->conn->commit();
    }
    
    public function rollback(): bool {
        return $this->conn->rollback();
    }
    
    public function create(Country $country): int|false {
        $sql = "INSERT INTO countries (
            country_name, country_code, flag_url, hero_title, hero_description, hero_bg_image,
            meta_title, meta_description, cta_title, cta_button_text,
            footer_title, footer_email, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param(
            'sssssssssssss',
            $country->country_name,
            $country->country_code,
            $country->flag_url,
            $country->hero_title,
            $country->hero_description,
            $country->hero_bg_image,
            $country->meta_title,
            $country->meta_description,
            $country->cta_title,
            $country->cta_button_text,
            $country->footer_title,
            $country->footer_email,
            $country->status
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
    
    public function findById(int $id): ?Country {
        $sql = "SELECT * FROM countries WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if (!$row) return null;
        return Country::fromArray($row);
    }
    
    public function findAll(array $filters = []): array {
        $sql = "SELECT * FROM countries";
        $conditions = [];
        $params = [];
        $types = '';
        
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sort = $filters['sort'] ?? 'country_name';
        $order = $filters['order'] ?? 'ASC';
        $allowed_sort_fields = ['country_name', 'country_code', 'created_at', 'updated_at', 'status'];
        if (!in_array($sort, $allowed_sort_fields)) $sort = 'country_name';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY {$sort} {$order}";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $countries = [];
        while ($row = $result->fetch_assoc()) {
            $countries[] = Country::fromArray($row);
        }
        $stmt->close();
        return $countries;
    }
    
    public function update(int $id, Country $country): bool {
        // Explicitly use NOW() for updated_at to ensure it always refreshes even if PHP clock is off
        $sql = "UPDATE countries SET
            country_name = ?, country_code = ?, flag_url = ?, hero_title = ?,
            hero_description = ?, meta_title = ?, meta_description = ?,
            cta_title = ?, cta_button_text = ?, footer_title = ?, footer_email = ?,
            status = ?, updated_at = NOW()
        WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param(
            'ssssssssssssi',
            $country->country_name,
            $country->country_code,
            $country->flag_url,
            $country->hero_title,
            $country->hero_description,
            $country->meta_title,
            $country->meta_description,
            $country->cta_title,
            $country->cta_button_text,
            $country->footer_title,
            $country->footer_email,
            $country->status,
            $id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function delete(int $id): bool {
        $sql = "DELETE FROM countries WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Check if a country with the given name already exists,
     * optionally excluding a specific ID (used during updates).
     */
    public function countryNameExists(string $name, ?int $exclude_id = null): bool {
        if ($exclude_id !== null) {
            $sql  = "SELECT COUNT(*) as cnt FROM countries WHERE country_name = ? AND id != ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('si', $name, $exclude_id);
        } else {
            $sql  = "SELECT COUNT(*) as cnt FROM countries WHERE country_name = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('s', $name);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['cnt'] > 0;
    }

    /**
     * Check if a country with the given ID exists.
     */
    public function exists(int $id): bool {
        $sql  = "SELECT COUNT(*) as cnt FROM countries WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['cnt'] > 0;
    }
    
    public function getCountryWithRelations(int $id): ?Country {
        $country = $this->findById($id);
        if (!$country) return null;
        
        $country->overview = $this->getOverview($id);
        $country->regulatory_frameworks = $this->getRegulatoryFrameworks($id);
        $country->documentation_cards = $this->getDocumentationCards($id);
        $country->country_services = $this->getCountryServices($id);
        
        return $country;
    }
    
    public function getOverview(int $country_id): ?CountryOverview {
        $sql = "SELECT * FROM country_overview WHERE country_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('i', $country_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? CountryOverview::fromArray($row) : null;
    }
    
    public function getRegulatoryFrameworks(int $country_id): array {
        $sql = "SELECT * FROM regulatory_frameworks WHERE country_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('i', $country_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = RegulatoryFramework::fromArray($row);
        }
        $stmt->close();
        return $items;
    }
    
    public function getDocumentationCards(int $country_id): array {
        $sql = "SELECT * FROM documentation_cards WHERE country_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('i', $country_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = DocumentationCard::fromArray($row);
        }
        $stmt->close();
        return $items;
    }

    public function saveOverview(int $country_id, ?string $left, ?string $right): bool {
        $exists = $this->getOverview($country_id);
        if ($exists) {
            $sql = "UPDATE country_overview SET overview_text_left = ?, overview_text_right = ?, updated_at = NOW() WHERE country_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssi', $left, $right, $country_id);
        } else {
            $sql = "INSERT INTO country_overview (country_id, overview_text_left, overview_text_right, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('iss', $country_id, $left, $right);
        }
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function saveRegulatoryFrameworks(int $country_id, array $items): bool {
        $this->conn->query("DELETE FROM regulatory_frameworks WHERE country_id = $country_id");
        if (empty($items)) return true;
        $sql = "INSERT INTO regulatory_frameworks (country_id, title, description, display_order, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        foreach ($items as $item) {
            if (empty($item['title'])) continue;
            $stmt->bind_param('issi', $country_id, $item['title'], $item['description'], $item['display_order']);
            $stmt->execute();
        }
        $stmt->close();
        return true;
    }

    public function saveDocumentationCards(int $country_id, array $items): bool {
        $this->conn->query("DELETE FROM documentation_cards WHERE country_id = $country_id");
        if (empty($items)) return true;
        $sql = "INSERT INTO documentation_cards (country_id, title, short_description, detailed_content, display_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        foreach ($items as $item) {
            if (empty($item['title'])) continue;
            $stmt->bind_param('isssi', $country_id, $item['title'], $item['short_description'], $item['detailed_content'], $item['display_order']);
            $stmt->execute();
        }
        $stmt->close();
        return true;
    }

    public function getCountryServices(int $country_id): array {
        $sql = "SELECT * FROM country_services WHERE country_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('i', $country_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = CountryService::fromArray($row);
        }
        $stmt->close();
        return $items;
    }

    public function saveCountryServices(int $country_id, array $items): bool {
        $this->conn->query("DELETE FROM country_services WHERE country_id = $country_id");
        if (empty($items)) return true;
        $sql = "INSERT INTO country_services (country_id, title, description, display_order, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        foreach ($items as $item) {
            if (empty($item['title'])) continue;
            $stmt->bind_param('issi', $country_id, $item['title'], $item['description'], $item['display_order']);
            $stmt->execute();
        }
        $stmt->close();
        return true;
    }

    public function getCount(?string $status = null): int {
        $sql = "SELECT COUNT(*) as count FROM countries";
        if ($status) $sql .= " WHERE status = '$status'";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }
}