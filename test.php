<?php
require_once get_template_directory() . '/inc/theme-options/vieproxy-theme-options-loader.php';
require_once get_template_directory() . '/inc/theme-options/page/page-contact.php';
require_once get_template_directory() . '/inc/theme-options/page/page-archive-partner.php';
require_once get_template_directory() . '/inc/vieproxy-cart.php';
require_once get_template_directory() . '/inc/vieproxy-payment.php';
require_once get_template_directory() . '/inc/vieproxy-send-email.php';

require_once get_template_directory() . '/vendor/autoload.php';
require_once get_template_directory() . '/inc/theme-options/api-user.php';
require_once get_template_directory() . '/inc/theme-options/functions-account.php';


// Tắt email WC mặc định
add_filter('woocommerce_email_enabled_customer_processing_order', '__return_false');
add_filter('woocommerce_email_enabled_customer_completed_order', '__return_false');
add_filter('woocommerce_email_enabled_new_order', '__return_false');

// ════════════════════════════════════════════════════════════════════════
// CẤU HÌNH THÔNG TIN NGÂN HÀNG CHO VIETQR
// ════════════════════════════════════════════════════════════════════════
function vieproxy_register_bank_options()
{
    // Mã ngân hàng theo chuẩn VietQR (xem danh sách tại img.vietqr.io/ui/list-bank.html)
    // TPBank = TPB | VCB = Vietcombank | MB = MBBank | TCB = Techcombank
    add_option('vieproxy_bank_id', 'TPB');
    add_option('vieproxy_bank_account', '07346143101');
    add_option('vieproxy_bank_name', 'LE NGUYEN HOAI PHUC'); // Tên IN HOA, không dấu
    add_option('vieproxy_bank_display_name', 'TPBank');              // Tên hiển thị trên UI
}
add_action('after_setup_theme', 'vieproxy_register_bank_options');
// ════════════════════════════════════════════════════════════════════════


