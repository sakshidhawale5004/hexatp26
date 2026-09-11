<?php

/**
 * ValidationResult
 * 
 * Represents the result of a validation operation.
 * Contains validation status, errors, and warnings.
 */
class ValidationResult {
    public bool $is_valid;
    public array $errors;   // ['field_name' => 'error message']
    public array $warnings; // ['field_name' => 'warning message']
    
    public function __construct() {
        $this->is_valid = true;
        $this->errors = [];
        $this->warnings = [];
    }
    
    /**
     * Add an error for a specific field
     * 
     * @param string $field The field name
     * @param string $message The error message
     */
    public function addError(string $field, string $message): void {
        $this->errors[$field] = $message;
        $this->is_valid = false;
    }
    
    /**
     * Add a warning for a specific field
     * 
     * @param string $field The field name
     * @param string $message The warning message
     */
    public function addWarning(string $field, string $message): void {
        $this->warnings[$field] = $message;
    }
    
    /**
     * Check if there are any errors
     * 
     * @return bool True if there are errors, false otherwise
     */
    public function hasErrors(): bool {
        return !$this->is_valid;
    }
    
    /**
     * Get all error messages as an array
     * 
     * @return array Array of error messages
     */
    public function getErrorMessages(): array {
        return array_values($this->errors);
    }
}
