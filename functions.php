<?php
/**
 * VOYA - functions.php
 * Core logic cho dự án APK WordPress
 */

if (!defined('ABSPATH'))
    exit;

// ============================================================
// 1. THEME SETUP
// ============================================================
function vy_theme_setup()
{
    // Hỗ trợ ngôn ngữ
    load_theme_textdomain('voya', get_template_directory() . '/languages');

    // Hỗ trợ thẻ tiêu đề và ảnh đại diện
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);

    add_theme_support('woocommerce');

    // Đăng ký Menu
    register_nav_menus([
        'primary' => __('Primary Menu (Header)', 'voya'),
        'footer' => __('Footer Menu', 'voya'),
    ]);

    // Hỗ trợ Custom Logo
    add_theme_support('custom-logo', [
        'height' => 100,
        'width' => 400,
        'flex-height' => true,
        'flex-width' => true,
    ]);

    // Image Sizes (Giữ theo cấu trúc VOYA bạn đã gửi)
    add_image_size('vy-hero', 1200, 600, true);
    add_image_size('vy-app-icon', 180, 180, true);
    add_image_size('vy-square', 600, 600, true);
}
add_action('after_setup_theme', 'vy_theme_setup');


// ============================================================
// 1.5 INCLUDE FILES (Load các file hỗ trợ)
// ============================================================
require_once get_template_directory() . '/inc/header-setup.php';
require_once get_template_directory() . '/inc/footer-setup.php';
require_once get_template_directory() . '/inc/woocommerce-setup.php';
require_once get_template_directory() . '/inc/home-options.php';
require_once get_template_directory() . '/inc/search-api.php';


// ============================================================
// 2. ENQUEUE ASSETS (LOAD CSS/JS CÓ ĐIỀU KIỆN)
// ============================================================
function vy_enqueue_scripts()
{
    $ver = '1.0.5';
    $theme_uri = get_template_directory_uri();
    $is_product_archive = is_post_type_archive('product') || is_tax('product_cat');

    // 1. Global Style (Biến CSS, Font, Reset)
    wp_enqueue_style('vy-global-style', $theme_uri . '/css/style.css', [], $ver);

    // 2. Header Assets (Load mọi trang)
    wp_enqueue_style('vy-header-style', $theme_uri . '/css/header.css', [], $ver);
    wp_enqueue_script('vy-header-script', $theme_uri . '/js/header.js', [], $ver, true);

    wp_enqueue_script(
        'vy-header-search',
        $theme_uri . '/js/header-search.js',
        ['jquery'],
        $ver,
        true
    );

    // Truyền config xuống JS
    wp_localize_script('vy-header-search', 'vySearchConfig', [
        'restUrl' => esc_url_raw(rest_url('voya/v1/')),
        'nonce' => wp_create_nonce('wp_rest'),
        'searchUrl' => home_url('/'),
    ]);

    // 3. Footer Assets (Load mọi trang)
    wp_enqueue_style('vy-footer-style', $theme_uri . '/css/footer.css', [], $ver);
    wp_enqueue_script('vy-footer-script', $theme_uri . '/js/footer.js', [], $ver, true);

    // 4. FontAwesome (Dùng cho icon search, menu)
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', [], '6.4.2');

    if (is_page_template('template-apk.php')) {
        wp_enqueue_style('vy-apk-style', $theme_uri . '/css/apk-style.css', [], $ver);
        wp_enqueue_script('vy-apk-script', $theme_uri . '/js/apk-script.js', ['jquery'], $ver, true);
    }

    // 5. Assets cho trang Home
    if (is_front_page()) {
        wp_enqueue_style('vy-home-style', $theme_uri . '/css/home.css', [], $ver);
        wp_enqueue_script('vy-home-script', $theme_uri . '/js/home.js', [], $ver, true);
    }

    if (is_front_page() || is_page_template('template-shop.php') || $is_product_archive) {
        wp_enqueue_style('vy-woocommerce-products', $theme_uri . '/css/woocommerce-products.css', [], $ver);
    }

    if (is_archive() || is_home() || $is_product_archive) {
        wp_enqueue_style('vy-archive-style', $theme_uri . '/css/archive.css', [], $ver);
        wp_enqueue_script('vy-archive-script', $theme_uri . '/js/archive.js', [], $ver, true);
    }

    if (is_singular('product')) {
        wp_enqueue_style('vy-woocommerce-products', $theme_uri . '/css/woocommerce-products.css', [], $ver);
        wp_enqueue_style('vy-single-product', $theme_uri . '/css/single-product.css', ['vy-woocommerce-products'], $ver);
        wp_enqueue_script('vy-single-product', $theme_uri . '/js/single-product.js', [], $ver, true);
    }

    if (is_home() || is_archive() && get_post_type() === 'post' || is_category() || is_tag() || is_author() || is_search()) {
        wp_enqueue_style('vy-archive-blog', $theme_uri . '/css/archive-blog.css', [], $ver);
        wp_enqueue_script('vy-archive-blog', $theme_uri . '/js/archive-blog.js', [], $ver, true);
    }

    if (is_singular('post')) {
        wp_enqueue_style('vy-archive-blog', $theme_uri . '/css/archive-blog.css', [], $ver); // widget styles
        wp_enqueue_style('vy-single-blog', $theme_uri . '/css/single-blog.css', ['vy-archive-blog'], $ver);
        wp_enqueue_script('vy-single-blog', $theme_uri . '/js/single-blog.js', [], $ver, true);
    }

    if (is_home()) {
        wp_enqueue_style('vy-archive-blog', $theme_uri . '/css/archive-blog.css', [], $ver);
        wp_enqueue_script('vy-archive-blog', $theme_uri . '/js/archive-blog.js', [], $ver, true);
    }

    if (is_page_template('page-cms.php')) {
        wp_enqueue_style('vy-cms-page', $theme_uri . '/css/cms-page.css', [], $ver);
    }

    if (is_search()) {
        wp_enqueue_style('vy-search-page', $theme_uri . '/css/search.css', [], $ver);
        wp_enqueue_script('vy-archive-js', $theme_uri . '/js/archive.js', ['jquery'], $ver, true);
    }
}
add_action('wp_enqueue_scripts', 'vy_enqueue_scripts');


