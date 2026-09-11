<?php
/**
 * Country Edit Form
 * Country Content CMS
 * 
 * Requirements: 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10, 11.8, 12.2
 */

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/ContentService.php';

// Start session and check authentication
$conn = getDBConnection();
$authService = new AuthService($conn);

if (!$authService->checkSession()) {
    header('Location: login.php');
    exit;
}

// Get current user
$current_user = $authService->getCurrentUser();

// Get CSRF token
$csrf_token = $authService->generateCsrfToken();

// Determine if creating new or editing existing
$action = $_GET['action'] ?? 'edit';
$country_id = $_GET['id'] ?? null;

$country = null;
$page_title = 'Add New Country';

if ($action === 'edit' && $country_id) {
    $contentService = new ContentService($conn);
    $country = $contentService->getCountry((int)$country_id);
    
    if (!$country) {
        header('Location: countries_list.php?error=not_found');
        exit;
    }
    
    $page_title = 'Edit Country: ' . htmlspecialchars($country->country_name);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | HexaTP CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        :root {
            --bg-light: #ffffff;
            --bg-subtle: #f9fafb;
            --accent: #f5c400;
            --accent-glow: rgba(245, 196, 0, 0.2);
            --card-bg: #ffffff;
            --glass-border: rgba(0, 0, 0, 0.08);
            --text-main: #0f172a;
            --text-slate: #64748b;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-main);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.85); box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            padding: 20px 0;
            margin-bottom: 30px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .admin-header h1 {
            color: var(--accent);
            font-weight: 800;
            margin: 0;
            font-size: 1.5rem;
        }

        .form-section {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .form-section h2 {
            color: var(--accent);
            font-size: 1.3rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-label {
            color: var(--text-slate);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-label .required {
            color: #ff6b6b;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-main);
            padding: 12px 16px;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
            color: var(--text-main);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .char-counter {
            font-size: 0.85rem;
            color: var(--text-slate);
            margin-top: 5px;
        }

        .char-counter.warning {
            color: #ff9800;
        }

        .char-counter.error {
            color: #ff6b6b;
        }

        .btn-save {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-save:hover {
            background: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--accent-glow);
        }

        .btn-draft {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
            border: 1px solid #ff9800;
        }

        .btn-draft:hover {
            background: #ff9800;
            color: #000;
        }

        .btn-cancel {
            background: transparent;
            color: var(--text-slate);
            border: 1px solid var(--glass-border);
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #4caf50;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
        }

        .alert-warning {
            background: rgba(255, 152, 0, 0.1);
            border: 1px solid rgba(255, 152, 0, 0.3);
            color: #ff9800;
        }

        .framework-box, .card-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .framework-box h4, .card-box h4 {
            color: var(--accent);
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .btn-remove {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .btn-remove:hover {
            background: #dc3545;
            color: var(--text-main);
        }

        .btn-add {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #4caf50;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-add:hover {
            background: #4caf50;
            color: var(--text-main);
        }

        /* TinyMCE dark theme adjustments */
        .tox .tox-edit-area__iframe {
            background-color: #1a1a1a !important;
        }

        @media (max-width: 768px) {
            .form-section {
                padding: 20px 15px;
            }

            .admin-header h1 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 d-flex align-items-center gap-3">
                    <img src="../logo-hexatp.jpeg" alt="HexaTP Logo" style="height: 40px;">
                    <h1 class="mb-0"><?php echo $page_title; ?></h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="countries_list.php" class="btn btn-cancel">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div id="alertContainer"></div>

        <form id="countryForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="country_id" value="<?php echo $country_id ?? ''; ?>">

            <!-- Basic Information -->
            <div class="form-section">
                <h2><i class="bi bi-info-circle"></i> Basic Information</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="country_name" class="form-label">
                                Country Name <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="country_name" 
                                name="country_name" 
                                placeholder="e.g., Australia"
                                required
                                maxlength="100"
                                value="<?php echo $country ? htmlspecialchars($country->country_name) : ''; ?>"
                            >
                            <div class="char-counter">
                                <span id="country_name_count">0</span> / 100 characters
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="country_code" class="form-label">
                                Country Code <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="country_code" 
                                name="country_code" 
                                placeholder="e.g., AU"
                                required
                                maxlength="10"
                                value="<?php echo $country ? htmlspecialchars($country->country_code) : ''; ?>"
                            >
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?php echo ($country && $country->status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($country && $country->status === 'published') ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="flag_url" class="form-label">Flag URL</label>
                            <input 
                                type="url" 
                                class="form-control" 
                                id="flag_url" 
                                name="flag_url" 
                                placeholder="https://example.com/flags/australia.png"
                                value="<?php echo $country ? htmlspecialchars($country->flag_url ?? '') : ''; ?>"
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="hero_bg_image" class="form-label">Hero Background Image URL</label>
                            <input 
                                type="url" 
                                class="form-control" 
                                id="hero_bg_image" 
                                name="hero_bg_image" 
                                placeholder="https://example.com/images/hero-bg.jpg"
                                value="<?php echo $country ? htmlspecialchars($country->hero_bg_image ?? '') : ''; ?>"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="form-section">
                <h2><i class="bi bi-star"></i> Hero Section</h2>
                <div class="mb-3">
                    <label for="hero_title" class="form-label">
                        Hero Title <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="hero_title" 
                        name="hero_title" 
                        placeholder="e.g., Transfer Pricing Australia"
                        required
                        maxlength="100"
                        value="<?php echo $country ? htmlspecialchars($country->hero_title ?? '') : ''; ?>"
                    >
                    <div class="char-counter">
                        <span id="hero_title_count">0</span> / 100 characters
                    </div>
                </div>
                <div class="mb-3">
                    <label for="hero_description" class="form-label">
                        Hero Description <span class="required">*</span>
                    </label>
                    <textarea 
                        class="form-control" 
                        id="hero_description" 
                        name="hero_description" 
                        rows="4"
                        placeholder="Brief description for the hero section..."
                        required
                        maxlength="500"
                    ><?php echo $country ? htmlspecialchars($country->hero_description ?? '') : ''; ?></textarea>
                    <div class="char-counter">
                        <span id="hero_description_count">0</span> / 500 characters
                    </div>
                </div>
            </div>

            <!-- SEO Meta Tags -->
            <div class="form-section">
                <h2><i class="bi bi-search"></i> SEO Meta Tags</h2>
                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="meta_title" 
                        name="meta_title" 
                        placeholder="e.g., Australia Transfer Pricing | HexaTP"
                        maxlength="255"
                        value="<?php echo $country ? htmlspecialchars($country->meta_title ?? '') : ''; ?>"
                    >
                    <div class="char-counter">
                        <span id="meta_title_count">0</span> / 255 characters
                    </div>
                </div>
                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea 
                        class="form-control" 
                        id="meta_description" 
                        name="meta_description" 
                        rows="3"
                        placeholder="SEO description for search engines..."
                        maxlength="500"
                    ><?php echo $country ? htmlspecialchars($country->meta_description ?? '') : ''; ?></textarea>
                    <div class="char-counter">
                        <span id="meta_description_count">0</span> / 500 characters
                    </div>
                </div>
            </div>

            <!-- Overview Section -->
            <div class="form-section">
                <h2><i class="bi bi-file-text"></i> Overview Section</h2>
                <p style="color: var(--text-slate); margin-bottom: 20px;">
                    The overview section appears at the top of the country page in a two-column layout.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="overview_text_left" class="form-label">Left Column</label>
                            <textarea 
                                class="form-control" 
                                id="overview_text_left" 
                                name="overview_text_left" 
                                rows="8"
                                placeholder="Overview content for left column..."
                            ><?php echo ($country && $country->overview) ? htmlspecialchars($country->overview->overview_text_left ?? '') : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="overview_text_right" class="form-label">Right Column</label>
                            <textarea 
                                class="form-control" 
                                id="overview_text_right" 
                                name="overview_text_right" 
                                rows="8"
                                placeholder="Overview content for right column..."
                            ><?php echo ($country && $country->overview) ? htmlspecialchars($country->overview->overview_text_right ?? '') : ''; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regulatory Frameworks -->
            <div class="form-section">
                <h2><i class="bi bi-shield-check"></i> Key Regulatory Frameworks</h2>
                <p style="color: var(--text-slate); margin-bottom: 20px;">
                    These are the three main regulatory framework boxes displayed on the country page.
                </p>
                
                <?php
                // Get existing frameworks or create empty array
                $frameworks = [];
                if ($country && !empty($country->regulatory_frameworks)) {
                    $frameworks = $country->regulatory_frameworks;
                }
                
                // Ensure we have exactly 3 frameworks
                while (count($frameworks) < 3) {
                    $framework = new stdClass();
                    $framework->id = null;
                    $framework->title = '';
                    $framework->description = '';
                    $framework->display_order = count($frameworks) + 1;
                    $frameworks[] = $framework;
                }
                
                foreach ($frameworks as $index => $framework):
                    $num = $index + 1;
                ?>
                <div class="framework-box">
                    <h4>Framework <?php echo $num; ?></h4>
                    <input type="hidden" name="frameworks[<?php echo $index; ?>][id]" value="<?php echo $framework->id ?? ''; ?>">
                    <input type="hidden" name="frameworks[<?php echo $index; ?>][display_order]" value="<?php echo $num; ?>">
                    
                    <div class="mb-3">
                        <label for="framework_title_<?php echo $num; ?>" class="form-label">Title</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="framework_title_<?php echo $num; ?>" 
                            name="frameworks[<?php echo $index; ?>][title]" 
                            placeholder="e.g., Transfer Pricing Regulations"
                            maxlength="255"
                            value="<?php echo htmlspecialchars($framework->title ?? ''); ?>"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="framework_description_<?php echo $num; ?>" class="form-label">Description</label>
                        <textarea 
                            class="form-control" 
                            id="framework_description_<?php echo $num; ?>" 
                            name="frameworks[<?php echo $index; ?>][description]" 
                            rows="6"
                            placeholder="Description of this regulatory framework..."
                        ><?php echo htmlspecialchars($framework->description ?? ''); ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Documentation Cards -->
            <div class="form-section">
                <h2><i class="bi bi-card-list"></i> Documentation Cards</h2>
                <p style="color: var(--text-slate); margin-bottom: 20px;">
                    Expandable cards with detailed documentation. You can add, edit, reorder, and delete cards.
                </p>
                
                <div id="documentation-cards-container">
                    <?php
                    if ($country && !empty($country->documentation_cards)):
                        foreach ($country->documentation_cards as $index => $card):
                    ?>
                    <div class="card-box" data-card-index="<?php echo $index; ?>">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="bi bi-grip-vertical"></i> Card <?php echo $index + 1; ?></h4>
                            <button type="button" class="btn btn-remove btn-remove-card" onclick="removeCard(this)">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                        
                        <input type="hidden" name="cards[<?php echo $index; ?>][id]" value="<?php echo $card->id ?? ''; ?>">
                        <input type="hidden" name="cards[<?php echo $index; ?>][display_order]" value="<?php echo $index + 1; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Title <span class="required">*</span></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="cards[<?php echo $index; ?>][title]" 
                                placeholder="e.g., Transfer Pricing Documentation"
                                maxlength="150"
                                value="<?php echo htmlspecialchars($card->title ?? ''); ?>"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea 
                                class="form-control" 
                                name="cards[<?php echo $index; ?>][short_description]" 
                                rows="2"
                                placeholder="Brief description shown when card is collapsed..."
                            ><?php echo htmlspecialchars($card->short_description ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detailed Content</label>
                            <textarea 
                                class="form-control" 
                                id="card_content_<?php echo $index; ?>"
                                name="cards[<?php echo $index; ?>][detailed_content]" 
                                rows="8"
                                placeholder="Full detailed content shown when card is expanded..."
                            ><?php echo htmlspecialchars($card->detailed_content ?? ''); ?></textarea>
                        </div>
                    </div>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-add" onclick="addCard()">
                        <i class="bi bi-plus-circle"></i> Add Documentation Card
                    </button>
                </div>
            </div>
                
            <!-- Form Actions -->
            <div class="form-section">
                <div class="d-flex gap-3 flex-wrap">
                    <button type="submit" class="btn btn-save" data-action="save">
                        <i class="bi bi-check-circle"></i> Save & Publish
                    </button>
                    <button type="submit" class="btn btn-save btn-draft" data-action="draft">
                        <i class="bi bi-file-earmark"></i> Save as Draft
                    </button>
                    <?php 
                    if ($country && $country->id): 
                        // Logic to find live HTML file
                        $html_file = strtolower(str_replace(' ', '', $country->country_name)) . '.html';
                        $special_cases = [
                            'united arab emirates' => 'unitedarab.html',
                            'saudi arabia' => 'saudiarabia.html',
                            'vietnam' => 'viethnam.html',
                            'united states' => 'us.html'
                        ];
                        $lookup_name = strtolower(trim($country->country_name));
                        if (isset($special_cases[$lookup_name])) {
                            $html_file = $special_cases[$lookup_name];
                        }
                        
                        // Default to country.php if HTML doesn't exist
                        $live_url = "../" . $html_file;
                        if (!file_exists(__DIR__ . '/../' . $html_file)) {
                            $live_url = "../country.php?id=" . $country->id;
                        }
                    ?>
                    <a href="<?php echo $live_url; ?>" target="_blank" class="btn btn-cancel" style="background: rgba(245, 196, 0, 0.1); color: var(--accent); border-color: var(--accent);">
                        <i class="bi bi-eye"></i> View Live Page
                    </a>
                    <?php endif; ?>
                    <a href="countries_list.php" class="btn btn-cancel">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // TinyMCE Initialization
        function initTinyMCE(selector) {
            tinymce.init({
                selector: selector,
                height: 300,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                skin: 'oxide-dark',
                content_css: 'dark',
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
        }

        // Initialize TinyMCE when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Check if tinymce is loaded, if not wait a bit (retry)
            let retries = 0;
            const tryInit = setInterval(() => {
                if (typeof tinymce !== 'undefined') {
                    clearInterval(tryInit);
                    initTinyMCE('#overview_text_left, #overview_text_right, [id^="framework_description_"], [id^="card_content_"]');
                } else if (retries > 10) {
                    clearInterval(tryInit);
                    console.error('TinyMCE failed to load after 10 retries');
                }
                retries++;
            }, 100);
        });

        // Documentation Card management
        let cardCounter = <?php echo $country && !empty($country->documentation_cards) ? count($country->documentation_cards) : 0; ?>;
        
        function addCard() {
            const container = document.getElementById('documentation-cards-container');
            const index = cardCounter++;
            
            const cardHTML = `
                <div class="card-box mb-4 p-4 border rounded" style="background: rgba(255,255,255,0.02);">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="m-0 text-info">Documentation Card #${index + 1}</h5>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeCard(this)">
                            <i class="bi bi-trash"></i> Remove Card
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Card Title</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="cards[${index}][title]" 
                            placeholder="e.g., Transfer Pricing Documentation"
                            maxlength="150"
                            required
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea 
                            class="form-control" 
                            name="cards[${index}][short_description]" 
                            rows="2"
                            placeholder="Brief description shown when card is collapsed..."
                        ></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detailed Content</label>
                        <textarea 
                            class="form-control" 
                            id="card_content_${index}"
                            name="cards[${index}][detailed_content]" 
                            rows="8"
                            placeholder="Full detailed content shown when card is expanded..."
                        ></textarea>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', cardHTML);
            
            // Initialize TinyMCE for the new textarea
            setTimeout(() => {
                initTinyMCE(`#card_content_${index}`);
            }, 100);
        }

        function removeCard(button) {
            if (confirm('Are you sure you want to remove this documentation card?')) {
                const cardBox = button.closest('.card-box');
                const textarea = cardBox.querySelector('textarea[id^="card_content_"]');
                if (textarea && tinymce.get(textarea.id)) {
                    tinymce.get(textarea.id).remove();
                }
                cardBox.remove();
            }
        }

        // Character counters
        function updateCharCounter(inputId, counterId, maxLength) {
            const input = document.getElementById(inputId);
            const counter = document.getElementById(counterId);
            const parent = counter.parentElement;
            
            if (!input || !counter) return;
            
            const updateCount = () => {
                const length = input.value.length;
                counter.textContent = length;
                
                parent.classList.remove('warning', 'error');
                if (length > maxLength * 0.9) {
                    parent.classList.add('warning');
                }
                if (length >= maxLength) {
                    parent.classList.add('error');
                }
            };
            
            input.addEventListener('input', updateCount);
            updateCount();
        }

        // Initialize character counters
        updateCharCounter('country_name', 'country_name_count', 100);
        updateCharCounter('hero_title', 'hero_title_count', 100);
        updateCharCounter('hero_description', 'hero_description_count', 500);
        updateCharCounter('meta_title', 'meta_title_count', 255);
        updateCharCounter('meta_description', 'meta_description_count', 500);

        // Session timeout warning (30 minutes = 1800 seconds)
        const SESSION_TIMEOUT = 1800; // 30 minutes in seconds
        const WARNING_TIME = 1500; // Show warning at 25 minutes (1500 seconds)
        
        // Start session timer
        let sessionStartTime = Date.now();
        
        // Check session timeout every minute
        setInterval(() => {
            const elapsedSeconds = Math.floor((Date.now() - sessionStartTime) / 1000);
            
            if (elapsedSeconds >= WARNING_TIME && elapsedSeconds < SESSION_TIMEOUT) {
                // Show warning at 25 minutes
                const remainingMinutes = Math.ceil((SESSION_TIMEOUT - elapsedSeconds) / 60);
                showAlert('warning', `Your session will expire in ${remainingMinutes} minute(s). Please save your work soon.`);
            }
        }, 60000); // Check every minute

        // Form submission
        let lastAction = 'save';
        
        // Capture which button was clicked
        document.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.addEventListener('click', function() {
                lastAction = this.dataset.action;
            });
        });

        document.getElementById('countryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = e.submitter || document.querySelector(`button[data-action="${lastAction}"]`);
            const action = lastAction;
            
            // Set status based on action
            const statusField = document.getElementById('status');
            if (statusField) {
                if (action === 'draft') {
                    statusField.value = 'draft';
                } else {
                    statusField.value = 'published';
                }
            }
            
            // Sync TinyMCE content to textareas
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
            
            // Collect form data
            const formData = new FormData(this);
            const data = {};
            
            // Convert FormData to nested object structure
            for (let [key, value] of formData.entries()) {
                if (key.includes('[')) {
                    // Handle array fields (frameworks, cards)
                    const matches = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
                    if (matches) {
                        const [, arrayName, index, fieldName] = matches;
                        if (!data[arrayName]) data[arrayName] = [];
                        if (!data[arrayName][index]) data[arrayName][index] = {};
                        data[arrayName][index][fieldName] = value;
                    }
                } else {
                    data[key] = value;
                }
            }
            
            // Show loading
            const originalHTML = submitBtn ? submitBtn.innerHTML : 'Saving...';
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
                submitBtn.disabled = true;
            }
            
            // Determine API endpoint
            const countryId = data.country_id;
            // Use POST for both Create and Update to avoid shared hosting PUT restrictions
            const method = 'POST';
            const url = countryId ? `../api/country.php?id=${countryId}` : '../api/country.php';
            
            // Submit to API
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                // Store status code for error handling
                const status = response.status;
                return response.json().then(result => ({ status, result }));
            })
            .then(({ status, result }) => {
                if (result.success) {
                    showAlert('success', 'Country saved successfully!');
                    setTimeout(() => {
                        window.location.href = 'countries_list.php';
                    }, 1500);
                } else {
                    // Handle different error types based on HTTP status code
                    if (status === 401) {
                        // Session expired
                        showAlert('danger', 'Session expired. Please log in again.');
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    } else if (status === 403) {
                        // CSRF token invalid
                        showAlert('danger', 'Invalid security token. Please refresh the page and try again.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else if (status === 400 && result.errors) {
                        // Validation errors - display field-specific errors
                        const errorMessages = Object.entries(result.errors).map(([field, message]) => {
                            return `<strong>${field}:</strong> ${message}`;
                        }).join('<br>');
                        showAlert('danger', 'Validation errors:<br>' + errorMessages);
                        submitBtn.innerHTML = originalHTML;
                        submitBtn.disabled = false;
                    } else {
                        // Generic error
                        const errorMsg = result.error || (result.errors ? Object.values(result.errors).join(', ') : 'Failed to save country');
                        showAlert('danger', 'Error: ' + errorMsg);
                        submitBtn.innerHTML = originalHTML;
                        submitBtn.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                showAlert('danger', 'Error: Failed to save country. Please try again later.');
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            });
        });

        // Show alert function
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    </script>
</body>
</html>
