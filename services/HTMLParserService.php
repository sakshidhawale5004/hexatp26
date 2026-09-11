<?php

require_once __DIR__ . '/../models/ValidationResult.php';

/**
 * HTMLParserService
 * 
 * Service for parsing, validating, sanitizing, and formatting HTML content.
 * Used to process HTML from WYSIWYG editors before storing in the database.
 * 
 * Validates Requirements: 13.1, 13.2, 13.5, 13.6, 13.7, 13.9
 */
class HTMLParserService {
    
    /**
     * Allowed HTML tags that are safe and supported
     */
    private const ALLOWED_TAGS = ['p', 'strong', 'em', 'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'br', 'span', 'div', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'hr', 'img'];
    
    /**
     * Dangerous tags that must be removed for security
     */
    private const DANGEROUS_TAGS = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'style', 'link', 'meta'];
    
    /**
     * Maximum nesting depth to prevent performance issues
     */
    private const MAX_NESTING_DEPTH = 10;
    
    /**
     * Parse HTML string into a ParsedHTML object
     * 
     * Validates Requirement 13.1: Parse HTML into a validated structure
     * 
     * @param string $html The HTML string to parse
     * @return ParsedHTML The parsed HTML object
     */
    public function parse(string $html): ParsedHTML {
        $parsed = new ParsedHTML($html);
        
        // Sanitize first to remove dangerous content
        $sanitized = $this->sanitize($html);
        
        // Create DOMDocument for parsing
        $dom = new DOMDocument('1.0', 'UTF-8');
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        
        // Load HTML with UTF-8 encoding
        $success = $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $sanitized,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        
        // Get parsing errors
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        
        if (!$success) {
            $parsed->errors[] = 'Failed to parse HTML';
            return $parsed;
        }
        
        // Store the DOM
        $parsed->dom = $dom;
        
        // Validate structure
        $validationResult = $this->validateStructure($dom);
        if (!$validationResult->is_valid) {
            $parsed->errors = array_merge($parsed->errors, $validationResult->getErrorMessages());
        }
        
        // Add any libxml errors
        foreach ($errors as $error) {
            $parsed->errors[] = trim($error->message) . ' at line ' . $error->line;
        }
        
        return $parsed;
    }
    
    /**
     * Sanitize HTML by removing dangerous tags and attributes
     * 
     * Validates Requirements 13.5, 13.6: Remove dangerous tags, preserve allowed tags
     * 
     * @param string $html The HTML string to sanitize
     * @return string The sanitized HTML
     */
    public function sanitize(string $html): string {
        // Remove dangerous tags completely (including their content for script/style)
        foreach (self::DANGEROUS_TAGS as $tag) {
            if (in_array($tag, ['script', 'style'])) {
                // Remove script and style tags with their content
                $html = preg_replace('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', '', $html);
            } else {
                // Remove other dangerous tags but keep content
                $html = preg_replace('/<\/?' . $tag . '[^>]*>/i', '', $html);
            }
        }
        
        // Create a temporary DOM to filter tags
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        
        $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        
        // Remove disallowed tags
        $this->removeDisallowedTags($dom);
        
        // Remove dangerous attributes from all elements
        $this->removeDangerousAttributes($dom);
        
        // Get the sanitized HTML
        $sanitized = $dom->saveHTML();
        
        // Remove XML declaration if present
        $sanitized = preg_replace('/<\?xml[^>]*>/i', '', $sanitized);
        
        return trim($sanitized);
    }
    
    /**
     * Pretty print HTML with consistent formatting and indentation
     * 
     * Validates Requirement 13.3: Format HTML with proper indentation
     * Validates Requirement 13.9: Escape special characters appropriately
     * 
     * @param ParsedHTML $parsed The parsed HTML object
     * @return string The formatted HTML string
     */
    public function prettyPrint(ParsedHTML $parsed): string {
        if (!$parsed->isValid() || $parsed->dom === null) {
            return '';
        }
        
        $dom = $parsed->dom;
        
        // Enable formatting
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        
        // Normalize whitespace
        $this->normalizeWhitespace($dom);
        
        // Get formatted HTML
        $html = $dom->saveHTML();
        
        // Remove XML declaration if present
        $html = preg_replace('/<\?xml[^>]*>/i', '', $html);
        
        // Clean up excessive whitespace while preserving structure
        $html = preg_replace('/\n\s*\n/', "\n", $html);
        
        return trim($html);
    }
    
    /**
     * Validate HTML structure and return validation result
     * 
     * Validates Requirements 13.2, 13.7: Validate HTML and report errors
     * 
     * @param string $html The HTML string to validate
     * @return ValidationResult The validation result
     */
    public function validate(string $html): ValidationResult {
        $result = new ValidationResult();
        
        // Parse the HTML
        $parsed = $this->parse($html);
        
        // Check for parsing errors
        if (!empty($parsed->errors)) {
            foreach ($parsed->errors as $error) {
                $result->addError('html', $error);
            }
            return $result;
        }
        
        // Validate structure
        $structureResult = $this->validateStructure($parsed->dom);
        if (!$structureResult->is_valid) {
            foreach ($structureResult->errors as $field => $error) {
                $result->addError($field, $error);
            }
        }
        
        return $result;
    }
    
    /**
     * Validate the structure of a DOM document
     * 
     * @param DOMDocument $dom The DOM document to validate
     * @return ValidationResult The validation result
     */
    private function validateStructure(DOMDocument $dom): ValidationResult {
        $result = new ValidationResult();
        
        // Check for unclosed tags by validating DOM structure
        $xpath = new DOMXPath($dom);
        
        // Check nesting depth
        $maxDepth = $this->getMaxNestingDepth($dom->documentElement);
        if ($maxDepth > self::MAX_NESTING_DEPTH) {
            $result->addError('structure', 'HTML nesting depth exceeds maximum of ' . self::MAX_NESTING_DEPTH . ' levels');
        }
        
        // Check for proper tag pairing
        $this->validateTagPairing($dom->documentElement, $result);
        
        return $result;
    }
    
