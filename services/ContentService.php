<?php

require_once __DIR__ . '/../repositories/CountryRepository.php';
require_once __DIR__ . '/../repositories/RevisionRepository.php';
require_once __DIR__ . '/../models/Country.php';
require_once __DIR__ . '/../models/ValidationResult.php';
require_once __DIR__ . '/HTMLParserService.php';

/**
 * ContentService
 * 
 * Business logic layer for country content management.
 * Handles CRUD operations, validation, and content sanitization.
 * 
 * Requirements: 2.2, 2.3, 3.1, 3.2, 3.3, 3.4, 3.5, 8.6
 */
class ContentService {
    private CountryRepository $countryRepo;
    private RevisionRepository $revisionRepo;
    private HTMLParserService $htmlParser;
    private mysqli $conn;
    
    /**
     * Constructor
     * 
     * @param mysqli $connection Database connection
     */
    public function __construct(mysqli $connection) {
        $this->conn = $connection;
        $this->countryRepo = new CountryRepository($connection);
        $this->revisionRepo = new RevisionRepository($connection);
        $this->htmlParser = new HTMLParserService();
    }
    
    /**
     * Get a country by ID with all related content
     * 
     * @param int $id The country ID
     * @return Country|null The country with all relations, or null if not found
     */
    public function getCountry(int $id): ?Country {
        return $this->countryRepo->getCountryWithRelations($id);
    }
    
    /**
     * Get all countries with optional filtering
     * 
     * @param array $filters Optional filters (status, sort, order)
     * @return array Array of Country objects
     */
    public function getAllCountries(array $filters = []): array {
        return $this->countryRepo->findAll($filters);
    }
    
