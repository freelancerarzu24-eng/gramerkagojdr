<?php
/**
 * Core Utility Functions
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sanitize inputs
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Generate Slug
function createSlug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($string));
    return trim($slug, '-');
}

// Redirect helper
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// Set flash message
function setFlash($name, $message, $class = 'alert alert-success') {
    $_SESSION['flash'][$name] = [
        'message' => $message,
        'class' => $class
    ];
}

// Display flash message
function displayFlash($name) {
    if (isset($_SESSION['flash'][$name])) {
        $flash = $_SESSION['flash'][$name];
        echo '<div class="' . $flash['class'] . ' alert-dismissible fade show" role="alert">' . $flash['message'] .
             '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['flash'][$name]);
    }
}

// Generate CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Log Activity
function logActivity($db, $activity) {
    if (!isLoggedIn()) return;
    $db->query("INSERT INTO activity_logs (user_id, activity, ip_address) VALUES (:uid, :act, :ip)");
    $db->bind(':uid', $_SESSION['user_id']);
    $db->bind(':act', $activity);
    $db->bind(':ip', $_SERVER['REMOTE_ADDR']);
    $db->execute();
}

// Format Date
function formatDate($date, $lang = 'en') {
    if ($lang == 'bn') {
        $en_chars = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December','Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','am','pm'];
        $bn_chars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর','শনিবার','রোববার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার','পূর্বাহ্ণ','অপরাহ্ণ'];
        $str = date('j F Y, g:i a', strtotime($date));
        return str_replace($en_chars, $bn_chars, $str);
    }
    return date('F j, Y, g:i a', strtotime($date));
}

// Get English/Bangla content based on session
function __($en, $bn) {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'bn';
    return ($lang == 'en') ? $en : $bn;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check admin role
function isAdmin() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'Super Admin' || $_SESSION['user_role'] == 'Admin');
}

// Reading time calculator
function getReadingTime($text) {
    $wordCount = str_word_count(strip_tags($text));
    $readingTime = ceil($wordCount / 200);
    return $readingTime;
}

// SEO Tags Generator
function generateSEOTags($title, $description, $keywords, $image = '') {
    $tags = '<title>' . $title . '</title>' . PHP_EOL;
    $tags .= '<meta name="description" content="' . $description . '">' . PHP_EOL;
    $tags .= '<meta name="keywords" content="' . $keywords . '">' . PHP_EOL;

    // Open Graph
    $tags .= '<meta property="og:title" content="' . $title . '">' . PHP_EOL;
    $tags .= '<meta property="og:description" content="' . $description . '">' . PHP_EOL;
    if ($image) {
        $tags .= '<meta property="og:image" content="' . $image . '">' . PHP_EOL;
    }
    $tags .= '<meta property="og:type" content="website">' . PHP_EOL;

    // Twitter
    $tags .= '<meta name="twitter:card" content="summary_large_image">' . PHP_EOL;
    $tags .= '<meta name="twitter:title" content="' . $title . '">' . PHP_EOL;
    $tags .= '<meta name="twitter:description" content="' . $description . '">' . PHP_EOL;

    return $tags;
}
?>