// Enqueue assets
function vieproxy_theme_enqueue_assets()
{
    wp_enqueue_style('main-style', get_stylesheet_directory_uri() . '/style.css', array(), filemtime(get_stylesheet_directory() . '/style.css'));
    wp_enqueue_style('font-icon', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css', array(), '1.0', 'all');

    $global_css = ['header', 'footer'];
    foreach ($global_css as $file) {
        wp_enqueue_style("vieproxy-{$file}", get_template_directory_uri() . "/css/{$file}.css", array(), filemtime(get_stylesheet_directory() . "/css/{$file}.css"));
    }

    wp_enqueue_script('vieproxy-header', get_template_directory_uri() . '/js/header.js', array('jquery'), filemtime(get_template_directory() . '/js/header.js'), true);

    // ── Cart Badge (load trên mọi trang) ─────────────────────────────────
    wp_enqueue_script('vieproxy-cart-badge', get_template_directory_uri() . '/js/cart-badge.js', array('jquery'), filemtime(get_template_directory() . '/js/cart-badge.js'), true);
    wp_localize_script('vieproxy-cart-badge', 'vieproxyBadge', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);

    // ── Homepage ──────────────────────────────────────────────────────────
    if (is_front_page() || is_home()) {
        wp_enqueue_style('vieproxy-home', get_template_directory_uri() . '/css/home.css', array(), filemtime(get_stylesheet_directory() . '/css/home.css'));
        wp_enqueue_style('vieproxy-pricing-card', get_template_directory_uri() . '/css/pricing-card.css', array(), filemtime(get_stylesheet_directory() . '/css/pricing-card.css'));

        $home_js = [
            'home-hero-order-widget',
            'home-proxy-list',
            'home-why-choose-slider',
            'home-pricing-slider',
            'home-rating-slider',
            'home-partners-slider',
            'home-faq-accordion',
        ];
        foreach ($home_js as $file) {
            wp_enqueue_script("vieproxy-{$file}", get_template_directory_uri() . "/js/{$file}.js", array('jquery'), filemtime(get_template_directory() . "/js/{$file}.js"), true);
        }

        // Localize vpHomeActions vào home-proxy-list.js (cart + buy now buttons)
        $cart_page = get_page_by_path('gio-hang');
        $current_user = wp_get_current_user();
        wp_localize_script('vieproxy-home-proxy-list', 'vpHomeActions', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'cart_url' => $cart_page ? get_permalink($cart_page) : home_url('/gio-hang'),
            'login_url' => home_url('/dang-nhap'),
            'is_logged_in' => is_user_logged_in() ? '1' : '0',
            'user_email' => is_user_logged_in() ? $current_user->user_email : '',
        ]);
    }

    // ── Single Product ────────────────────────────────────────────────────
    if (is_singular('product')) {
        wp_enqueue_style('vieproxy-single-product', get_template_directory_uri() . '/css/single-product.css', array(), filemtime(get_stylesheet_directory() . '/css/single-product.css'));
        wp_enqueue_style('vieproxy-pricing-card', get_template_directory_uri() . '/css/pricing-card.css', array(), filemtime(get_stylesheet_directory() . '/css/pricing-card.css'));

        $single_product_js = [
            'single-product',
            'single-product-related-slider',
            'single-product-toc-align',
            'single-product-add-to-cart',
        ];
        foreach ($single_product_js as $file) {
            wp_enqueue_script("vieproxy-{$file}", get_template_directory_uri() . "/js/{$file}.js", array('jquery'), filemtime(get_template_directory() . "/js/{$file}.js"), true);
        }

        $cart_page = get_page_by_path('gio-hang');
        $current_user = wp_get_current_user();
        wp_localize_script('vieproxy-single-product-add-to-cart', 'vieproxyConfig', [
            'vndRate' => (float) get_option('vieproxy_usd_rate', 25000),
            'ajax_url' => admin_url('admin-ajax.php'),
            'cart_url' => $cart_page ? get_permalink($cart_page) : home_url('/gio-hang'),
            'product_id' => get_the_ID(),
            'is_logged_in' => is_user_logged_in() ? '1' : '0',
            'user_email' => is_user_logged_in() ? $current_user->user_email : '',
            'login_url' => home_url('/dang-nhap'),
        ]);
    }

    // ── Blog Archive ──────────────────────────────────────────────────────
    if (is_page_template('page-blog.php') || get_query_var('vp_blog_cat')) {
        wp_enqueue_style('vieproxy-page-blog', get_template_directory_uri() . '/css/page-blog.css', array(), filemtime(get_stylesheet_directory() . '/css/page-blog.css'));
        wp_enqueue_script('vieproxy-page-blog', get_template_directory_uri() . '/js/page-blog.js', array('jquery'), filemtime(get_template_directory() . '/js/page-blog.js'), true);
    }

    // ── Single Blog Post ──────────────────────────────────────────────────
    if (is_singular('post')) {
        wp_enqueue_style('vieproxy-single-blog', get_template_directory_uri() . '/css/single-blog.css', array(), filemtime(get_stylesheet_directory() . '/css/single-blog.css'));
        wp_enqueue_script('vieproxy-single-blog', get_template_directory_uri() . '/js/single-blog.js', array('jquery'), filemtime(get_template_directory() . '/js/single-blog.js'), true);
    }

    if (is_page_template('page-privacy-policy.php') || is_page_template('page-terms-of-service.php')) {
        wp_enqueue_style(
            'info-pages-style',
            get_template_directory_uri() . '/css/info-pages.css',
            array(),
            filemtime(get_stylesheet_directory() . '/css/info-pages.css') // ← filemtime
        );
        wp_enqueue_script(
            'info-pages-script',
            get_template_directory_uri() . '/js/info-pages.js',
            array(),
            filemtime(get_template_directory() . '/js/info-pages.js'), // ← filemtime
            true
        );
    }



    if (is_page_template('page-archive-partners.php')) {
        wp_enqueue_style(
            'archive-partners',
            get_template_directory_uri() . '/css/archive-partners.css',
            [],
            filemtime(get_stylesheet_directory() . '/css/archive-partners.css')
        );
    }

    if (get_query_var('partners_archive')) {
        wp_enqueue_style(
            'archive-partners',
            get_template_directory_uri() . '/css/archive-partners.css',
            [],
            filemtime(get_stylesheet_directory() . '/css/archive-partners.css')
        );
    }


    $is_single_partner = isset($_SERVER['REQUEST_URI']) &&
        preg_match('#/doi-tac/[^/]+/?(\?.*)?$#', $_SERVER['REQUEST_URI']);

    if ($is_single_partner) {
        wp_enqueue_style(
            'single-partner',
            get_template_directory_uri() . '/css/single-partner.css',
            [],
            filemtime(get_stylesheet_directory() . '/css/single-partner.css')
        );
    }

    wp_enqueue_style(
        'speed-dial',
        get_template_directory_uri() . '/css/speed-dial.css',
        array(),
        filemtime(get_stylesheet_directory() . '/css/speed-dial.css')
    );

    wp_enqueue_script(
        'speed-dial',
        get_template_directory_uri() . '/js/speed-dial.js',
        array(),
        filemtime(get_template_directory() . '/js/speed-dial.js'),
        true
    );

    wp_enqueue_style(
        'back-to-top',
        get_template_directory_uri() . '/css/back-to-top.css',
        array(),
        filemtime(get_stylesheet_directory() . '/css/back-to-top.css')
    );

    wp_enqueue_script(
        'back-to-top',
        get_template_directory_uri() . '/js/back-to-top.js',
        array(),
        filemtime(get_template_directory() . '/js/back-to-top.js'),
        true
    );

    // Reading Progress Bar 
    $show_progress = (
        is_page_template('page-privacy-policy.php') ||
        is_page_template('page-terms-of-service.php') ||
        is_page_template('page-contact.php') ||
        is_single()

    );

    if ($show_progress) {
        wp_enqueue_style(
            'reading-progress',
            get_template_directory_uri() . '/css/reading-progress.css',
            array(),
            filemtime(get_stylesheet_directory() . '/css/reading-progress.css')
        );
        wp_enqueue_script(
            'reading-progress',
            get_template_directory_uri() . '/js/reading-progress.js',
            array(),
            filemtime(get_template_directory() . '/js/reading-progress.js'),
            true
        );
    }

    if (is_page_template('page-about.php')) {

        wp_enqueue_style(
            'info-pages-style',
            get_template_directory_uri() . '/css/info-pages.css',
            [],
            filemtime(get_stylesheet_directory() . '/css/info-pages.css')
        );

        wp_enqueue_style(
            'vieproxy-about',
            get_template_directory_uri() . '/css/about.css',
            ['info-pages-style'],
            filemtime(get_stylesheet_directory() . '/css/about.css')
        );

        wp_enqueue_script(
            'info-pages-script',
            get_template_directory_uri() . '/js/info-pages.js',
            [],
            filemtime(get_template_directory() . '/js/info-pages.js'),
            true
        );

        wp_enqueue_script(
            'vieproxy-about',
            get_template_directory_uri() . '/js/about.js',
            ['info-pages-script'],
            filemtime(get_template_directory() . '/js/about.js'),
            true
        );
    }

    // ── Cart Page ─────────────────────────────────────────────────────────
    if (is_page_template('page-cart.php')) {
        wp_enqueue_style('vieproxy-cart', get_template_directory_uri() . '/css/cart.css', array(), filemtime(get_stylesheet_directory() . '/css/cart.css'));
        wp_enqueue_script('vieproxy-cart', get_template_directory_uri() . '/js/cart.js', array('jquery'), filemtime(get_template_directory() . '/js/cart.js'), true);
        wp_enqueue_script('vieproxy-cart-login-redirect', get_template_directory_uri() . '/js/cart-login-redirect.js', array(), filemtime(get_template_directory() . '/js/cart-login-redirect.js'), true);
        $current_user = wp_get_current_user();
        wp_localize_script('vieproxy-cart', 'vieproxyCart', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'usd_rate' => (float) get_option('vieproxy_usd_rate', 25000),
            'is_logged_in' => is_user_logged_in() ? '1' : '0',
            'user_email' => is_user_logged_in() ? $current_user->user_email : '',
            'login_url' => home_url('/dang-nhap'),
        ]);
    }

    // ── Payment Page ──────────────────────────────────────────────────────
    if (is_page_template('page-payment.php')) {
        wp_enqueue_style(
            'vieproxy-payment',
            get_template_directory_uri() . '/css/payment.css',
            array(),
            filemtime(get_stylesheet_directory() . '/css/payment.css')
        );
        // jQuery đã có sẵn toàn site, payment page dùng inline script trong template
    }

    // ── 404 Error Page ────────────────────────────────────────────────────
    if (is_404()) {
        wp_enqueue_style('vieproxy-404', get_template_directory_uri() . '/css/404.css', array(), filemtime(get_stylesheet_directory() . '/css/404.css'));
        wp_enqueue_script('vieproxy-404', get_template_directory_uri() . '/js/404.js', array('jquery'), filemtime(get_template_directory() . '/js/404.js'), true);

        $shop_url = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/');
        wp_localize_script('vieproxy-404', 'vieproxy404', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'shop_url' => esc_url($shop_url),
        ));
    }

    if (is_page_template('page-sign-in.php')) {
        wp_enqueue_style(
            'vieproxy-sign-in',
            get_template_directory_uri() . '/css/sign-in.css',
            [],
            filemtime(get_stylesheet_directory() . '/css/sign-in.css')
        );
        wp_enqueue_script(
            'vieproxy-sign-in',
            get_template_directory_uri() . '/js/sign-in.js',
            [],
            filemtime(get_template_directory() . '/js/sign-in.js'),
            true // footer
        );
    }

    if (is_page_template('page-register.php')) {
        wp_enqueue_style('vieproxy-register', get_template_directory_uri() . '/css/register.css', [], filemtime(get_stylesheet_directory() . '/css/register.css'));
        wp_enqueue_script('vieproxy-register', get_template_directory_uri() . '/js/register.js', [], filemtime(get_template_directory() . '/js/register.js'), true);
    }

    if (is_page_template('page-forgot-password.php')) {
        wp_enqueue_style('vieproxy-fp', get_template_directory_uri() . '/css/forgot-password.css', [], filemtime(get_stylesheet_directory() . '/css/forgot-password.css'));
        wp_enqueue_script('vieproxy-fp', get_template_directory_uri() . '/js/forgot-password.js', [], filemtime(get_template_directory() . '/js/forgot-password.js'), true);
    }

    $account_pages = ['account', 'profile', 'change-password', 'purchase-history', 'my-proxies'];

    if (is_page($account_pages)) {

        wp_enqueue_style(
            'vieproxy-account',
            get_template_directory_uri() . '/css/account.css',
            [],
            filemtime(get_stylesheet_directory() . '/css/account.css')
        );

        wp_enqueue_script(
            'vieproxy-account',
            get_template_directory_uri() . '/js/account.js',
            [],
            filemtime(get_template_directory() . '/js/account.js'),
            true
        );

        // ── Enqueue sub-page JS — version = hash nội dung file (tự động cache-bust) ──
        $pages_js = ['purchase-history', 'my-proxies', 'profile', 'changepass'];
        foreach ($pages_js as $name) {
            $path = get_template_directory() . '/js/pages/' . $name . '.js';
            if (file_exists($path)) {
                wp_enqueue_script(
                    'vieproxy-page-' . $name,
                    get_template_directory_uri() . '/js/pages/' . $name . '.js',
                    [],
                    substr(md5_file($path), 0, 8), // hash 8 ký tự — đổi ngay khi file thay đổi
                    true
                );
            }
        }
    }

    add_filter('template_include', function ($template) {

        if (!is_page())
            return $template;

        $slug = get_post_field('post_name', get_queried_object_id());

        $spa_pages = ['account', 'profile', 'change-password', 'purchase-history', 'my-proxies'];

        if (in_array($slug, $spa_pages)) {
            $t = locate_template('page-account.php');
            if ($t)
                return $t;
        }

        return $template;

    });

    if (is_author()) {
        wp_enqueue_style('vieproxy-page-blog', get_template_directory_uri() . '/css/page-blog.css', array(), filemtime(get_stylesheet_directory() . '/css/page-blog.css'));
        wp_enqueue_style('vieproxy-page-author', get_template_directory_uri() . '/css/page-author.css', array('vieproxy-page-blog'), filemtime(get_stylesheet_directory() . '/css/page-author.css'));
        wp_enqueue_script('vieproxy-page-author', get_template_directory_uri() . '/js/page-author.js', array(), filemtime(get_template_directory() . '/js/page-author.js'), true);
    }


}
add_action('wp_enqueue_scripts', 'vieproxy_theme_enqueue_assets');

