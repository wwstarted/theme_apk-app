<?php
/**
 * Header Setup & Customizer Controls
 * File này quản lý cấu hình header và WordPress Customizer
 */

if (!defined('ABSPATH'))
    exit;

// ============================================================
// 1. Customizer - Tất cả controls cho Header
// ============================================================

function vy_header_customize_register($wp_customize)
{
    // Panel: HappyMod Theme Settings
    $wp_customize->add_panel('vy_theme_panel', [
        'title' => __('HappyMod Theme Settings', 'voya'),
        'priority' => 10,
    ]);

    // ========== SECTION: HEADER SETTINGS ==========
    $wp_customize->add_section('vy_header_section', [
        'title' => __('Header Settings', 'voya'),
        'panel' => 'vy_theme_panel',
        'priority' => 10,
    ]);

    // Control: Search Placeholder Text
    $wp_customize->add_setting('vy_search_placeholder', [
        'default' => __('Tìm kiếm', 'voya'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ]);

    $wp_customize->add_control('vy_search_placeholder', [
        'label' => __('Search Placeholder Text', 'voya'),
        'description' => __('Text shown in search input field', 'voya'),
        'section' => 'vy_header_section',
        'type' => 'text',
        'priority' => 10,
    ]);

    // Control: Sticky Header
    $wp_customize->add_setting('vy_sticky_header', [
        'default' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
        'transport' => 'postMessage',
    ]);

    $wp_customize->add_control('vy_sticky_header', [
        'label' => __('Enable Sticky Header', 'voya'),
        'description' => __('Header sẽ fixed khi scroll xuống', 'voya'),
        'section' => 'vy_header_section',
        'type' => 'checkbox',
        'priority' => 20,
    ]);

    // ========== SECTION: MENU SETTINGS ==========
    $wp_customize->add_section('vy_menu_section', [
        'title' => __('Navigation Menu', 'voya'),
        'panel' => 'vy_theme_panel',
        'priority' => 20,
    ]);

    // Info control
    $wp_customize->add_setting('vy_menu_info');
    $wp_customize->add_control('vy_menu_info', [
        'label' => __('Navigation Menu', 'voya'),
        'section' => 'vy_menu_section',
        'type' => 'custom',
        'description' => __('Quản lý navigation tại: Appearance > Menus. Gán menu vào vị trí "Primary Menu".', 'voya'),
    ]);

    // ========== SECTION: COLORS ==========
    $wp_customize->add_section('vy_colors_section', [
        'title' => __('Theme Colors', 'voya'),
        'panel' => 'vy_theme_panel',
        'priority' => 30,
    ]);

    // Control: Primary Accent Color
    $wp_customize->add_setting('vy_primary_color', [
        'default' => '#5eb51c',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'postMessage',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'vy_primary_color', [
        'label' => __('Primary Accent Color (Green)', 'voya'),
        'description' => __('Màu sắc chính của theme', 'voya'),
        'section' => 'vy_colors_section',
        'priority' => 10,
    ]));

    // Control: Header Background Color
    $wp_customize->add_setting('vy_header_bg_color', [
        'default' => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'postMessage',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'vy_header_bg_color', [
        'label' => __('Header Background', 'voya'),
        'section' => 'vy_colors_section',
        'priority' => 20,
    ]));

    // Control: Header Text Color
    $wp_customize->add_setting('vy_header_text_color', [
        'default' => '#333333',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'postMessage',
    ]);

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'vy_header_text_color', [
        'label' => __('Header Text Color', 'voya'),
        'section' => 'vy_colors_section',
        'priority' => 30,
    ]));
}
add_action('customize_register', 'vy_header_customize_register');


// ============================================================
// 2. Output Customizer CSS (Inline)
// ============================================================

function vy_header_customizer_css()
{
    $primary_color = get_theme_mod('vy_primary_color', '#5eb51c');
    $header_bg = get_theme_mod('vy_header_bg_color', '#ffffff');
    $header_text = get_theme_mod('vy_header_text_color', '#333333');
    ?>
    <style type="text/css" id="vy-customizer-css">
        :root {
            --vy-accent:
                <?php echo esc_attr($primary_color); ?>
            ;
            --vy-header-bg:
                <?php echo esc_attr($header_bg); ?>
            ;
            --vy-text:
                <?php echo esc_attr($header_text); ?>
            ;
        }

        .header {
            background: var(--vy-header-bg);
        }

        .nav>li>a {
            color: var(--vy-text);
        }

        .nav>li>a:hover,
        .nav>li>a.active {
            color: var(--vy-accent);
            border-bottom-color: var(--vy-accent);
        }

        .search-field:focus {
            border-color: var(--vy-accent);
            box-shadow: 0 0 0 3px rgba(94, 181, 28, 0.1);
        }

        .ux-search-submit:hover {
            color: var(--vy-accent);
        }
    </style>
    <?php
}
add_action('wp_head', 'vy_header_customizer_css');


// ============================================================
// 3. Customizer JavaScript Preview Updates (Real-time)
// ============================================================

function vy_header_customize_preview_js()
{
    wp_enqueue_script(
        'vy-header-customize-preview',
        get_template_directory_uri() . '/js/customize-preview-header.js',
        ['customize-preview'],
        '1.0.0',
        true
    );
}
add_action('customize_preview_init', 'vy_header_customize_preview_js');
