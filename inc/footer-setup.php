<?php
/**
 * VOYA — footer-setup.php
 * WordPress Customizer controls cho footer
 * + Helper: render link list từ JSON option
 */

if (!defined('ABSPATH'))
    exit;

/* ============================================================
   1. REGISTER CUSTOMIZER PANEL + SECTIONS + CONTROLS
   ============================================================ */
add_action('customize_register', 'vy_footer_customize_register');

function vy_footer_customize_register($wp_customize)
{
    // ── Panel ──
    $wp_customize->add_panel('vy_footer_panel', [
        'title' => __('Footer Settings', 'voya'),
        'description' => __('Tùy chỉnh nội dung và giao diện footer.', 'voya'),
        'priority' => 150,
    ]);

    /* ──────────────────────────────────────────────────────
       SECTION 1: Logo & Bio (Col 1 + Col 2)
    ────────────────────────────────────────────────────── */
    $wp_customize->add_section('vy_footer_branding', [
        'title' => __('Logo & Giới thiệu', 'voya'),
        'panel' => 'vy_footer_panel',
        'priority' => 10,
    ]);

    // Footer Logo
    $wp_customize->add_setting('vy_footer_logo_id', [
        'type' => 'option',
        'default' => '',
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'vy_footer_logo_id', [
        'label' => __('Logo Footer (icon/ảnh)', 'voya'),
        'description' => __('Khuyến nghị: ảnh vuông PNG/SVG, nền trong suốt. Để trống = dùng Custom Logo.', 'voya'),
        'section' => 'vy_footer_branding',
        'mime_type' => 'image',
    ]));

    // Bio text
    $wp_customize->add_setting('vy_footer_bio', [
        'type' => 'option',
        'default' => '',
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('vy_footer_bio', [
        'label' => __('Đoạn giới thiệu (Bio)', 'voya'),
        'description' => __('Cho phép HTML. Ví dụ: <strong>HappyMod</strong> là nền tảng...', 'voya'),
        'section' => 'vy_footer_branding',
        'type' => 'textarea',
        'priority' => 20,
    ]);

    // Contact line
    $wp_customize->add_setting('vy_footer_contact', [
        'type' => 'option',
        'default' => '',
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('vy_footer_contact', [
        'label' => __('Dòng liên hệ', 'voya'),
        'description' => __('VD: Liên hệ qua Tele: <strong>@lienketnet</strong>', 'voya'),
        'section' => 'vy_footer_branding',
        'type' => 'text',
        'priority' => 30,
    ]);

    /* ──────────────────────────────────────────────────────
       SECTION 2: Social Links
    ────────────────────────────────────────────────────── */
    $wp_customize->add_section('vy_footer_socials', [
        'title' => __('Mạng xã hội', 'voya'),
        'panel' => 'vy_footer_panel',
        'priority' => 20,
    ]);

    $social_platforms = [
        'facebook' => 'Facebook',
        'twitter' => 'X (Twitter)',
        'pinterest' => 'Pinterest',
        'linkedin' => 'LinkedIn',
        'telegram' => 'Telegram',
        'youtube' => 'YouTube',
        'instagram' => 'Instagram',
    ];

    $priority = 10;
    foreach ($social_platforms as $key => $label) {
        $wp_customize->add_setting("vy_footer_social_$key", [
            'type' => 'option',
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control("vy_footer_social_$key", [
            'label' => $label . ' URL',
            'section' => 'vy_footer_socials',
            'type' => 'url',
            'priority' => $priority,
        ]);
        $priority += 10;
    }

    /* ──────────────────────────────────────────────────────
       SECTION 3: Col 3 — Danh mục
    ────────────────────────────────────────────────────── */
    $wp_customize->add_section('vy_footer_col_cats', [
        'title' => __('Cột Danh Mục', 'voya'),
        'panel' => 'vy_footer_panel',
        'priority' => 30,
    ]);

    $wp_customize->add_setting('vy_footer_cat_title', [
        'type' => 'option',
        'default' => 'Danh mục',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('vy_footer_cat_title', [
        'label' => __('Tiêu đề cột', 'voya'),
        'section' => 'vy_footer_col_cats',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('vy_footer_cat_links', [
        'type' => 'option',
        'default' => '',
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('vy_footer_cat_links', [
        'label' => __('Danh sách links (JSON)', 'voya'),
        'description' => __('Format JSON: [{"label":"Game","url":"https://..."},{"label":"App","url":"https://..."}]. Để trống = tự lấy từ product categories.', 'voya'),
        'section' => 'vy_footer_col_cats',
        'type' => 'textarea',
        'priority' => 20,
    ]);

    /* ──────────────────────────────────────────────────────
       SECTION 4: Col 4 — Về chúng tôi
    ────────────────────────────────────────────────────── */
    $wp_customize->add_section('vy_footer_col_about', [
        'title' => __('Cột Về Chúng Tôi', 'voya'),
        'panel' => 'vy_footer_panel',
        'priority' => 40,
    ]);

    $wp_customize->add_setting('vy_footer_about_title', [
        'type' => 'option',
        'default' => 'Về chúng tôi',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('vy_footer_about_title', [
        'label' => __('Tiêu đề cột', 'voya'),
        'section' => 'vy_footer_col_about',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('vy_footer_about_links', [
        'type' => 'option',
        'default' => '',
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('vy_footer_about_links', [
        'label' => __('Danh sách links (JSON)', 'voya'),
        'description' => __('Format JSON: [{"label":"Giới thiệu","url":"https://..."},{"label":"DMCA","url":"https://..."}]', 'voya'),
        'section' => 'vy_footer_col_about',
        'type' => 'textarea',
        'priority' => 20,
    ]);

    /* ──────────────────────────────────────────────────────
       SECTION 5: Copyright bar
    ────────────────────────────────────────────────────── */
    $wp_customize->add_section('vy_footer_bottom', [
        'title' => __('Copyright Bar', 'voya'),
        'panel' => 'vy_footer_panel',
        'priority' => 50,
    ]);

    $wp_customize->add_setting('vy_footer_copyright', [
        'type' => 'option',
        'default' => '',
        'sanitize_callback' => 'wp_kses_post',
    ]);
    $wp_customize->add_control('vy_footer_copyright', [
        'label' => __('Nội dung Copyright', 'voya'),
        'description' => __('Để trống = tự sinh. Cho phép HTML. VD: Copyright 2026 &copy; <strong>HappyMod &ndash; All Rights Reserved</strong>', 'voya'),
        'section' => 'vy_footer_bottom',
        'type' => 'textarea',
    ]);

    /* ──────────────────────────────────────────────────────
       SECTION 6: Colors
    ────────────────────────────────────────────────────── */
    $wp_customize->add_section('vy_footer_colors', [
        'title' => __('Màu sắc Footer', 'voya'),
        'panel' => 'vy_footer_panel',
        'priority' => 60,
    ]);

    $color_controls = [
        'vy_footer_color_bg' => ['label' => __('Màu nền chính', 'voya'), 'default' => '#e8e8e8'],
        'vy_footer_color_bot' => ['label' => __('Màu nền copyright bar', 'voya'), 'default' => '#555555'],
        'vy_footer_color_accent' => ['label' => __('Màu accent (tiêu đề, icon)', 'voya'), 'default' => '#0a8f0f'],
        'vy_footer_color_text' => ['label' => __('Màu văn bản bio', 'voya'), 'default' => '#444444'],
        'vy_footer_color_link' => ['label' => __('Màu link danh sách', 'voya'), 'default' => '#4e7891'],
        'vy_footer_color_copy' => ['label' => __('Màu chữ copyright bar', 'voya'), 'default' => '#eeeeee'],
    ];

    $priority = 10;
    foreach ($color_controls as $setting => $cfg) {
        $wp_customize->add_setting($setting, [
            'type' => 'option',
            'default' => $cfg['default'],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage',
        ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $setting, [
            'label' => $cfg['label'],
            'section' => 'vy_footer_colors',
            'priority' => $priority,
        ]));
        $priority += 10;
    }
}


/* ============================================================
   2. OUTPUT INLINE CSS FROM COLOR OPTIONS
   ============================================================ */
add_action('wp_head', 'vy_footer_output_colors', 20);

function vy_footer_output_colors()
{
    $bg = get_option('vy_footer_color_bg', '#e8e8e8');
    $bot = get_option('vy_footer_color_bot', '#555555');
    $accent = get_option('vy_footer_color_accent', '#0a8f0f');
    $text = get_option('vy_footer_color_text', '#444444');
    $link = get_option('vy_footer_color_link', '#4e7891');
    $copy = get_option('vy_footer_color_copy', '#eeeeee');

    // Only output if values differ from CSS defaults
    $lines = [];
    if ($bg !== '#e8e8e8')
        $lines[] = '--vy-ft-bg:' . esc_attr($bg) . ';';
    if ($bot !== '#555555')
        $lines[] = '--vy-ft-bg-bot:' . esc_attr($bot) . ';';
    if ($accent !== '#0a8f0f')
        $lines[] = '--vy-ft-heading:' . esc_attr($accent) . '; --vy-ft-accent:' . esc_attr($accent) . '; --vy-ft-link-hover:' . esc_attr($accent) . ';';
    if ($text !== '#444444')
        $lines[] = '--vy-ft-text:' . esc_attr($text) . ';';
    if ($link !== '#4e7891')
        $lines[] = '--vy-ft-link:' . esc_attr($link) . ';';
    if ($copy !== '#eeeeee')
        $lines[] = '--vy-ft-copy-text:' . esc_attr($copy) . ';';

    if (!empty($lines)) {
        echo '<style id="vy-footer-colors">:root{' . implode('', $lines) . '}</style>' . "\n";
    }
}


/* ============================================================
   3. REGISTER FOOTER WIDGET AREAS (tùy chọn)
   ============================================================ */
add_action('widgets_init', 'vy_register_footer_widgets');

function vy_register_footer_widgets()
{
    register_sidebar([
        'name' => __('Footer Col 1 — Logo/Bio', 'voya'),
        'id' => 'footer-col-1',
        'description' => __('Widget area cho cột 1 footer (thay logo mặc định)', 'voya'),
        'before_widget' => '<div class="vy-footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="vy-footer__col-title">',
        'after_title' => '</h3>',
    ]);
}


/* ============================================================
   4. ADMIN PAGE: Quản lý Links Footer dễ hơn
   Thêm submenu trong Appearance để nhập JSON links trực tiếp
   ============================================================ */
add_action('admin_menu', 'vy_footer_admin_menu');

function vy_footer_admin_menu()
{
    add_theme_page(
        __('Footer Links', 'voya'),
        __('Footer Links', 'voya'),
        'edit_theme_options',
        'vy-footer-links',
        'vy_footer_links_page'
    );
}

function vy_footer_links_page()
{
    // Save
    if (isset($_POST['vy_footer_links_nonce']) && wp_verify_nonce($_POST['vy_footer_links_nonce'], 'vy_footer_links_save')) {
        if (current_user_can('edit_theme_options')) {

            // Cat links
            $cat_links = [];
            if (!empty($_POST['cat_label']) && is_array($_POST['cat_label'])) {
                foreach ($_POST['cat_label'] as $i => $label) {
                    $label = sanitize_text_field($label);
                    $url = isset($_POST['cat_url'][$i]) ? esc_url_raw($_POST['cat_url'][$i]) : '';
                    if ($label && $url)
                        $cat_links[] = compact('label', 'url');
                }
            }
            update_option('vy_footer_cat_links', wp_json_encode($cat_links, JSON_UNESCAPED_UNICODE));

            // About links
            $about_links = [];
            if (!empty($_POST['about_label']) && is_array($_POST['about_label'])) {
                foreach ($_POST['about_label'] as $i => $label) {
                    $label = sanitize_text_field($label);
                    $url = isset($_POST['about_url'][$i]) ? esc_url_raw($_POST['about_url'][$i]) : '';
                    if ($label && $url)
                        $about_links[] = compact('label', 'url');
                }
            }
            update_option('vy_footer_about_links', wp_json_encode($about_links, JSON_UNESCAPED_UNICODE));

            // Titles
            update_option('vy_footer_cat_title', sanitize_text_field($_POST['cat_title'] ?? 'Danh mục'));
            update_option('vy_footer_about_title', sanitize_text_field($_POST['about_title'] ?? 'Về chúng tôi'));

            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Đã lưu thành công!', 'voya') . '</p></div>';
        }
    }

    // Load current values
    $cat_title = get_option('vy_footer_cat_title', 'Danh mục');
    $about_title = get_option('vy_footer_about_title', 'Về chúng tôi');
    $cat_links = json_decode(get_option('vy_footer_cat_links', '[]'), true) ?: [];
    $about_links = json_decode(get_option('vy_footer_about_links', '[]'), true) ?: [];

    // Add empty rows for new entries
    $cat_links[] = ['label' => '', 'url' => ''];
    $about_links[] = ['label' => '', 'url' => ''];
    ?>
<div class="wrap">
    <h1><?php esc_html_e('Footer Links', 'voya'); ?></h1>
    <p style="color:#666;">
        <?php esc_html_e('Quản lý links hiển thị ở cột "Danh mục" và "Về chúng tôi" trong footer.', 'voya'); ?></p>

    <form method="post" action="">
        <?php wp_nonce_field('vy_footer_links_save', 'vy_footer_links_nonce'); ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;max-width:900px;">

            <!-- Col 3: Danh mục -->
            <div>
                <h2 style="font-size:16px;margin-bottom:4px;"><?php esc_html_e('Cột Danh Mục', 'voya'); ?></h2>
                <p><label><strong><?php esc_html_e('Tiêu đề cột:', 'voya'); ?></strong><br>
                        <input type="text" name="cat_title" value="<?php echo esc_attr($cat_title); ?>"
                            style="width:100%;margin-top:4px;">
                    </label></p>
                <table class="widefat" style="margin-top:8px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Tên hiển thị', 'voya'); ?></th>
                            <th><?php esc_html_e('URL', 'voya'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cat-rows">
                        <?php foreach ($cat_links as $i => $link): ?>
                        <tr>
                            <td><input type="text" name="cat_label[]" value="<?php echo esc_attr($link['label']); ?>"
                                    style="width:100%;"></td>
                            <td><input type="url" name="cat_url[]" value="<?php echo esc_attr($link['url']); ?>"
                                    style="width:100%;" placeholder="https://"></td>
                            <td><button type="button" onclick="this.closest('tr').remove()"
                                    style="color:red;background:none;border:none;cursor:pointer;font-size:18px;">&times;</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" onclick="addRow('cat-rows','cat_label','cat_url')" class="button button-secondary"
                    style="margin-top:8px;">
                    + <?php esc_html_e('Thêm link', 'voya'); ?>
                </button>
            </div>

            <!-- Col 4: Về chúng tôi -->
            <div>
                <h2 style="font-size:16px;margin-bottom:4px;"><?php esc_html_e('Cột Về Chúng Tôi', 'voya'); ?></h2>
                <p><label><strong><?php esc_html_e('Tiêu đề cột:', 'voya'); ?></strong><br>
                        <input type="text" name="about_title" value="<?php echo esc_attr($about_title); ?>"
                            style="width:100%;margin-top:4px;">
                    </label></p>
                <table class="widefat" style="margin-top:8px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Tên hiển thị', 'voya'); ?></th>
                            <th><?php esc_html_e('URL', 'voya'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="about-rows">
                        <?php foreach ($about_links as $i => $link): ?>
                        <tr>
                            <td><input type="text" name="about_label[]" value="<?php echo esc_attr($link['label']); ?>"
                                    style="width:100%;"></td>
                            <td><input type="url" name="about_url[]" value="<?php echo esc_attr($link['url']); ?>"
                                    style="width:100%;" placeholder="https://"></td>
                            <td><button type="button" onclick="this.closest('tr').remove()"
                                    style="color:red;background:none;border:none;cursor:pointer;font-size:18px;">&times;</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" onclick="addRow('about-rows','about_label','about_url')"
                    class="button button-secondary" style="margin-top:8px;">
                    + <?php esc_html_e('Thêm link', 'voya'); ?>
                </button>
            </div>

        </div>

        <p style="margin-top:24px;">
            <input type="submit" class="button button-primary button-large"
                value="<?php esc_attr_e('Lưu thay đổi', 'voya'); ?>">
        </p>
    </form>
</div>

<script>
function addRow(tbodyId, labelName, urlName) {
    var tbody = document.getElementById(tbodyId);
    var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="' + labelName + '[]" style="width:100%;"></td>' +
        '<td><input type="url" name="' + urlName + '[]" style="width:100%;" placeholder="https://"></td>' +
        '<td><button type="button" onclick="this.closest(\'tr\').remove()" style="color:red;background:none;border:none;cursor:pointer;font-size:18px;">&times;</button></td>';
    tbody.appendChild(tr);
}
</script>
<?php
}