// ── 404 Page: Fast Product Search AJAX ────────────────────────────────────
function vieproxy_404_product_search()
{
    $query = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';

    if (strlen($query) < 2) {
        wp_send_json([]);
    }

    $product_ids = get_posts([
        'post_type' => 'product',
        'post_status' => 'publish',
        's' => $query,
        'posts_per_page' => 6,
        'fields' => 'ids',
        'no_found_rows' => true,
    ]);

    $results = [];
    foreach ($product_ids as $id) {
        $product = wc_get_product($id);
        if (!$product)
            continue;

        $thumb = get_the_post_thumbnail_url($id, 'thumbnail');
        if (!$thumb) {
            $thumb = wc_placeholder_img_src('thumbnail');
        }

        $results[] = [
            'id' => $id,
            'name' => $product->get_name(),
            'price_html' => wp_strip_all_tags($product->get_price_html()),
            'thumb' => $thumb,
            'url' => get_permalink($id),
        ];
    }

    wp_send_json($results);
}
add_action('wp_ajax_vieproxy_product_search', 'vieproxy_404_product_search');
add_action('wp_ajax_nopriv_vieproxy_product_search', 'vieproxy_404_product_search');

// edit url canonical page home
add_filter('rank_math/frontend/canonical', function ($canonical) {
    if (is_front_page()) {
        return 'https://vieproxy.com/';
    }
    return $canonical;
});

// Thêm title vào header
function vieproxy_theme_setup()
{
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'vieproxy_theme_setup');

// Thêm hàm cho phép upload ảnh thumnail cho bài post
add_theme_support('post-thumbnails');