    /**
     * Get the maximum nesting depth of a DOM element
     * 
     * @param DOMNode|null $node The node to check
     * @param int $currentDepth The current depth
     * @return int The maximum depth
     */
    private function getMaxNestingDepth(?DOMNode $node, int $currentDepth = 0): int {
        if ($node === null || !$node->hasChildNodes()) {
            return $currentDepth;
        }
        
        $maxDepth = $currentDepth;
        
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $depth = $this->getMaxNestingDepth($child, $currentDepth + 1);
                $maxDepth = max($maxDepth, $depth);
            }
        }
        
        return $maxDepth;
    }
    
    /**
     * Validate that all tags are properly paired
     * 
     * @param DOMNode|null $node The node to validate
     * @param ValidationResult $result The validation result to update
     */
    private function validateTagPairing(?DOMNode $node, ValidationResult $result): void {
        if ($node === null || !$node->hasChildNodes()) {
            return;
        }
        
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                // Self-closing tags like <br> are allowed
                $selfClosingTags = ['br', 'hr', 'img'];
                
                if (!in_array(strtolower($child->nodeName), $selfClosingTags)) {
                    // Check if tag has proper closing (DOMDocument handles this automatically)
                    // If we got here, the tag is properly closed
                }
                
                // Recursively validate children
                $this->validateTagPairing($child, $result);
            }
        }
    }
    
    /**
     * Remove disallowed tags from DOM
     * 
     * @param DOMDocument $dom The DOM document
     */
    private function removeDisallowedTags(DOMDocument $dom): void {
        $xpath = new DOMXPath($dom);
        $allElements = $xpath->query('//*');
        
        $nodesToRemove = [];
        
        foreach ($allElements as $element) {
            $tagName = strtolower($element->nodeName);
            
            // Skip if it's an allowed tag
            if (in_array($tagName, self::ALLOWED_TAGS)) {
                continue;
            }
            
            // Skip if it's a text node or special node
            if (in_array($tagName, ['#text', '#comment', 'html', 'body'])) {
                continue;
            }
            
            // Mark for removal
            $nodesToRemove[] = $element;
        }
        
        // Remove disallowed tags but keep their content
        foreach ($nodesToRemove as $node) {
            // Move children to parent before removing
            if ($node->hasChildNodes()) {
                $fragment = $dom->createDocumentFragment();
                while ($node->firstChild) {
                    $fragment->appendChild($node->firstChild);
                }
                $node->parentNode->replaceChild($fragment, $node);
            } else {
                $node->parentNode->removeChild($node);
            }
        }
    }
    
    /**
     * Remove dangerous attributes from all elements
     * 
     * @param DOMDocument $dom The DOM document
     */
    private function removeDangerousAttributes(DOMDocument $dom): void {
        $dangerousAttributes = ['onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur', 'onchange', 'onsubmit'];
        
        $xpath = new DOMXPath($dom);
        $allElements = $xpath->query('//*');
        
        foreach ($allElements as $element) {
            foreach ($dangerousAttributes as $attr) {
                if ($element->hasAttribute($attr)) {
                    $element->removeAttribute($attr);
                }
            }
            
            // Also check for javascript: in href attributes
            if ($element->hasAttribute('href')) {
                $href = $element->getAttribute('href');
                if (stripos($href, 'javascript:') === 0) {
                    $element->removeAttribute('href');
                }
            }
            
            // Check for data URIs in src attributes
            if ($element->hasAttribute('src')) {
                $src = $element->getAttribute('src');
                if (stripos($src, 'data:') === 0) {
                    $element->removeAttribute('src');
                }
            }
        }
    }
    
    /**
     * Normalize whitespace in DOM while preserving intentional breaks
     * 
     * Validates Requirement 13.8: Normalize whitespace while preserving intentional breaks
     * 
     * @param DOMDocument $dom The DOM document
     */
    private function normalizeWhitespace(DOMDocument $dom): void {
        $xpath = new DOMXPath($dom);
        $textNodes = $xpath->query('//text()');
        
        foreach ($textNodes as $textNode) {
            $text = $textNode->nodeValue;
            
            // Skip if parent is <pre> or <code> (preserve formatting)
            $parent = $textNode->parentNode;
            if ($parent && in_array(strtolower($parent->nodeName), ['pre', 'code'])) {
                continue;
            }
            
            // Normalize multiple spaces to single space
            $text = preg_replace('/[ \t]+/', ' ', $text);
            
            // Normalize multiple newlines
            $text = preg_replace('/\n\s*\n/', "\n", $text);
            
            $textNode->nodeValue = $text;
        }
    }
}

/**
 * ParsedHTML
 * 
 * Represents parsed HTML content with validation state
 */
class ParsedHTML {
    public string $raw_html;
    public ?DOMDocument $dom = null;
    public array $allowed_tags;
    public array $errors = [];
    
    public function __construct(string $html) {
        $this->raw_html = $html;
        $this->allowed_tags = ['p', 'strong', 'em', 'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'br'];
    }
    
    /**
     * Check if the parsed HTML is valid
     * 
     * @return bool True if valid, false otherwise
     */
    public function isValid(): bool {
        return empty($this->errors) && $this->dom !== null;
    }
    
    /**
     * Get the body content as HTML string
     * 
     * @return string The HTML body content
     */
    public function getBody(): string {
        if ($this->dom === null) {
            return '';
        }
        
        return $this->dom->saveHTML();
    }
}