    /**
     * Create a new country
     * 
     * Validates the country data and sanitizes HTML content before saving.
     * 
     * @param Country $country The country to create
     * @param int $user_id The ID of the user creating the country
     * @param array $additional_data Optional additional data (overview, frameworks, cards)
     * @return array Result array with 'success', 'id', and optional 'errors'
     */
    public function createCountry(Country $country, int $user_id, array $additional_data = []): array {
        // Validate country data
        $validation = $country->validate();
        if (!$validation->is_valid) {
            return [
                'success' => false,
                'errors' => $validation->errors
            ];
        }
        
        // Set initial timestamps
        $country->created_at = new DateTime();
        $country->updated_at = new DateTime();
        
        // Sanitize HTML content
        if (!empty($country->hero_description)) {
            $country->hero_description = $this->htmlParser->sanitize($country->hero_description);
        }
        
        // Check for duplicate country name
        if ($this->countryRepo->countryNameExists($country->country_name)) {
            return [
                'success' => false,
                'errors' => ['country_name' => 'A country with this name already exists']
            ];
        }
        
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Create the country
            $country_id = $this->countryRepo->create($country);
            
            if ($country_id === false) {
                throw new Exception('Failed to create country');
            }
            
            // Save overview if provided
            if (isset($additional_data['overview_text_left']) || isset($additional_data['overview_text_right'])) {
                $overview_left = isset($additional_data['overview_text_left']) ? $this->htmlParser->sanitize($additional_data['overview_text_left']) : null;
                $overview_right = isset($additional_data['overview_text_right']) ? $this->htmlParser->sanitize($additional_data['overview_text_right']) : null;
                
                $this->countryRepo->saveOverview(
                    $country_id,
                    $overview_left,
                    $overview_right
                );
            }
            
            // Save regulatory frameworks if provided
            if (isset($additional_data['frameworks']) && is_array($additional_data['frameworks'])) {
                $frameworks = $additional_data['frameworks'];
                foreach ($frameworks as &$fw) {
                    if (isset($fw['description'])) {
                        $fw['description'] = $this->htmlParser->sanitize($fw['description']);
                    }
                }
                $this->countryRepo->saveRegulatoryFrameworks($country_id, $frameworks);
            }
            
            // Save documentation cards if provided
            if (isset($additional_data['cards']) && is_array($additional_data['cards'])) {
                $cards = $additional_data['cards'];
                foreach ($cards as &$card) {
                    if (isset($card['detailed_content'])) {
                        $card['detailed_content'] = $this->htmlParser->sanitize($card['detailed_content']);
                    }
                }
                $this->countryRepo->saveDocumentationCards($country_id, $cards);
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'id' => $country_id
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            
            return [
                'success' => false,
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }
    
    /**
     * Update an existing country
     * 
     * Validates the country data, sanitizes HTML content, and creates revision records.
     * 
     * @param int $id The country ID
     * @param Country $country The updated country data
     * @param int $user_id The ID of the user updating the country
     * @param array $additional_data Optional additional data (overview, frameworks, cards)
     * @return array Result array with 'success' and optional 'errors'
     */
    public function updateCountry(int $id, Country $country, int $user_id, array $additional_data = []): array {
        // Check if country exists
        $existing = $this->countryRepo->findById($id);
        if (!$existing) {
            return [
                'success' => false,
                'errors' => ['country' => 'Country not found']
            ];
        }
        
        // Validate country data
        $validation = $country->validate();
        if (!$validation->is_valid) {
            return [
                'success' => false,
                'errors' => $validation->errors
            ];
        }
        
        // Sanitize HTML content
        if (!empty($country->hero_description)) {
            $country->hero_description = $this->htmlParser->sanitize($country->hero_description);
        }
        
        // Check for duplicate country name (excluding current country)
        if ($this->countryRepo->countryNameExists($country->country_name, $id)) {
            return [
                'success' => false,
                'errors' => ['country_name' => 'A country with this name already exists']
            ];
        }
        
        // Update timestamp
        $country->updated_at = new DateTime();
        
        // Start transaction for atomic update with revision tracking
        $this->conn->begin_transaction();
        
        try {
            // Create revision records for changed fields
            $this->createRevisions($id, $existing, $country, $user_id);
            
            // Update the country
            $result = $this->countryRepo->update($id, $country);
            
            if (!$result) {
                throw new Exception('Failed to update country');
            }
            
            // Save overview if provided
            if (isset($additional_data['overview_text_left']) || isset($additional_data['overview_text_right'])) {
                $overview_left = isset($additional_data['overview_text_left']) ? $this->htmlParser->sanitize($additional_data['overview_text_left']) : null;
                $overview_right = isset($additional_data['overview_text_right']) ? $this->htmlParser->sanitize($additional_data['overview_text_right']) : null;
                
                $this->countryRepo->saveOverview(
                    $id,
                    $overview_left,
                    $overview_right
                );
            }
            
            // Save regulatory frameworks if provided
            if (isset($additional_data['frameworks']) && is_array($additional_data['frameworks'])) {
                $frameworks = $additional_data['frameworks'];
                foreach ($frameworks as &$fw) {
                    if (isset($fw['description'])) {
                        $fw['description'] = $this->htmlParser->sanitize($fw['description']);
                    }
                }
                $this->countryRepo->saveRegulatoryFrameworks($id, $frameworks);
            }
            
            // Save documentation cards if provided
            if (isset($additional_data['cards']) && is_array($additional_data['cards'])) {
                $cards = $additional_data['cards'];
                foreach ($cards as &$card) {
                    if (isset($card['short_description'])) {
                        $card['short_description'] = $this->htmlParser->sanitize($card['short_description']);
                    }
                    if (isset($card['detailed_content'])) {
                        $card['detailed_content'] = $this->htmlParser->sanitize($card['detailed_content']);
                    }
                }
                $this->countryRepo->saveDocumentationCards($id, $cards);
            }
            
            $this->conn->commit();
            
            return [
                'success' => true
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            
            return [
                'success' => false,
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }
    
    /**
     * Delete a country and all associated content
     * 
     * @param int $id The country ID
     * @return array Result array with 'success' and optional 'errors'
     */
    public function deleteCountry(int $id): array {
        // Check if country exists
        if (!$this->countryRepo->exists($id)) {
            return [
                'success' => false,
                'errors' => ['country' => 'Country not found']
            ];
        }
        
        // Delete the country (cascade will handle related records)
        $result = $this->countryRepo->delete($id);
        
        if (!$result) {
            return [
                'success' => false,
                'errors' => ['database' => 'Failed to delete country']
            ];
        }
        
        return [
            'success' => true
        ];
    }
    
    /**
     * Publish a country (change status from draft to published)
     * 
     * @param int $id The country ID
     * @param int $user_id The ID of the user publishing the country
     * @return array Result array with 'success' and optional 'errors'
     */
    public function publishCountry(int $id, int $user_id): array {
        // Get the country
        $country = $this->countryRepo->findById($id);
        if (!$country) {
            return [
                'success' => false,
                'errors' => ['country' => 'Country not found']
            ];
        }
        
        // Check if already published
        if ($country->status === 'published') {
            return [
                'success' => true,
                'message' => 'Country is already published'
            ];
        }
        
        // Validate that country has required content for publication
        $validation = $this->validateForPublication($country);
        if (!$validation->is_valid) {
            return [
                'success' => false,
                'errors' => $validation->errors
            ];
        }
        
        // Update status to published
        $country->status = 'published';
        $country->updated_at = new DateTime();
        
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Create revision record for status change
            $revision = new ContentRevision();
            $revision->country_id = $id;
            $revision->content_type = 'country';
            $revision->content_id = $id;
            $revision->field_name = 'status';
            $revision->old_value = 'draft';
            $revision->new_value = 'published';
            $revision->changed_by = $user_id;
            
            $this->revisionRepo->create($revision);
            
            // Update the country
            $result = $this->countryRepo->update($id, $country);
            
            if (!$result) {
                throw new Exception('Failed to publish country');
            }
            
            $this->conn->commit();
            
            return [
                'success' => true
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            
            return [
                'success' => false,
                'errors' => ['database' => $e->getMessage()]
            ];
        }
    }
    
    /**
     * Duplicate a country to create a template for a new country
     * 
     * @param int $id The country ID to duplicate
     * @param string $new_name The name for the new country
     * @param string $new_code The code for the new country
     * @param int $user_id The ID of the user creating the duplicate
     * @return array Result array with 'success', 'id', and optional 'errors'
     */
    public function duplicateCountry(int $id, string $new_name, string $new_code, int $user_id): array {
        // Get the source country with all relations
        $source = $this->countryRepo->getCountryWithRelations($id);
        if (!$source) {
            return [
                'success' => false,
                'errors' => ['country' => 'Source country not found']
            ];
        }
        
        // Create new country object
        $new_country = new Country();
        $new_country->country_name = $new_name;
        $new_country->country_code = $new_code;
        $new_country->flag_url = $source->flag_url;
        $new_country->hero_title = $source->hero_title;
        $new_country->hero_description = $source->hero_description;
        $new_country->meta_title = $source->meta_title;
        $new_country->meta_description = $source->meta_description;
        $new_country->status = 'draft'; // Always create as draft
        
        // Create the new country
        return $this->createCountry($new_country, $user_id);
    }
    
    /**
     * Get country statistics
     * 
     * @return array Statistics array with counts
     */
    public function getStatistics(): array {
        return [
            'total' => $this->countryRepo->getCount(),
            'published' => $this->countryRepo->getCount('published'),
            'draft' => $this->countryRepo->getCount('draft')
        ];
    }
    
    /**
     * Create revision records for changed fields
     * 
     * @param int $country_id The country ID
     * @param Country $old The old country data
     * @param Country $new The new country data
     * @param int $user_id The ID of the user making the change
     * @return void
     */
    private function createRevisions(int $country_id, Country $old, Country $new, int $user_id): void {
        $fields = [
            'country_name', 'country_code', 'flag_url', 'hero_title', 
            'hero_description', 'meta_title', 'meta_description', 'status'
        ];
        
        foreach ($fields as $field) {
            if ($old->$field !== $new->$field) {
                $revision = new ContentRevision();
                $revision->country_id = $country_id;
                $revision->content_type = 'country';
                $revision->content_id = $country_id;
                $revision->field_name = $field;
                $revision->old_value = $old->$field;
                $revision->new_value = $new->$field;
                $revision->changed_by = $user_id;
                
                $this->revisionRepo->create($revision);
            }
        }
    }
    
    /**
     * Validate that a country has all required content for publication
     * 
     * @param Country $country The country to validate
     * @return ValidationResult The validation result
     */
    private function validateForPublication(Country $country): ValidationResult {
        $result = new ValidationResult();
        
        // Basic validation
        $basic_validation = $country->validate();
        if (!$basic_validation->is_valid) {
            foreach ($basic_validation->errors as $field => $message) {
                $result->addError($field, $message);
            }
        }
        
        // Additional publication requirements
        if (empty($country->hero_title)) {
            $result->addError('hero_title', 'Hero title is required for publication');
        }
        
        if (empty($country->hero_description)) {
            $result->addError('hero_description', 'Hero description is required for publication');
        }
        
        return $result;
    }
}