// Đăng ký menus
function vieproxy_register_menus()
{
    register_nav_menus(array(
        'header_menu' => __('Header Menu', 'vieproxy'),
        'footer_col_danh_muc' => __('Footer - Danh mục hàng đầu', 'vieproxy'),
        'footer_col_proxy' => __('Footer - Proxy', 'vieproxy'),
        'footer_col_dich_vu' => __('Footer - Dịch vụ và tiêu chuẩn', 'vieproxy'),
        'footer_col_tro_giup' => __('Footer - Trung tâm trợ giúp', 'vieproxy'),
    ));
}
add_action('after_setup_theme', 'vieproxy_register_menus');

// rewrite rules for partners
// ── Register rewrite rule cho /doi-tac/{slug}/ ────────────────────────────
add_action('init', function () {
    add_rewrite_rule(
        '^doi-tac/([^/]+)/?$',
        'index.php?partner_slug=$matches[1]',
        'top'
    );
});

// Đăng ký custom query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'partner_slug';
    $vars[] = 'partners_archive';
    $vars[] = 'vp_partner_cat';
    return $vars;
});

// Load template khi có partner_slug
add_filter('template_include', function ($template) {
    $slug = get_query_var('partner_slug');
    if ($slug) {
        $custom = locate_template('page-single-partner.php');
        if ($custom) {
            return $custom;
        }
    }
    return $template;
});

// Ensure /doi-tac always resolves to archive partners template (even without WP page)
add_action('init', function () {
    add_rewrite_rule(
        '^doi-tac/page/([0-9]+)/?$',
        'index.php?partners_archive=1&paged=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^doi-tac/?$',
        'index.php?partners_archive=1',
        'top'
    );
});

add_filter('template_include', function ($template) {
    if ((int) get_query_var('partners_archive') === 1) {
        $custom = locate_template('page-archive-partners.php');
        if ($custom) {
            return $custom;
        }
    }
    return $template;
}, 20);

// Prevent WP from marking /doi-tac and /doi-tac/page/{n} as 404.
add_filter('pre_handle_404', function ($preempt, $wp_query) {
    if ((int) get_query_var('partners_archive') !== 1) {
        return $preempt;
    }

    if ($wp_query instanceof WP_Query) {
        $wp_query->is_404 = false;
    }

    return true;
}, 10, 2);

// Keep custom partners archive route as a valid 200 page (avoid 404 title/status).
add_action('template_redirect', function () {
    if ((int) get_query_var('partners_archive') !== 1) {
        return;
    }

    global $wp_query;
    if ($wp_query instanceof WP_Query) {
        $wp_query->is_404 = false;
    }

    status_header(200);
}, 0);

// Prevent canonical redirects from rewriting partners pagination to unrelated URLs.
add_filter('redirect_canonical', function ($redirect_url) {
    if ((int) get_query_var('partners_archive') === 1) {
        return false;
    }
    return $redirect_url;
}, 10, 2);

// Append "Trang N" for custom paginated routes so title/OG/Twitter stay accurate.
if (!function_exists('vp_append_paged_suffix')) {
    function vp_append_paged_suffix($title, $paged)
    {
        $title = trim((string) $title);
        $paged = (int) $paged;
        if ($paged <= 1 || $title === '') {
            return $title;
        }

        if (preg_match('/(?:trang|page)\s+\d+/iu', $title)) {
            return $title;
        }

        return $title . ' - Trang ' . $paged;
    }
}

if (!function_exists('vp_is_custom_paginated_route')) {
    function vp_is_custom_paginated_route()
    {
        return ((int) get_query_var('partners_archive') === 1)
            || (bool) get_query_var('vp_blog_cat')
            || (bool) get_query_var('vp_proxy_cat');
    }
}

add_filter('document_title_parts', function ($parts) {
    if (!vp_is_custom_paginated_route()) {
        return $parts;
    }

    $paged = max(1, (int) get_query_var('paged'));
    if ($paged <= 1) {
        return $parts;
    }

    if (!empty($parts['title'])) {
        $parts['title'] = vp_append_paged_suffix($parts['title'], $paged);
    }

    return $parts;
}, 20);

add_filter('rank_math/frontend/title', function ($title) {
    if (!vp_is_custom_paginated_route()) {
        return $title;
    }

    $paged = max(1, (int) get_query_var('paged'));
    return vp_append_paged_suffix($title, $paged);
});

add_filter('rank_math/opengraph/facebook/title', function ($title) {
    if (!vp_is_custom_paginated_route()) {
        return $title;
    }

    $paged = max(1, (int) get_query_var('paged'));
    return vp_append_paged_suffix($title, $paged);
});

add_filter('rank_math/opengraph/twitter/title', function ($title) {
    if (!vp_is_custom_paginated_route()) {
        return $title;
    }

    $paged = max(1, (int) get_query_var('paged'));
    return vp_append_paged_suffix($title, $paged);
});

// ── CORS cho VieProxy REST API ────────────────────────────────────────────
add_action('rest_api_init', function () {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function ($value) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        return $value;
    });
}, 15);

// ── 1. Đăng ký query var ─────────────────────────────────────────────────
add_filter('query_vars', function ($vars) {
    $vars[] = 'vp_proxy_cat';
    return $vars;
});

add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'vp_blog_cat';
    return $vars;
});

add_action('init', function () {
    // Lấy slugs của sub-cats đối tác để exclude khỏi blog cat rewrite
    $doi_tac = get_category_by_slug('doi-tac');
    $partner_sub_slugs = [];
    if ($doi_tac && !is_wp_error($doi_tac)) {
        $subs = get_terms([
            'taxonomy' => 'category',
            'parent' => (int) $doi_tac->term_id,
            'hide_empty' => false,
            'fields' => 'slugs',
        ]);
        if (!is_wp_error($subs))
            $partner_sub_slugs = $subs;
    }

    $excluded = array_merge(['doi-tac', 'partners', 'partner'], $partner_sub_slugs);

    $cats = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => false,
        'fields' => 'slugs',
    ]);
    if (empty($cats) || is_wp_error($cats))
        return;

    foreach ($cats as $slug) {
        if (in_array($slug, $excluded, true))
            continue; // ← exclude cả sub-cat đối tác
        add_rewrite_rule(
            '^' . preg_quote($slug, '/') . '/?$',
            'index.php?vp_blog_cat=' . $slug,
            'top'
        );
    }
});
add_filter('template_include', function ($template) {
    if (get_query_var('vp_blog_cat')) {
        $t = locate_template('page-blog.php');
        if ($t)
            return $t;
    }
    return $template;
});

