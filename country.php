<?php
/**
 * HexaTP Country Page - Master Template
 * Mirrors the premium India.html design perfectly.
 */
require_once 'db_config.php';
require_once 'services/ContentService.php';

$contentService = new ContentService($conn);

// Get slug from URL (rewrite rule handles this)
$slug = isset($_GET['slug']) ? $_GET['slug'] : 'india';
$countryName = ucfirst(str_replace('.html', '', $slug));

// Slug mapping to ensure correct lookup
$slugMap = [
    'unitedarab' => 'United Arab Emirates',
    'saudiarabia' => 'Saudi Arabia',
    'qatar' => 'Qatar',
    'oman' => 'Oman',
    'bahrain' => 'Bahrain',
    'egypt' => 'Egypt',
    'india' => 'India',
    'bangladesh' => 'Bangladesh',
    'singapore' => 'Singapore',
    'thailand' => 'Thailand',
    'malaysia' => 'Malaysia',
    'australia' => 'Australia',
    'indonesia' => 'Indonesia',
    'viethnam' => 'Vietnam',
    'botswana' => 'Botswana',
    'ghana' => 'Ghana',
    'kenya' => 'Kenya',
    'canada' => 'Canada',
    'us' => 'United States'
];

$lookupName = isset($slugMap[strtolower($slug)]) ? $slugMap[strtolower($slug)] : $countryName;

// Fetch country details from DB
$country = null;
$allCountries = $contentService->getAllCountries();
foreach ($allCountries as $c) {
    if (strtolower($c->country_name) === strtolower($lookupName)) {
        $country = $contentService->getCountry($c->id);
        break;
    }
}

// Fallback if not found
if (!$country) {
    header("Location: index.html");
    exit;
}

$page_title = $country->meta_title ?: "HexaTP | " . $country->country_name . " Transfer Pricing";
$meta_description = $country->meta_description;

include 'includes/header.php';
?>

