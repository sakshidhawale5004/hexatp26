<?php

/**
 * ContentRevision Model
 * 
 * Represents a content revision/change history entry.
 * Tracks changes made to country content for audit and rollback purposes.
 */
class ContentRevision {
    public int $id;
    public int $country_id;
    public string $content_type; // 'country', 'overview', 'regulatory_framework', 'documentation_card'
    public int $content_id;
    public string $field_name;
    public ?string $old_value;
    public ?string $new_value;
    public int $changed_by;
    public DateTime $changed_at;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->changed_at = new DateTime();
    }
    
    /**
     * Convert the ContentRevision model to an associative array
     * 
     * @return array The content revision data as an array
     */
    public function toArray(): array {
        $data = [
            'country_id' => $this->country_id,
            'content_type' => $this->content_type,
            'content_id' => $this->content_id,
            'field_name' => $this->field_name,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
            'changed_by' => $this->changed_by,
            'changed_at' => $this->changed_at->format('Y-m-d H:i:s')
        ];
        
        // Include id if it's set
        if (isset($this->id)) {
            $data['id'] = $this->id;
        }
        
        return $data;
    }
    
    /**
     * Create a ContentRevision model from an associative array
     * 
     * @param array $data The content revision data as an array
     * @return ContentRevision The ContentRevision model instance
     */
    public static function fromArray(array $data): ContentRevision {
        $revision = new ContentRevision();
        
        // Set id if provided
        if (isset($data['id'])) {
            $revision->id = (int)$data['id'];
        }
        
        // Set required fields
        $revision->country_id = isset($data['country_id']) ? (int)$data['country_id'] : 0;
        $revision->content_type = $data['content_type'] ?? '';
        $revision->content_id = isset($data['content_id']) ? (int)$data['content_id'] : 0;
        $revision->field_name = $data['field_name'] ?? '';
        $revision->changed_by = isset($data['changed_by']) ? (int)$data['changed_by'] : 0;
        
        // Set optional fields
        $revision->old_value = $data['old_value'] ?? null;
        $revision->new_value = $data['new_value'] ?? null;
        
        // Set timestamp
        if (isset($data['changed_at'])) {
            $revision->changed_at = is_string($data['changed_at']) 
                ? new DateTime($data['changed_at']) 
                : $data['changed_at'];
        }
        
        return $revision;
    }
    
    /**
     * Get a diff between old and new values as HTML
     * 
     * This is a simple implementation that highlights changes.
     * For more sophisticated diff rendering, consider using a library like php-diff.
     * 
     * @return string HTML representation of the diff
     */
    public function getDiff(): string {
        if ($this->old_value === null && $this->new_value === null) {
            return '<span class="text-muted">No changes</span>';
        }
        
        if ($this->old_value === null) {
            return '<span class="diff-added">+ ' . htmlspecialchars($this->new_value) . '</span>';
        }
        
        if ($this->new_value === null) {
            return '<span class="diff-removed">- ' . htmlspecialchars($this->old_value) . '</span>';
        }
        
        // Simple diff: show both old and new
        $html = '<div class="diff-comparison">';
        $html .= '<div class="diff-removed">- ' . htmlspecialchars($this->old_value) . '</div>';
        $html .= '<div class="diff-added">+ ' . htmlspecialchars($this->new_value) . '</div>';
        $html .= '</div>';
        
        return $html;
    }
}