// Fallback function cho Navigation Menu
function vy_nav_fallback()
{
    echo '<li><a href="' . esc_url(home_url('/')) . '" class="nav-top-link">' . __('Home', 'voya') . '</a></li>';
}

// Filter để thay đổi độ dài nội dung tóm tắt (Excerpt)
function vy_custom_excerpt_length($length)
{
    return 20;
}
add_filter('excerpt_length', 'vy_custom_excerpt_length', 999);

// Woo archive: 10 products per page
function vy_set_product_archive_posts_per_page($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_post_type_archive('product') || $query->is_tax('product_cat')) {
        $query->set('posts_per_page', 10);
    }
}
add_action('pre_get_posts', 'vy_set_product_archive_posts_per_page');


/**
 * Pretty URLs for Woo product categories:
 * /game, /app, /game/page/2, /app/page/2
 */
function vy_product_cat_custom_rewrite_rules()
{
    add_rewrite_rule('^(game|app)/?$', 'index.php?product_cat=$matches[1]', 'top');
    add_rewrite_rule('^(game|app)/page/([0-9]{1,})/?$', 'index.php?product_cat=$matches[1]&paged=$matches[2]', 'top');
    add_rewrite_rule('^(game|app)/([^/]+)/?$', 'index.php?product_cat=$matches[2]', 'top');
    add_rewrite_rule('^(game|app)/([^/]+)/page/([0-9]{1,})/?$', 'index.php?product_cat=$matches[2]&paged=$matches[3]', 'top');
    add_rewrite_rule('^\\((game|app)\\)/?$', 'index.php?product_cat=$matches[1]', 'top');
    add_rewrite_rule('^\\((game|app)\\)/page/([0-9]{1,})/?$', 'index.php?product_cat=$matches[1]&paged=$matches[2]', 'top');
}
add_action('init', 'vy_product_cat_custom_rewrite_rules');

/**
 * Canonical redirect for malformed links like /(game)/ or /(app)/
 */
function vy_archive_canonical_redirect()
{
    if (is_admin()) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    if (!$request_uri) {
        return;
    }

    if (preg_match('#/\((game|app)\)/#i', $request_uri, $m)) {
        $target = home_url('/' . strtolower($m[1]) . '/');
        wp_safe_redirect($target, 301);
        exit;
    }
}
add_action('template_redirect', 'vy_archive_canonical_redirect');

remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);