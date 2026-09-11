<?php

require_once __DIR__ . '/ValidationResult.php';

/**
 * RegulatoryFramework Model
 * 
 * Represents a regulatory framework entry for a country.
 * Includes validation, serialization, and deserialization methods.
 */
class RegulatoryFramework {
    public int $id;
    public int $country_id;
    public string $title;
    public ?string $description;
    public int $display_order;
    public DateTime $created_at;
    public DateTime $updated_at;
    
    // Validation constants
    const MAX_TITLE_LENGTH = 255;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->display_order = 0;
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }
    
    /**
     * Validate the RegulatoryFramework model
     * 
     * Validates all fields according to requirements:
     * - Required fields: country_id, title
     * - Field length limits
     * 
     * @return ValidationResult The validation result
     */
    public function validate(): ValidationResult {
        $result = new ValidationResult();
        
        // Validate required field: country_id
        if (empty($this->country_id)) {
            $result->addError('country_id', 'Country ID is required');
        }
        
        // Validate required field: title (Requirement 11.4)
        if (empty($this->title)) {
            $result->addError('title', 'Title is required');
        } elseif (strlen($this->title) > self::MAX_TITLE_LENGTH) {
            $result->addError(
                'title', 
                sprintf('Title must not exceed %d characters (current: %d)', 
                    self::MAX_TITLE_LENGTH, 
                    strlen($this->title)
                )
            );
        }
        
        // Validate description (Requirement 11.4)
        if (empty($this->description)) {
            $result->addWarning('description', 'Description is recommended for better content quality');
        }
        
        return $result;
    }
    
    /**
     * Convert the RegulatoryFramework model to an associative array
     * 
     * @return array The regulatory framework data as an array
     */
    public function toArray(): array {
        $data = [
            'country_id' => $this->country_id,
            'title' => $this->title,
            'description' => $this->description,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
        
        // Include id if it's set
        if (isset($this->id)) {
            $data['id'] = $this->id;
        }
        
        return $data;
    }
    
    /**
     * Create a RegulatoryFramework model from an associative array
     * 
     * @param array $data The regulatory framework data as an array
     * @return RegulatoryFramework The RegulatoryFramework model instance
     */
    public static function fromArray(array $data): RegulatoryFramework {
        $framework = new RegulatoryFramework();
        
        // Set id if provided
        if (isset($data['id'])) {
            $framework->id = (int)$data['id'];
        }
        
        // Set required fields
        $framework->country_id = isset($data['country_id']) ? (int)$data['country_id'] : 0;
        $framework->title = $data['title'] ?? '';
        
        // Set optional fields
        $framework->description = $data['description'] ?? null;
        $framework->display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;
        
        // Set timestamps
        if (isset($data['created_at'])) {
            $framework->created_at = is_string($data['created_at']) 
                ? new DateTime($data['created_at']) 
                : $data['created_at'];
        }
        
        if (isset($data['updated_at'])) {
            $framework->updated_at = is_string($data['updated_at']) 
                ? new DateTime($data['updated_at']) 
                : $data['updated_at'];
        }
        
        return $framework;
    }
}