// Xóa Cate Blog Mặc Định
add_action('init', function () {
    $new_default_slug = 'kien-thuc-proxy'; // slug category bạn muốn làm mặc định
    $term = get_category_by_slug($new_default_slug);

    if ($term && get_option('default_category') != $term->term_id) {
        update_option('default_category', $term->term_id);
    }

    $uncat = get_category_by_slug('uncategorized');
    if ($uncat) {
        wp_delete_term($uncat->term_id, 'category');
    }
});

// Inject robots meta for partners archive and single partner detail pages
add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }

    if (is_page_template('page-archive-partners.php') || get_query_var('partner_slug') || get_query_var('partners_archive')) {
        echo '<meta name="robots" content="nofollow, noindex" />' . "\n";
    }
}, 1);

// Ensure "Doi Tac" category exists for internal partner data source
add_action('init', function () {
    if (!term_exists('doi-tac', 'category')) {
        wp_insert_term('Đối tác', 'category', [
            'slug' => 'doi-tac',
            'description' => 'Category for partner pages/data.',
        ]);
    }
}, 9);


if (!function_exists('vp_get_partner_category_ids')) {
    function vp_get_partner_category_ids()
    {
        static $partner_cat_ids = null;
        if ($partner_cat_ids !== null)
            return $partner_cat_ids;

        $partner_cat_ids = [];
        $parent = get_category_by_slug('doi-tac');
        if (!$parent || is_wp_error($parent))
            return $partner_cat_ids;

        $parent_id = (int) $parent->term_id;
        $partner_cat_ids[] = $parent_id;

        // ── Include sub-cats để exclude đúng khỏi blog ───────────
        $sub_cats = get_terms([
            'taxonomy' => 'category',
            'parent' => $parent_id,
            'hide_empty' => false,
            'fields' => 'ids',
        ]);
        if (!is_wp_error($sub_cats) && !empty($sub_cats)) {
            $partner_cat_ids = array_merge(
                $partner_cat_ids,
                array_map('intval', $sub_cats)
            );
        }

        $partner_cat_ids = array_values(array_unique($partner_cat_ids));
        return $partner_cat_ids;
    }
}

// Exclude Partner category from blog/category post listings by default
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query instanceof WP_Query) {
        return;
    }

    if ((bool) $query->get('vp_allow_partner')) {
        return;
    }

    $partner_cat_ids = vp_get_partner_category_ids();
    if (empty($partner_cat_ids)) {
        return;
    }

    $post_type = $query->get('post_type');
    $is_post_query = ($post_type === 'post' || (is_array($post_type) && in_array('post', $post_type, true)) || empty($post_type));

    if (!$is_post_query) {
        return;
    }

    // Keep partner posts available on dedicated partner routes only.
    if (get_query_var('partner_slug') || is_page_template('page-archive-partners.php')) {
        return;
    }

    $excluded = $query->get('category__not_in');
    if (!is_array($excluded)) {
        $excluded = empty($excluded) ? [] : [(int) $excluded];
    }
    foreach ($partner_cat_ids as $partner_cat_id) {
        if (!in_array($partner_cat_id, $excluded, true)) {
            $excluded[] = $partner_cat_id;
        }
    }
    $query->set('category__not_in', $excluded);
});

// Partner data inputs in post editor
add_action('add_meta_boxes', function () {
    add_meta_box(
        'vp_partner_data_box',
        'Partner Data',
        function ($post) {
            wp_nonce_field('vp_partner_data_nonce_action', 'vp_partner_data_nonce');

            $features_raw = get_post_meta($post->ID, '_vp_partner_features', true);
            ?>
<p><strong>Lưu ý:</strong> Các dữ liệu này dùng cho trang Partner (category slug: <code>doi-tac</code>).</p>
<p>
    <label for="vp_partner_features"><strong>Features Overview</strong></label><br />
    <?php
                wp_editor(
                    $features_raw,
                    'vp_partner_features_editor',
                    [
                        'textarea_name' => 'vp_partner_features',
                        'textarea_rows' => 10,
                        'media_buttons' => false,
                        'teeny' => false,
                    ]
                );
                ?>
</p>
<?php
        },
        'post',
        'normal',
        'default'
    );
});

add_action('save_post_post', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!isset($_POST['vp_partner_data_nonce']) || !wp_verify_nonce($_POST['vp_partner_data_nonce'], 'vp_partner_data_nonce_action')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $features_raw = isset($_POST['vp_partner_features']) ? wp_unslash($_POST['vp_partner_features']) : '';
    update_post_meta($post_id, '_vp_partner_features', wp_kses_post($features_raw));
});

// Use /doi-tac/{slug}/ permalink for posts in partner categories
add_filter('post_link', function ($permalink, $post, $leavename) {
    if (!$post instanceof WP_Post || $post->post_type !== 'post') {
        return $permalink;
    }

    if (!has_category('doi-tac', $post)) {
        return $permalink;
    }

    $slug = $leavename ? '%postname%' : $post->post_name;
    return home_url('/doi-tac/' . $slug . '/');
}, 10, 3);

// Prevent partner posts from rendering with single blog view (/blog/{slug})
add_action('template_redirect', function () {
    // Legacy redirect: /partners/... -> /doi-tac/...
    if (!empty($_SERVER['REQUEST_URI']) && preg_match('#^/partners(?:/|$)#', $_SERVER['REQUEST_URI'])) {
        $target = preg_replace('#^/partners#', '/doi-tac', $_SERVER['REQUEST_URI']);
        wp_safe_redirect(home_url($target), 301);
        exit;
    }

    if (is_category(['doi-tac', 'partners', 'partner'])) {
        wp_safe_redirect(home_url('/doi-tac/'), 301);
        exit;
    }

    if (!is_singular('post')) {
        return;
    }

    $post = get_queried_object();
    if (!$post instanceof WP_Post || !has_category('doi-tac', $post)) {
        return;
    }

    wp_safe_redirect(home_url('/doi-tac/' . $post->post_name . '/'), 301);
    exit;
}, 1);

