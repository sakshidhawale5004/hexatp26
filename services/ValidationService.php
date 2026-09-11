<?php

require_once __DIR__ . '/../models/Country.php';
require_once __DIR__ . '/../models/CountryOverview.php';
require_once __DIR__ . '/../models/RegulatoryFramework.php';
require_once __DIR__ . '/../models/DocumentationCard.php';
require_once __DIR__ . '/../models/ValidationResult.php';
require_once __DIR__ . '/HTMLParserService.php';

/**
 * ValidationService
 * 
 * Centralized validation service for all content types.
 * Provides validation methods for models and HTML content.
 * 
 * Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6, 11.7, 11.8, 11.9, 11.10
 */
class ValidationService {
    private HTMLParserService $htmlParser;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->htmlParser = new HTMLParserService();
    }
    
    /**
     * Validate a Country model
     * 
     * @param Country $country The country to validate
     * @return ValidationResult The validation result
     */
    public function validateCountry(Country $country): ValidationResult {
        return $country->validate();
    }
    
    /**
     * Validate a CountryOverview model
     * 
     * @param CountryOverview $overview The overview to validate
     * @return ValidationResult The validation result
     */
    public function validateOverview(CountryOverview $overview): ValidationResult {
        return $overview->validate();
    }
    
    /**
     * Validate a RegulatoryFramework model
     * 
     * @param RegulatoryFramework $framework The framework to validate
     * @return ValidationResult The validation result
     */
    public function validateRegulatoryFramework(RegulatoryFramework $framework): ValidationResult {
        return $framework->validate();
    }
    
    /**
     * Validate a DocumentationCard model
     * 
     * @param DocumentationCard $card The card to validate
     * @return ValidationResult The validation result
     */
    public function validateDocumentationCard(DocumentationCard $card): ValidationResult {
        return $card->validate();
    }
    
    /**
     * Validate HTML content
     * 
     * Checks for valid HTML structure and dangerous content.
     * 
     * @param string $html The HTML content to validate
     * @return ValidationResult The validation result
     */
    public function validateHTML(string $html): ValidationResult {
        return $this->htmlParser->validate($html);
    }
    
    /**
     * Check for broken links in HTML content
     * 
     * Extracts all links from HTML and checks if they are accessible.
     * Returns an array of broken links with their URLs.
     * 
     * @param string $html The HTML content to check
     * @return array Array of broken links ['url' => string, 'error' => string]
     */
    public function checkBrokenLinks(string $html): array {
        $broken_links = [];
        
        // Parse HTML to extract links
        $parsed = $this->htmlParser->parse($html);
        if (!$parsed->isValid()) {
            return $broken_links;
        }
        
        // Extract all anchor tags
        $dom = $parsed->dom;
        $links = $dom->getElementsByTagName('a');
        
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            
            // Skip empty, anchor, and javascript links
            if (empty($href) || $href[0] === '#' || strpos($href, 'javascript:') === 0) {
                continue;
            }
            
            // Skip mailto and tel links
            if (strpos($href, 'mailto:') === 0 || strpos($href, 'tel:') === 0) {
                continue;
            }
            
            // Check if link is accessible
            if (!$this->isLinkAccessible($href)) {
                $broken_links[] = [
                    'url' => $href,
                    'error' => 'Link is not accessible'
                ];
            }
        }
        
        return $broken_links;
    }
    
    /**
     * Validate that a country has all required content for publication
     * 
     * @param Country $country The country to validate
     * @param CountryOverview|null $overview The country overview
     * @param array $frameworks Array of RegulatoryFramework objects
     * @return ValidationResult The validation result
     */
    public function validateForPublication(
        Country $country, 
        ?CountryOverview $overview, 
        array $frameworks
    ): ValidationResult {
        $result = new ValidationResult();
        
        // Validate country basic fields
        $country_validation = $country->validate();
        if (!$country_validation->is_valid) {
            foreach ($country_validation->errors as $field => $message) {
                $result->addError($field, $message);
            }
        }
        
        // Requirement 11.1: Hero title required
        if (empty($country->hero_title)) {
            $result->addError('hero_title', 'Hero title is required for publication');
        }
        
        // Requirement 11.2: Hero description required
        if (empty($country->hero_description)) {
            $result->addError('hero_description', 'Hero description is required for publication');
        }
        
        // Requirement 11.3: At least one overview paragraph required
        if ($overview === null || (empty($overview->overview_text_left) && empty($overview->overview_text_right))) {
            $result->addError('overview', 'At least one overview paragraph is required for publication');
        }
        
        // Requirement 11.4: All 3 regulatory framework boxes must have title and description
        if (count($frameworks) < 3) {
            $result->addError('regulatory_frameworks', 'All 3 regulatory framework boxes are required for publication');
        } else {
            foreach ($frameworks as $index => $framework) {
                if (empty($framework->title)) {
                    $result->addError("framework_{$index}_title", "Regulatory framework {$index} title is required");
                }
                if (empty($framework->description)) {
                    $result->addError("framework_{$index}_description", "Regulatory framework {$index} description is required");
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Validate character count for a field
     * 
     * @param string $value The value to check
     * @param int $max_length The maximum allowed length
     * @param string $field_name The field name for error messages
     * @return ValidationResult The validation result
     */
    public function validateCharacterCount(string $value, int $max_length, string $field_name): ValidationResult {
        $result = new ValidationResult();
        
        $length = strlen($value);
        if ($length > $max_length) {
            $result->addError(
                $field_name,
                sprintf('%s must not exceed %d characters (current: %d)', 
                    $field_name, 
                    $max_length, 
                    $length
                )
            );
        }
        
        return $result;
    }
    
    /**
     * Validate that HTML content does not contain dangerous scripts or iframes
     * 
     * @param string $html The HTML content to validate
     * @return ValidationResult The validation result
     */
    public function validateHTMLSecurity(string $html): ValidationResult {
        $result = new ValidationResult();
        
        // Check for dangerous tags
        $dangerous_tags = ['script', 'iframe', 'object', 'embed', 'applet', 'form'];
        
        foreach ($dangerous_tags as $tag) {
            if (preg_match("/<{$tag}[\s>]/i", $html)) {
                $result->addError(
                    'html_security',
                    "HTML content contains potentially dangerous <{$tag}> tag"
                );
            }
        }
        
        // Check for javascript: in attributes
        if (preg_match('/javascript:/i', $html)) {
            $result->addError(
                'html_security',
                'HTML content contains potentially dangerous javascript: protocol'
            );
        }
        
        // Check for on* event handlers
        if (preg_match('/\son\w+\s*=/i', $html)) {
            $result->addError(
                'html_security',
                'HTML content contains potentially dangerous event handlers'
            );
        }
        
        return $result;
    }
    
    /**
     * Check if a link is accessible
     * 
     * Performs a HEAD request to check if the URL is accessible.
     * 
     * @param string $url The URL to check
     * @return bool True if accessible, false otherwise
     */
    private function isLinkAccessible(string $url): bool {
        // For relative URLs, we can't check accessibility without a base URL
        if (!preg_match('/^https?:\/\//i', $url)) {
            return true; // Assume relative URLs are valid
        }
        
        // Use curl to check if URL is accessible
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Consider 2xx and 3xx status codes as accessible
        return $result !== false && $http_code >= 200 && $http_code < 400;
    }
    
    /**
     * Validate email format
     * 
     * @param string $email The email to validate
     * @return bool True if valid, false otherwise
     */
    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate URL format
     * 
     * @param string $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    public function validateURL(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Sanitize user input to prevent XSS
     * 
     * @param string $input The input to sanitize
     * @return string The sanitized input
     */
    public function sanitizeInput(string $input): string {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
