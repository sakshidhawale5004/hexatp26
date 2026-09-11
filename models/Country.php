<?php

require_once __DIR__ . '/ValidationResult.php';

/**
 * Country Model
 * 
 * Represents a country entity with all its properties.
 * Includes validation, serialization, and deserialization methods.
 */
class Country {
    public int $id;
    public string $country_name;
    public string $country_code;
    public ?string $flag_url;
    public ?string $hero_title;
    public ?string $hero_description;
    public ?string $meta_title;
    public ?string $meta_description;
    public ?string $cta_title;
    public ?string $cta_button_text;
    public ?string $footer_title;
    public ?string $footer_email;
    public ?string $hero_bg_image;
    public string $status; // 'draft' or 'published'
    public DateTime $created_at;
    public DateTime $updated_at;
    
    // Relationships (not stored directly in this table)
    public $overview = null;
    public array $regulatory_frameworks = [];
    public array $documentation_cards = [];
    public array $country_services = [];
    
    // Validation constants
    const MAX_HERO_TITLE_LENGTH = 100;
    const MAX_HERO_DESCRIPTION_LENGTH = 500;
    const MAX_META_TITLE_LENGTH = 255;
    const MAX_META_DESCRIPTION_LENGTH = 500;
    const MAX_COUNTRY_NAME_LENGTH = 100;
    const MAX_COUNTRY_CODE_LENGTH = 10;
    const MAX_FLAG_URL_LENGTH = 255;
    
