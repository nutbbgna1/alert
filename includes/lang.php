<?php
// ─── Language System ───────────────────────────────────────
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle language switch request
if (isset($_GET['lang']) && in_array($_GET['lang'], ['th', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
    // Redirect back to same page without lang param
    $redirect = strtok($_SERVER['REQUEST_URI'], '?');
    $params = $_GET;
    unset($params['lang']);
    if (!empty($params)) $redirect .= '?' . http_build_query($params);
    header("Location: $redirect");
    exit;
}

// Default language
$currentLang = $_SESSION['lang'] ?? 'th';

// Load language file
$langFile = __DIR__ . "/../lang/{$currentLang}.php";
if (!file_exists($langFile)) $langFile = __DIR__ . "/../lang/th.php";
$translations = include $langFile;

/**
 * Translate a key. Falls back to the key itself if not found.
 */
function __t(string $key, array $replace = []): string {
    global $translations;
    $text = $translations[$key] ?? $key;
    foreach ($replace as $placeholder => $value) {
        $text = str_replace(':' . $placeholder, $value, $text);
    }
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Same as __t but without htmlspecialchars (for use in HTML attributes etc)
 */
function __r(string $key, array $replace = []): string {
    global $translations;
    $text = $translations[$key] ?? $key;
    foreach ($replace as $placeholder => $value) {
        $text = str_replace(':' . $placeholder, $value, $text);
    }
    return $text;
}
?>