// Never output category link for partner categories as /blog/category/partners
add_filter('term_link', function ($termlink, $term, $taxonomy) {
    if ($taxonomy !== 'category') {
        return $termlink;
    }

    if (in_array($term->slug, ['doi-tac', 'partners', 'partner'], true)) {
        return home_url('/doi-tac/');
    }

    return $termlink;
}, 10, 3);

// Force Rank Math sitemap URL for partner posts to /doi-tac/{slug}/
add_filter('rank_math/sitemap/entry', function ($entry, $type, $object) {
    if ($type !== 'post') {
        return $entry;
    }

    $post_id = 0;
    $post_slug = '';

    if ($object instanceof WP_Post) {
        $post_id = (int) $object->ID;
        $post_slug = (string) $object->post_name;
    } elseif (is_object($object) && isset($object->ID)) {
        $post_id = (int) $object->ID;
        $post_slug = isset($object->post_name) ? (string) $object->post_name : '';
    } elseif (is_array($object) && isset($object['ID'])) {
        $post_id = (int) $object['ID'];
        $post_slug = isset($object['post_name']) ? (string) $object['post_name'] : '';
    }

    if ($post_id <= 0) {
        return $entry;
    }

    // Support both new and legacy slugs to avoid stale sitemap links.
    if (!has_term(['doi-tac', 'partners', 'partner'], 'category', $post_id)) {
        return $entry;
    }

    if ($post_slug === '') {
        $post_slug = get_post_field('post_name', $post_id);
    }
    if ($post_slug === '') {
        return $entry;
    }

    $entry['loc'] = home_url('/doi-tac/' . $post_slug . '/');
    return $entry;
}, 10, 3);

// Avoid stale Rank Math sitemap cache when admin cannot Save settings.
add_filter('rank_math/sitemap/enable_caching', '__return_false');

// One-time migrate old partner categories (partners/partner) to doi-tac
add_action('init', function () {
    if (get_option('vp_partner_cat_migrated_to_doi_tac') === '1') {
        return;
    }

    $new = get_category_by_slug('doi-tac');
    if (!$new || is_wp_error($new)) {
        return;
    }

    $new_id = (int) $new->term_id;
    $old_ids = [];
    foreach (['partners', 'partner'] as $old_slug) {
        $old = get_category_by_slug($old_slug);
        if ($old && !is_wp_error($old)) {
            $old_ids[] = (int) $old->term_id;
        }
    }

    if (empty($old_ids)) {
        update_option('vp_partner_cat_migrated_to_doi_tac', '1', false);
        return;
    }

    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'any',
        'posts_per_page' => -1,

        'fields' => 'ids',
        'category__in' => $old_ids,
        'no_found_rows' => true,
    ]);

    foreach ($posts as $post_id) {
        $cat_ids = wp_get_post_categories($post_id);
        if (!in_array($new_id, $cat_ids, true)) {
            $cat_ids[] = $new_id;
        }
        $cat_ids = array_values(array_unique(array_filter(array_map('intval', $cat_ids))));
        wp_set_post_categories($post_id, $cat_ids, false);
    }

    update_option('vp_partner_cat_migrated_to_doi_tac', '1', false);
}, 11);

// rewite rules for sub-cate 
// 
// ── Rewrite rules cho sub-cats của doi-tac → clean URL /slug/ ─────────
add_action('init', function () {
    $doi_tac = get_category_by_slug('doi-tac');
    if (!$doi_tac || is_wp_error($doi_tac))
        return;

    $sub_cats = get_terms([
        'taxonomy' => 'category',
        'parent' => (int) $doi_tac->term_id,
        'hide_empty' => false,
        'fields' => 'slugs',
    ]);
    if (empty($sub_cats) || is_wp_error($sub_cats))
        return;

    foreach ($sub_cats as $slug) {
        // Paged: /tri-tue-nhan-tao/page/2/
        add_rewrite_rule(
            '^' . preg_quote($slug, '/') . '/page/([0-9]+)/?$',
            'index.php?partners_archive=1&vp_partner_cat=' . $slug . '&paged=$matches[1]',
            'top'
        );
        // Base: /tri-tue-nhan-tao/
        add_rewrite_rule(
            '^' . preg_quote($slug, '/') . '/?$',
            'index.php?partners_archive=1&vp_partner_cat=' . $slug,
            'top'
        );
    }
}, 8);


// Build TOC + normalized content HTML from editor content.
if (!function_exists('vp_build_toc_data_from_content')) {
    function vp_build_toc_data_from_content($content, $id_prefix = 'vp-content')
    {
        $data = [
            'has_toc' => false,
            'toc' => '',
            'content' => (string) $content,
        ];

        if (!is_string($content) || trim(wp_strip_all_tags($content)) === '') {
            return $data;
        }

        if (!class_exists('DOMDocument')) {
            return $data;
        }

        $dom = new DOMDocument();
        $wrapped_html = '<div id="vp-toc-root">' . $content . '</div>';

        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?>' . $wrapped_html,
LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
);
libxml_clear_errors();

if (!$loaded) {
return $data;
}

$root = $dom->getElementById('vp-toc-root');
if (!$root) {
return $data;
}

$xpath = new DOMXPath($dom);
$headings = $xpath->query('.//h2|.//h3|.//h4', $root);
if (!$headings || $headings->length === 0) {
$content_html = '';
foreach ($root->childNodes as $child_node) {
$content_html .= $dom->saveHTML($child_node);
}
$data['content'] = $content_html;
return $data;
}

$used_ids = [];
$toc_items = [];
$counter = 1;

foreach ($headings as $heading) {
if (!$heading instanceof DOMElement) {
continue;
}

$tag_name = strtolower($heading->tagName);
$level = (int) str_replace('h', '', $tag_name);
if (!in_array($level, [2, 3, 4], true)) {
continue;
}

$text = trim(wp_strip_all_tags($heading->textContent));
if ($text === '') {
continue;
}

$raw_id = (string) $heading->getAttribute('id');
$base_id = sanitize_title($raw_id !== '' ? $raw_id : $text);
if ($base_id === '') {
$base_id = sanitize_title($id_prefix) . '-heading-' . $counter;
}

$heading_id = $base_id;
$suffix = 2;
while (in_array($heading_id, $used_ids, true)) {
$heading_id = $base_id . '-' . $suffix;
$suffix++;
}
$used_ids[] = $heading_id;

if ($raw_id !== $heading_id) {
$heading->setAttribute('id', $heading_id);
}

$item_class = 'vp-page-toc__item';
if ($level >= 3) {
$item_class .= ' is-sub';
}
if ($level >= 4) {
$item_class .= ' is-deep';
}

$toc_items[] = sprintf(
'<li class="%1$s"><a href="#%2$s">%3$s</a></li>',
esc_attr($item_class),
esc_attr($heading_id),
esc_html($text)
);

$counter++;
}

if (!empty($toc_items)) {
$data['has_toc'] = true;
$data['toc'] = '<ul class="vp-page-toc__list">' . implode('', $toc_items) . '</ul>';
}

$content_html = '';
foreach ($root->childNodes as $child_node) {
$content_html .= $dom->saveHTML($child_node);
}
$data['content'] = $content_html;

return $data;
}
}

