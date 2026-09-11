<?php

require_once __DIR__ . '/ValidationResult.php';

/**
 * DocumentationCard Model
 * 
 * Represents a documentation card entry for a country.
 * Includes validation, serialization, and deserialization methods.
 */
class DocumentationCard {
    public int $id;
    public int $country_id;
    public string $title;
    public ?string $short_description;
    public ?string $detailed_content;
    public int $display_order;
    public DateTime $created_at;
    public DateTime $updated_at;
    
    // Validation constants
    const MAX_TITLE_LENGTH = 150; // Requirement 11.5
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->display_order = 0;
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }
    
    /**
     * Validate the DocumentationCard model
     * 
     * Validates all fields according to requirements:
     * - Required fields: country_id, title
     * - Title length limit: 150 characters (Requirement 11.5)
     * - Content validation
     * 
     * @return ValidationResult The validation result
     */
    public function validate(): ValidationResult {
        $result = new ValidationResult();
        
        // Validate required field: country_id
        if (empty($this->country_id)) {
            $result->addError('country_id', 'Country ID is required');
        }
        
        // Validate required field: title (Requirement 11.5)
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
        
        // Validate content (Requirement 11.5)
        if (empty($this->short_description) && empty($this->detailed_content)) {
            $result->addWarning(
                'content', 
                'At least one of short_description or detailed_content is recommended'
            );
        }
        
        return $result;
    }
    
    /**
     * Convert the DocumentationCard model to an associative array
     * 
     * @return array The documentation card data as an array
     */
    public function toArray(): array {
        $data = [
            'country_id' => $this->country_id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'detailed_content' => $this->detailed_content,
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
     * Create a DocumentationCard model from an associative array
     * 
     * @param array $data The documentation card data as an array
     * @return DocumentationCard The DocumentationCard model instance
     */
    public static function fromArray(array $data): DocumentationCard {
        $card = new DocumentationCard();
        
        // Set id if provided
        if (isset($data['id'])) {
            $card->id = (int)$data['id'];
        }
        
        // Set required fields
        $card->country_id = isset($data['country_id']) ? (int)$data['country_id'] : 0;
        $card->title = $data['title'] ?? '';
        
        // Set optional fields
        $card->short_description = $data['short_description'] ?? null;
        $card->detailed_content = $data['detailed_content'] ?? null;
        $card->display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;
        
        // Set timestamps
        if (isset($data['created_at'])) {
            $card->created_at = is_string($data['created_at']) 
                ? new DateTime($data['created_at']) 
                : $data['created_at'];
        }
        
        if (isset($data['updated_at'])) {
            $card->updated_at = is_string($data['updated_at']) 
                ? new DateTime($data['updated_at']) 
                : $data['updated_at'];
        }
        
        return $card;
    }
}