<style>
    /* Section specific design mirroring India.html */
    .hero {
        padding: 180px 0 100px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.9)), 
                    url('<?php echo $country->hero_bg_image ?: "https://plus.unsplash.com/premium_photo-1661919589683-f11880119fb7"; ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .hero h1 { font-weight: 700; font-size: 48px; }
    .hero p { color: var(--text-slate); max-width: 600px; }

    .btn-accent {
        background: var(--accent);
        color: black;
        font-weight: 600;
        padding: 12px 30px;
        border-radius: 40px;
        text-decoration: none;
        display: inline-block;
    }

    .section-title { font-weight: 700; font-size: 34px; margin-bottom: 40px; text-align: center; }
    
    .reg-box {
        padding: 25px;
        border-left: 3px solid var(--accent);
        background: var(--card-bg);
        margin-bottom: 20px;
        height: 100%;
    }

    .glass-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 30px;
        height: 100%;
        transition: 0.3s;
        cursor: pointer;
    }

    .glass-card:hover { transform: translateY(-6px); border-color: var(--accent); }

    .content { display: none; margin-top: 15px; line-height: 1.7; color: var(--text-slate); font-size: 0.95rem; }
    .arrow { font-size: 18px; color: var(--accent); display: block; margin-bottom: 10px; transition: 0.3s; }
    .rotate { transform: rotate(90deg); }

    .cta {
        padding: 80px 0;
        text-align: center;
        background: var(--bg-subtle);
    }

    /* Team styles */
    .team-card {
        background: var(--card-bg);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        transition: 0.3s;
        height: 100%;
    }
    .team-img-wrapper img { width: 150px; height: 150px; object-fit: cover; border: 1px solid var(--glass-border); }

    /* Contact Footer styles */
    .contact-footer {
        padding: 100px 5%;
        text-align: center;
        background: linear-gradient(0deg, #fff9e6 0%, var(--bg-light) 100%);
    }

    /* Custom CSS Injection for background and unique styles */
    <?php
    $bgFile = __DIR__ . '/custom_layouts/' . strtolower($slug) . '_bg.css';
    if (file_exists($bgFile)) {
        echo file_get_contents($bgFile);
    }
    ?>
</style>

<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-4">Transfer Pricing in <span class="text-warning"><?php echo htmlspecialchars($country->country_name); ?></span></h1>
                <p class="mb-4 lead"><?php echo strip_tags($country->hero_description); ?></p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="javascript:void(0)" class="btn btn-accent" onclick="openExpertModal('General Consultation')">Book Free Consultation</a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="<?php echo htmlspecialchars($country->flag_url); ?>" alt="Flag" class="img-fluid rounded shadow-lg" style="width: 100%; max-width: 450px; height: 280px; object-fit: cover; border-radius: 12px; border: 1px solid var(--glass-border);">
            </div>
        </div>
    </div>
</section>

<!-- Overview Section -->
<?php if ($country->overview): ?>
<section class="py-5">
    <div class="container">
        <h2 class="section-title"><?php echo htmlspecialchars($country->country_name); ?> TP Overview</h2>
        <div class="row">
            <div class="col-lg-6">
                <div style="color:var(--text-slate);"><?php echo $country->overview->overview_text_left; ?></div>
            </div>
            <div class="col-lg-6">
                <div style="color:var(--text-slate);"><?php echo $country->overview->overview_text_right; ?></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Regulatory Framework -->
<?php if (!empty($country->regulatory_frameworks)): ?>
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Key Regulatory Framework</h2>
        <div class="row g-4">
            <?php foreach ($country->regulatory_frameworks as $fw): ?>
            <div class="col-lg-4">
                <div class="reg-box">
                    <h5><?php echo htmlspecialchars($fw->title); ?></h5>
                    <div style="color:var(--text-slate)"><?php echo $fw->description; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Documentation Pillars -->
<?php if (!empty($country->documentation_cards)): ?>
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Documentation Pillars</h2>
        <div class="row g-4">
            <?php 
            $i = 1;
            foreach ($country->documentation_cards as $card): 
            ?>
            <div class="col-lg-4">
                <div class="glass-card" onclick="toggleContent('dc_content_<?php echo $i; ?>', 'dc_arrow_<?php echo $i; ?>')">
                    <span class="arrow" id="dc_arrow_<?php echo $i; ?>"><?php echo htmlspecialchars($card->title); ?></span>
                    <p style="color:var(--text-slate)"><?php echo htmlspecialchars($card->short_description); ?></p>
                    <div class="content" id="dc_content_<?php echo $i; ?>">
                        <?php echo $card->detailed_content; ?>
                    </div>
                </div>
            </div>
            <?php 
            $i++;
            endforeach; 
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Our Services -->
<?php if (!empty($country->country_services)): ?>
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center">Our Services in <?php echo htmlspecialchars($country->country_name); ?></h2>
        <div class="row g-4">
            <?php foreach ($country->country_services as $service): ?>
            <div class="col-lg-3">
                <div class="glass-card text-center">
                    <h5><?php echo htmlspecialchars($service->title); ?></h5>
                    <div style="color:var(--text-slate)"><?php echo $service->description; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta" id="consult">
    <div class="container">
        <h2><?php echo htmlspecialchars($country->cta_title); ?></h2>
        <p style="color:var(--text-slate)">Navigate the complex TP landscape with our specialized local expertise and benchmarking solutions.</p>
        <a class="btn btn-accent mt-3" href="mailto:connect@hexatp.com"><?php echo htmlspecialchars($country->cta_button_text); ?></a>
    </div>
</section>

<!-- Team Section -->
<?php
// Try to load team partial from custom_layouts
$teamFile = __DIR__ . '/custom_layouts/' . strtolower($slug) . '_team.html';
if (file_exists($teamFile)) {
    include $teamFile;
} else {
    echo '<section class="py-5"><div class="container"><p class="text-center text-muted">Contact our Managing Director at connect@hexatp.com</p></div></section>';
}
?>

<!-- Contact Footer -->
<section class="contact-footer">
    <div class="container">
        <span class="text-warning text-uppercase fw-bold" style="letter-spacing:2px;">Secure Your Future</span>
        <h2 class="mt-3"><?php echo htmlspecialchars($country->footer_title); ?></h2>
        <a href="mailto:<?php echo htmlspecialchars($country->footer_email); ?>" class="btn-main px-5 py-3 fs-5 mt-4"><?php echo htmlspecialchars($country->footer_email); ?></a>
        <p class="mt-4 text-muted">Contact our Managing Director for a confidential consultation on your Transfer Pricing needs.</p>
    </div>
</section>

<!-- Team Modals Injection -->
<?php
$modalFile = __DIR__ . '/custom_layouts/' . strtolower($slug) . '_modals.html';
if (file_exists($modalFile)) {
    include $modalFile;
}
?>

<!-- General Inquiry Modal -->
<div class="modal fade" id="expertInquiryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-light); border: 1px solid var(--accent); border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" style="font-weight: 700;">Inquiry for <span id="expertName" style="color: var(--accent);">Expert</span></h5>
                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="expertForm">
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-slate); font-size: 0.9rem;">Your Full Name</label>
                        <input type="text" class="form-control bg-transparent text-dark" style="border: 1px solid var(--glass-border); border-radius: 10px;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-slate); font-size: 0.9rem;">Work Email</label>
                        <input type="email" class="form-control bg-transparent text-dark" style="border: 1px solid var(--glass-border); border-radius: 10px;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-slate); font-size: 0.9rem;">Message</label>
                        <textarea class="form-control bg-transparent text-dark" rows="3" style="border: 1px solid var(--glass-border); border-radius: 10px;"></textarea>
                    </div>
                    <button type="submit" class="btn-main w-100 border-0 mt-2 py-3">Submit Inquiry</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleContent(contentId, arrowId) {
        let content = document.getElementById(contentId);
        let arrow = document.getElementById(arrowId);
        if (content.style.display === "block") {
            content.style.display = "none";
            arrow.classList.remove("rotate");
        } else {
            content.style.display = "block";
            arrow.classList.add("rotate");
        }
    }

    function openExpertModal(name) {
        document.getElementById('expertName').innerText = name;
        var myModal = new bootstrap.Modal(document.getElementById('expertInquiryModal'));
        myModal.show();
    }
</script>

<?php include 'includes/footer.php'; ?>