if (!function_exists('vp_get_proxy_cat_content_data')) {
function vp_get_proxy_cat_content_data()
{
$empty = ['has_toc' => false, 'toc' => '', 'content' => ''];

$term = get_term_by('slug', 'mua-proxy', 'product_cat');
if (!$term || is_wp_error($term)) {
return $empty;
}

$raw = get_term_meta($term->term_id, '_vp_cat_seo_content', true);
if (empty(trim(wp_strip_all_tags((string) $raw)))) {
return $empty;
}

$rendered = apply_filters('the_content', wp_kses_post($raw));
if (function_exists('vp_build_toc_data_from_content')) {
return vp_build_toc_data_from_content($rendered, 'mua-proxy');
}

return ['has_toc' => false, 'toc' => '', 'content' => $rendered];
}
}

// ── Hiển thị field wysiwyg khi EDIT category đã tồn tại ──────────────────
add_action('product_cat_edit_form_fields', 'vp_cat_seo_content_edit_field', 20);

function vp_cat_seo_content_edit_field(WP_Term $term): void
{
$content = (string) get_term_meta($term->term_id, '_vp_cat_seo_content', true);
$editor_id = 'vp_cat_seo_content';
?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="<?php echo esc_attr($editor_id); ?>">
            Nội dung SEO
        </label>
    </th>
    <td>
        <?php wp_nonce_field('vp_save_cat_seo_content', 'vp_cat_seo_nonce'); ?>

        <?php
            wp_editor(
                $content,
                $editor_id,
                [
                    'textarea_name' => 'vp_cat_seo_content',
                    'textarea_rows' => 18,
                    'media_buttons' => true,
                    'teeny' => false,
                    'quicktags' => true,
                ]
            );
            ?>

        <p class="description" style="margin-top: 10px; color: #666;">
            Nội dung bài viết SEO hiển thị bên dưới danh sách sản phẩm (trang <code>/mua-proxy/</code>).<br>
            Dùng heading H2, H3 để tự động sinh mục lục (TOC). Hỗ trợ đầy đủ HTML.
        </p>
    </td>
</tr>
<?php
}

// ── Save term meta ────────────────────────────────────────────────────────
add_action('edited_product_cat', 'vp_save_cat_seo_content', 10, 2);

function vp_save_cat_seo_content(int $term_id, int $tt_id): void
{
    // Verify nonce
    if (
        !isset($_POST['vp_cat_seo_nonce']) ||
        !wp_verify_nonce($_POST['vp_cat_seo_nonce'], 'vp_save_cat_seo_content')
    ) {
        return;
    }

    if (!current_user_can('manage_product_terms')) {
        return;
    }

    $raw = isset($_POST['vp_cat_seo_content'])
        ? wp_unslash($_POST['vp_cat_seo_content'])
        : '';

    $sanitized = wp_kses_post($raw);

    if (!empty(trim(wp_strip_all_tags($sanitized)))) {
        update_term_meta($term_id, '_vp_cat_seo_content', $sanitized);
    } else {
        delete_term_meta($term_id, '_vp_cat_seo_content');
    }
}

// ── Load TinyMCE / media scripts đúng trên trang edit term ───────────────
add_action('admin_enqueue_scripts', function (string $hook): void {
    // WC taxonomy edit page: edit-tags.php với taxonomy=product_cat
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }
    $taxonomy = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';
    if ($taxonomy !== 'product_cat') {
        return;
    }
    wp_enqueue_media();

    // Fix: wp_editor trong term form cần script này để hoạt động
    add_action('admin_print_footer_scripts', '_WP_Editors::enqueue_scripts', 99);
});


add_action('wp_head', function () {
    if (!is_product()) {
        return;
    }

    global $post;

    $service = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        '@id' => get_permalink($post->ID) . '#service',
        'name' => get_the_title($post->ID),
        'description' => wp_strip_all_tags(get_the_excerpt($post->ID) ?: get_the_title($post->ID)),
        'url' => get_permalink($post->ID),
        'serviceType' => 'Proxy Service',
        'provider' => [
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
        ],
    ];

    if (has_post_thumbnail($post->ID)) {
        $image = wp_get_attachment_image_url(get_post_thumbnail_id($post->ID), 'full');
        if ($image) {
            $service['image'] = $image;
        }
    }

    echo '<script type="application/ld+json">' .
        wp_json_encode($service, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        '</script>';
}, 99);

add_filter('template_include', function (string $template): string {
    if (is_tax('product_cat') && is_product_category('mua-proxy')) {
        $custom = locate_template('archive-product.php');
        if ($custom) {
            return $custom;
        }
    }
    return $template;
}, 99);


