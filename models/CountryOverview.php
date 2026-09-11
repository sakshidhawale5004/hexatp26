<?php

require_once __DIR__ . '/ValidationResult.php';

/**
 * CountryOverview Model
 * 
 * Represents the overview section of a country page with left and right text columns.
 * Includes validation, serialization, and deserialization methods.
 */
class CountryOverview {
    public int $id;
    public int $country_id;
    public ?string $overview_text_left;
    public ?string $overview_text_right;
    public DateTime $created_at;
    public DateTime $updated_at;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }
    
    /**
     * Validate the CountryOverview model
     * 
     * Validates all fields according to requirements:
     * - Required field: country_id
     * - At least one overview paragraph required for publication (Requirement 11.3)
     * 
     * @return ValidationResult The validation result
     */
    public function validate(): ValidationResult {
        $result = new ValidationResult();
        
        // Validate required field
        if (empty($this->country_id)) {
            $result->addError('country_id', 'Country ID is required');
        }
        
        // Validate that at least one overview paragraph exists (Requirement 11.3)
        // This is a warning for drafts but would be an error for publication
        if (empty($this->overview_text_left) && empty($this->overview_text_right)) {
            $result->addWarning(
                'overview_text', 
                'At least one overview paragraph (left or right) is required before publication'
            );
        }
        
        return $result;
    }
    
    /**
     * Convert the CountryOverview model to an associative array
     * 
     * @return array The country overview data as an array
     */
    public function toArray(): array {
        $data = [
            'country_id' => $this->country_id,
            'overview_text_left' => $this->overview_text_left,
            'overview_text_right' => $this->overview_text_right,
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
     * Create a CountryOverview model from an associative array
     * 
     * @param array $data The country overview data as an array
     * @return CountryOverview The CountryOverview model instance
     */
    public static function fromArray(array $data): CountryOverview {
        $overview = new CountryOverview();
        
        // Set id if provided
        if (isset($data['id'])) {
            $overview->id = (int)$data['id'];
        }
        
        // Set required field
        $overview->country_id = isset($data['country_id']) ? (int)$data['country_id'] : 0;
        
        // Set optional fields
        $overview->overview_text_left = $data['overview_text_left'] ?? null;
        $overview->overview_text_right = $data['overview_text_right'] ?? null;
        
        // Set timestamps
        if (isset($data['created_at'])) {
            $overview->created_at = is_string($data['created_at']) 
                ? new DateTime($data['created_at']) 
                : $data['created_at'];
        }
        
        if (isset($data['updated_at'])) {
            $overview->updated_at = is_string($data['updated_at']) 
                ? new DateTime($data['updated_at']) 
                : $data['updated_at'];
        }
        
        return $overview;
    }
}