    const VALID_STATUSES = ['draft', 'published'];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->status = 'draft';
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }
    
    /**
     * Validate the Country model
     * 
     * Validates all fields according to requirements:
     * - Required fields: country_name, country_code
     * - Field length limits
     * - Valid status values
     * 
     * @return ValidationResult The validation result
     */
    public function validate(): ValidationResult {
        $result = new ValidationResult();
        
        // Validate required fields
        if (empty($this->country_name)) {
            $result->addError('country_name', 'Country name is required');
        } elseif (strlen($this->country_name) > self::MAX_COUNTRY_NAME_LENGTH) {
            $result->addError(
                'country_name', 
                sprintf('Country name must not exceed %d characters (current: %d)', 
                    self::MAX_COUNTRY_NAME_LENGTH, 
                    strlen($this->country_name)
                )
            );
        }
        
        if (empty($this->country_code)) {
            $result->addError('country_code', 'Country code is required');
        } elseif (strlen($this->country_code) > self::MAX_COUNTRY_CODE_LENGTH) {
            $result->addError(
                'country_code', 
                sprintf('Country code must not exceed %d characters (current: %d)', 
                    self::MAX_COUNTRY_CODE_LENGTH, 
                    strlen($this->country_code)
                )
            );
        }
        
        // Validate optional field lengths
        if (!empty($this->flag_url) && strlen($this->flag_url) > self::MAX_FLAG_URL_LENGTH) {
            $result->addError(
                'flag_url', 
                sprintf('Flag URL must not exceed %d characters (current: %d)', 
                    self::MAX_FLAG_URL_LENGTH, 
                    strlen($this->flag_url)
                )
            );
        }
        
        // Validate hero_title length (Requirement 11.1)
        if (!empty($this->hero_title) && strlen($this->hero_title) > self::MAX_HERO_TITLE_LENGTH) {
            $result->addError(
                'hero_title', 
                sprintf('Title must not exceed %d characters (current: %d)', 
                    self::MAX_HERO_TITLE_LENGTH, 
                    strlen($this->hero_title)
                )
            );
        }
        
        // Validate hero_description length (Requirement 11.2)
        if (!empty($this->hero_description) && strlen($this->hero_description) > self::MAX_HERO_DESCRIPTION_LENGTH) {
            $result->addError(
                'hero_description', 
                sprintf('Description must not exceed %d characters (current: %d)', 
                    self::MAX_HERO_DESCRIPTION_LENGTH, 
                    strlen($this->hero_description)
                )
            );
        }
        
        // Validate meta_title length
        if (!empty($this->meta_title) && strlen($this->meta_title) > self::MAX_META_TITLE_LENGTH) {
            $result->addError(
                'meta_title', 
                sprintf('Meta title must not exceed %d characters (current: %d)', 
                    self::MAX_META_TITLE_LENGTH, 
                    strlen($this->meta_title)
                )
            );
        }
        
        // Validate meta_description length
        if (!empty($this->meta_description) && strlen($this->meta_description) > self::MAX_META_DESCRIPTION_LENGTH) {
            $result->addError(
                'meta_description', 
                sprintf('Meta description must not exceed %d characters (current: %d)', 
                    self::MAX_META_DESCRIPTION_LENGTH, 
                    strlen($this->meta_description)
                )
            );
        }
        
        // Validate status
        if (!in_array($this->status, self::VALID_STATUSES)) {
            $result->addError(
                'status', 
                sprintf('Status must be one of: %s (current: %s)', 
                    implode(', ', self::VALID_STATUSES), 
                    $this->status
                )
            );
        }
        
        return $result;
    }
    
    /**
     * Convert the Country model to an associative array
     * 
     * @return array The country data as an array
     */
    public function toArray(): array {
        $data = [
            'country_name' => $this->country_name,
            'country_code' => $this->country_code,
            'flag_url' => $this->flag_url,
            'hero_title' => $this->hero_title,
            'hero_description' => $this->hero_description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'cta_title' => $this->cta_title,
            'cta_button_text' => $this->cta_button_text,
            'footer_title' => $this->footer_title,
            'footer_email' => $this->footer_email,
            'hero_bg_image' => $this->hero_bg_image,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
        
        // Include id if it's set
        if (isset($this->id)) {
            $data['id'] = $this->id;
        }
        
        // Include relationships if they exist
        if ($this->overview !== null) {
            $data['overview'] = is_object($this->overview) && method_exists($this->overview, 'toArray') 
                ? $this->overview->toArray() 
                : $this->overview;
        }
        
        if (!empty($this->regulatory_frameworks)) {
            $data['regulatory_frameworks'] = array_map(function($framework) {
                return is_object($framework) && method_exists($framework, 'toArray') 
                    ? $framework->toArray() 
                    : $framework;
            }, $this->regulatory_frameworks);
        }
        
        if (!empty($this->documentation_cards)) {
            $data['documentation_cards'] = array_map(function($card) {
                return is_object($card) && method_exists($card, 'toArray') 
                    ? $card->toArray() 
                    : $card;
            }, $this->documentation_cards);
        }

        if (!empty($this->country_services)) {
            $data['country_services'] = array_map(function($service) {
                return is_object($service) && method_exists($service, 'toArray') 
                    ? $service->toArray() 
                    : $service;
            }, $this->country_services);
        }
        
        return $data;
    }
    
    /**
     * Create a Country model from an associative array
     * 
     * @param array $data The country data as an array
     * @return Country The Country model instance
     */
    public static function fromArray(array $data): Country {
        $country = new Country();
        
        // Set id if provided
        if (isset($data['id'])) {
            $country->id = (int)$data['id'];
        }
        
        // Set required fields
        $country->country_name = $data['country_name'] ?? '';
        $country->country_code = $data['country_code'] ?? '';
        
        // Set optional fields
        $country->flag_url = $data['flag_url'] ?? null;
        $country->hero_title = $data['hero_title'] ?? null;
        $country->hero_description = $data['hero_description'] ?? null;
        $country->meta_title = $data['meta_title'] ?? null;
        $country->meta_description = $data['meta_description'] ?? null;
        $country->cta_title = $data['cta_title'] ?? null;
        $country->cta_button_text = $data['cta_button_text'] ?? null;
        $country->footer_title = $data['footer_title'] ?? null;
        $country->footer_email = $data['footer_email'] ?? null;
        $country->hero_bg_image = $data['hero_bg_image'] ?? null;
        $country->status = $data['status'] ?? 'draft';
        
        // Set timestamps
        if (isset($data['created_at'])) {
            $country->created_at = is_string($data['created_at']) 
                ? new DateTime($data['created_at']) 
                : $data['created_at'];
        }
        
        if (isset($data['updated_at'])) {
            $country->updated_at = is_string($data['updated_at']) 
                ? new DateTime($data['updated_at']) 
                : $data['updated_at'];
        }
        
        // Set relationships if provided
        if (isset($data['overview'])) {
            $country->overview = $data['overview'];
        }
        
        if (isset($data['regulatory_frameworks'])) {
            $country->regulatory_frameworks = $data['regulatory_frameworks'];
        }
        
        if (isset($data['documentation_cards'])) {
            $country->documentation_cards = $data['documentation_cards'];
        }

        if (isset($data['country_services'])) {
            $country->country_services = $data['country_services'];
        }
        
        return $country;
    }
}