add_action('wp_enqueue_scripts', function () {
    if (!is_tax('product_cat') || !is_product_category('mua-proxy')) {
        return;
    }

    wp_enqueue_style(
        'vieproxy-archive-product',
        get_template_directory_uri() . '/css/archive-proxies.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/archive-proxies.css')
    );

    wp_enqueue_style(
        'vieproxy-pricing-card',
        get_template_directory_uri() . '/css/pricing-card.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/pricing-card.css')
    );

    wp_enqueue_script(
        'vieproxy-archive-product',
        get_template_directory_uri() . '/js/archive-proxies.js',
        [],
        filemtime(get_template_directory() . '/js/archive-proxies.js'),
        true
    );
}, 99);

add_action('show_user_profile', 'vp_author_avatar_field');
add_action('edit_user_profile', 'vp_author_avatar_field');

function vp_author_avatar_field($user)
{
    $attachment_id = (int) get_user_meta($user->ID, '_vp_author_avatar_id', true);
    $preview_url = $attachment_id > 0
        ? wp_get_attachment_image_url($attachment_id, 'thumbnail')
        : '';
    ?>
<h3>Ảnh tác giả (Vieproxy)</h3>
<table class="form-table">
    <tr>
        <th><label>Ảnh đại diện</label></th>
        <td>
            <?php wp_nonce_field('vp_save_author_avatar', 'vp_author_avatar_nonce'); ?>

            <!-- Hidden: lưu attachment ID -->
            <input type="hidden" id="vp_author_avatar_id" name="vp_author_avatar_id"
                value="<?php echo esc_attr($attachment_id ?: ''); ?>">

            <!-- Preview -->
            <div id="vp-avatar-preview" style="margin-bottom:12px;">
                <?php if ($preview_url): ?>
                <img id="vp-avatar-img" src="<?php echo esc_url($preview_url); ?>"
                    style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #e0e0e0;">
                <?php else: ?>
                <img id="vp-avatar-img" src=""
                    style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #e0e0e0;display:none;">
                <?php endif; ?>
            </div>

            <!-- Buttons -->
            <button type="button" id="vp-avatar-upload-btn" class="button button-secondary">
                <?php echo $attachment_id ? 'Đổi ảnh' : 'Chọn ảnh từ thư viện'; ?>
            </button>
            <?php if ($attachment_id): ?>
            <button type="button" id="vp-avatar-remove-btn" class="button" style="margin-left:8px;color:#d63638;">
                Xoá ảnh
            </button>
            <?php else: ?>
            <button type="button" id="vp-avatar-remove-btn" class="button"
                style="margin-left:8px;color:#d63638;display:none;">
                Xoá ảnh
            </button>
            <?php endif; ?>

            <p class="description" style="margin-top:10px;">
                Chọn ảnh từ Media Library hoặc upload mới. Tỉ lệ 1:1 (vuông), tối thiểu 300×300px.
            </p>
        </td>
    </tr>
</table>

<script>
jQuery(document).ready(function($) {
    var mediaFrame;

    $('#vp-avatar-upload-btn').on('click', function(e) {
        e.preventDefault();

        if (mediaFrame) {
            mediaFrame.open();
            return;
        }

        mediaFrame = wp.media({
            title: 'Chọn ảnh đại diện tác giả',
            button: {
                text: 'Dùng ảnh này'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        mediaFrame.on('select', function() {
            var attachment = mediaFrame.state().get('selection').first().toJSON();

            $('#vp_author_avatar_id').val(attachment.id);

            var previewSrc = attachment.sizes && attachment.sizes.thumbnail ?
                attachment.sizes.thumbnail.url :
                attachment.url;

            var $img = $('#vp-avatar-img');
            $img.attr('src', previewSrc).show();
            $('#vp-avatar-remove-btn').show();
            $('#vp-avatar-upload-btn').text('Đổi ảnh');
        });

        mediaFrame.open();
    });

    $('#vp-avatar-remove-btn').on('click', function(e) {
        e.preventDefault();
        $('#vp_author_avatar_id').val('');
        $('#vp-avatar-img').attr('src', '').hide();
        $(this).hide();
        $('#vp-avatar-upload-btn').text('Chọn ảnh từ thư viện');
    });
});
</script>
<?php
}

// Load WP Media scripts trên trang User Profile
add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, ['profile.php', 'user-edit.php'], true)) {
        return;
    }
    wp_enqueue_media();
});

// Save attachment ID
add_action('personal_options_update', 'vp_save_author_avatar_field');
add_action('edit_user_profile_update', 'vp_save_author_avatar_field');

function vp_save_author_avatar_field($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }
    if (!isset($_POST['vp_author_avatar_nonce']) || !wp_verify_nonce($_POST['vp_author_avatar_nonce'], 'vp_save_author_avatar')) {
        return;
    }

    $attachment_id = isset($_POST['vp_author_avatar_id'])
        ? (int) sanitize_text_field($_POST['vp_author_avatar_id'])
        : 0;

    if ($attachment_id > 0) {
        update_user_meta($user_id, '_vp_author_avatar_id', $attachment_id);
    } else {
        delete_user_meta($user_id, '_vp_author_avatar_id');
    }
}


// function custom_change_author_base() {
//     global $wp_rewrite;
//     // Đổi 'author' thành 'tac-gia'
//     $wp_rewrite->author_base = 'tac-gia';
// }
// add_action( 'init', 'custom_change_author_base' );
// 
// Đổi URL đầu ra từ /author/ thành /tac-gia/
add_filter('author_link', 'vp_custom_author_link', 10, 3);
function vp_custom_author_link($link, $author_id, $author_nicename)
{
    return home_url('/tac-gia/' . $author_nicename . '/');
}

// Định tuyến lại đường dẫn /tac-gia/ để WordPress hiểu được
add_filter('author_rewrite_rules', 'vp_custom_author_rewrite_rules');
function vp_custom_author_rewrite_rules($author_rewrite)
{
    $author_rewrite = array(
        'tac-gia/([a-zA-Z0-9\-]+)/page/?([0-9]{1,})/?$' => 'index.php?author_name=$matches[1]&paged=$matches[2]',
        'tac-gia/([a-zA-Z0-9\-]+)/?$' => 'index.php?author_name=$matches[1]',
    );
    return $author_rewrite;
}