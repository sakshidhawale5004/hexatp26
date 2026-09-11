<?php

require_once __DIR__ . '/ValidationResult.php';

/**
 * CountryService Model
 * 
 * Represents a service box for a country.
 * Includes validation, serialization, and deserialization methods.
 */
class CountryService {
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
     * Validate the CountryService model
     */
    public function validate(): ValidationResult {
        $result = new ValidationResult();
        
        if (empty($this->country_id)) {
            $result->addError('country_id', 'Country ID is required');
        }
        
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
        
        return $result;
    }
    
    /**
     * Convert the CountryService model to an associative array
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
        
        if (isset($this->id)) {
            $data['id'] = $this->id;
        }
        
        return $data;
    }
    
    /**
     * Create a CountryService model from an associative array
     */
    public static function fromArray(array $data): CountryService {
        $service = new CountryService();
        
        if (isset($data['id'])) {
            $service->id = (int)$data['id'];
        }
        
        $service->country_id = isset($data['country_id']) ? (int)$data['country_id'] : 0;
        $service->title = $data['title'] ?? '';
        $service->description = $data['description'] ?? null;
        $service->display_order = isset($data['display_order']) ? (int)$data['display_order'] : 0;
        
        if (isset($data['created_at'])) {
            $service->created_at = is_string($data['created_at']) ? new DateTime($data['created_at']) : $data['created_at'];
        }
        
        if (isset($data['updated_at'])) {
            $service->updated_at = is_string($data['updated_at']) ? new DateTime($data['updated_at']) : $data['updated_at'];
        }
        
        return $service;
    }
}
