<?php
/**
 * VOYA — home-options.php
 * Admin page: Appearance → Home Options
 * Quản lý nội dung hiển thị trên front-page.php:
 *   - Section Game Phổ Biến (product IDs)
 *   - Section App Phổ Biến (product IDs)
 *   - Section Tin Tức (post IDs, 6 bài)
 */

if (!defined('ABSPATH'))
    exit;

/* ============================================================
   1. REGISTER ADMIN MENU
   ============================================================ */
add_action('admin_menu', 'vy_home_options_menu');

function vy_home_options_menu()
{
    add_theme_page(
        __('Home Page Options', 'voya'),
        __('Home Options', 'voya'),
        'edit_theme_options',
        'vy-home-options',
        'vy_home_options_page'
    );
}


/* ============================================================
   2. ENQUEUE SELECT2 for searchable post picker
   ============================================================ */
add_action('admin_enqueue_scripts', 'vy_home_options_scripts');

function vy_home_options_scripts($hook)
{
    if ($hook !== 'appearance_page_vy-home-options')
        return;

    // Select2
    wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13');
    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], '4.0.13', true);

    // AJAX for searching posts/products
    wp_localize_script('select2', 'vyHomeAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vy_home_search'),
    ]);

    wp_add_inline_script('select2', "
jQuery(function($) {
    // Search products
    $('.vy-select2-products').select2({
        placeholder: 'Tìm kiếm sản phẩm...',
        allowClear: true,
        ajax: {
            url: vyHomeAjax.ajaxurl,
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { action: 'vy_search_products', term: params.term, nonce: vyHomeAjax.nonce };
            },
            processResults: function(data) { return { results: data }; }
        }
    });

    // Search posts (blog)
    $('.vy-select2-posts').select2({
        placeholder: 'Tìm kiếm bài viết...',
        allowClear: true,
        ajax: {
            url: vyHomeAjax.ajaxurl,
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { action: 'vy_search_posts', term: params.term, nonce: vyHomeAjax.nonce };
            },
            processResults: function(data) { return { results: data }; }
        }
    });

    // Sortable rows
    if ($.fn.sortable) {
        $('.vy-sortable-rows').sortable({ handle: '.vy-drag-handle', axis: 'y' });
    }
});
");
}


/* ============================================================
   3. AJAX HANDLERS — search products / search posts
   ============================================================ */
add_action('wp_ajax_vy_search_products', 'vy_ajax_search_products');
add_action('wp_ajax_vy_search_posts', 'vy_ajax_search_posts');

function vy_ajax_search_products()
{
    check_ajax_referer('vy_home_search', 'nonce');
    $term = sanitize_text_field($_GET['term'] ?? '');

    $q = new WP_Query([
        'post_type' => 'product',
        'post_status' => 'publish',
        's' => $term,
        'posts_per_page' => 20,
        'no_found_rows' => true,
    ]);

    $results = [];
    foreach ($q->posts as $p) {
        $results[] = ['id' => $p->ID, 'text' => $p->post_title . ' (#' . $p->ID . ')'];
    }

    wp_send_json($results);
}

function vy_ajax_search_posts()
{
    check_ajax_referer('vy_home_search', 'nonce');
    $term = sanitize_text_field($_GET['term'] ?? '');

    $q = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        's' => $term,
        'posts_per_page' => 20,
        'no_found_rows' => true,
    ]);

    $results = [];
    foreach ($q->posts as $p) {
        $results[] = ['id' => $p->ID, 'text' => $p->post_title . ' (#' . $p->ID . ')'];
    }

    wp_send_json($results);
}


/* ============================================================
   4. ADMIN PAGE — render
   ============================================================ */
function vy_home_options_page()
{
    /* ── SAVE ── */
    if (
        isset($_POST['vy_home_nonce']) &&
        wp_verify_nonce($_POST['vy_home_nonce'], 'vy_home_save') &&
        current_user_can('edit_theme_options')
    ) {
        /* Games section */
        if (isset($_POST['vy_games_title'])) {
            update_option('vy_home_games_title', sanitize_text_field($_POST['vy_games_title']));
        }
        if (isset($_POST['vy_game_ids']) && is_array($_POST['vy_game_ids'])) {
            $ids = array_values(array_filter(array_map('absint', $_POST['vy_game_ids'])));
            update_option('vy_home_game_ids', wp_json_encode($ids));
        } else {
            update_option('vy_home_game_ids', '[]');
        }

        /* Apps section */
        if (isset($_POST['vy_apps_title'])) {
            update_option('vy_home_apps_title', sanitize_text_field($_POST['vy_apps_title']));
        }
        if (isset($_POST['vy_app_ids']) && is_array($_POST['vy_app_ids'])) {
            $ids = array_values(array_filter(array_map('absint', $_POST['vy_app_ids'])));
            update_option('vy_home_app_ids', wp_json_encode($ids));
        } else {
            update_option('vy_home_app_ids', '[]');
        }

        /* News section */
        if (isset($_POST['vy_news_title'])) {
            update_option('vy_home_news_title', sanitize_text_field($_POST['vy_news_title']));
        }
        if (isset($_POST['vy_news_view_more'])) {
            update_option('vy_home_news_view_more', esc_url_raw($_POST['vy_news_view_more']));
        }
        if (isset($_POST['vy_news_ids']) && is_array($_POST['vy_news_ids'])) {
            $ids = array_slice(array_values(array_filter(array_map('absint', $_POST['vy_news_ids']))), 0, 6);
            update_option('vy_home_news_ids', wp_json_encode($ids));
        } else {
            update_option('vy_home_news_ids', '[]');
        }

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Đã lưu thành công!', 'voya') . '</p></div>';
    }

    /* ── Load current values ── */
    $games_title = get_option('vy_home_games_title', 'Game Phổ Biến');
    $apps_title = get_option('vy_home_apps_title', 'App Phổ Biến');
    $news_title = get_option('vy_home_news_title', 'Tin tức mới nhất');
    $news_vm = get_option('vy_home_news_view_more', '');

    $game_ids = json_decode(get_option('vy_home_game_ids', '[]'), true) ?: [];
    $app_ids = json_decode(get_option('vy_home_app_ids', '[]'), true) ?: [];
    $news_ids = json_decode(get_option('vy_home_news_ids', '[]'), true) ?: [];

    /* Helper: render pre-selected options for Select2 */
    $render_product_options = function ($ids) {
        foreach ($ids as $id) {
            $post = get_post(absint($id));
            if ($post && $post->post_type === 'product') {
                echo '<option value="' . esc_attr($id) . '" selected>' . esc_html($post->post_title) . ' (#' . esc_attr($id) . ')</option>';
            }
        }
    };

    $render_post_options = function ($ids) {
        foreach ($ids as $id) {
            $post = get_post(absint($id));
            if ($post && $post->post_type === 'post') {
                echo '<option value="' . esc_attr($id) . '" selected>' . esc_html($post->post_title) . ' (#' . esc_attr($id) . ')</option>';
            }
        }
    };
    ?>

<div class="wrap">
    <h1><?php esc_html_e('Home Page Options', 'voya'); ?></h1>
    <p style="color:#666;margin-bottom:24px;">
        <?php esc_html_e('Tùy chỉnh nội dung hiển thị trên trang chủ. Mỗi section có thể chọn bài theo ý muốn hoặc để trống để hiển thị tự động.', 'voya'); ?>
    </p>

    <form method="post" action="">
        <?php wp_nonce_field('vy_home_save', 'vy_home_nonce'); ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;max-width:1100px;">

            <!-- ═══════════════════════
                     SECTION GAME
                ═══════════════════════ -->
            <div style="background:#fff;padding:20px;border-radius:6px;box-shadow:0 1px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size:16px;margin:0 0 4px;color:#58c21c;">
                    🎮 <?php esc_html_e('Section: Game Phổ Biến', 'voya'); ?>
                </h2>
                <p style="color:#888;font-size:12px;margin:0 0 16px;">
                    <?php esc_html_e('Để trống = tự động lấy theo category "game"', 'voya'); ?></p>

                <p>
                    <label><strong><?php esc_html_e('Tiêu đề section:', 'voya'); ?></strong></label><br>
                    <input type="text" name="vy_games_title" value="<?php echo esc_attr($games_title); ?>"
                        style="width:100%;margin-top:4px;">
                </p>
                <p>
                    <label><strong><?php esc_html_e('Chọn sản phẩm (tối đa 24):', 'voya'); ?></strong></label><br>
                    <select name="vy_game_ids[]" multiple class="vy-select2-products"
                        style="width:100%;margin-top:4px;">
                        <?php $render_product_options($game_ids); ?>
                    </select>
                    <span
                        style="font-size:11px;color:#aaa;display:block;margin-top:4px;"><?php esc_html_e('Gõ tên để tìm kiếm sản phẩm', 'voya'); ?></span>
                </p>
            </div>

            <!-- ═══════════════════════
                     SECTION APP
                ═══════════════════════ -->
            <div style="background:#fff;padding:20px;border-radius:6px;box-shadow:0 1px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size:16px;margin:0 0 4px;color:#58c21c;">
                    📱 <?php esc_html_e('Section: App Phổ Biến', 'voya'); ?>
                </h2>
                <p style="color:#888;font-size:12px;margin:0 0 16px;">
                    <?php esc_html_e('Để trống = tự động lấy theo category "app"', 'voya'); ?></p>

                <p>
                    <label><strong><?php esc_html_e('Tiêu đề section:', 'voya'); ?></strong></label><br>
                    <input type="text" name="vy_apps_title" value="<?php echo esc_attr($apps_title); ?>"
                        style="width:100%;margin-top:4px;">
                </p>
                <p>
                    <label><strong><?php esc_html_e('Chọn sản phẩm (tối đa 24):', 'voya'); ?></strong></label><br>
                    <select name="vy_app_ids[]" multiple class="vy-select2-products" style="width:100%;margin-top:4px;">
                        <?php $render_product_options($app_ids); ?>
                    </select>
                    <span
                        style="font-size:11px;color:#aaa;display:block;margin-top:4px;"><?php esc_html_e('Gõ tên để tìm kiếm sản phẩm', 'voya'); ?></span>
                </p>
            </div>

        </div><!-- end 2-col grid -->


        <!-- ═══════════════════════════════════════════════
                 SECTION TIN TỨC — full width
            ═══════════════════════════════════════════════ -->
        <div
            style="background:#fff;padding:20px;border-radius:6px;box-shadow:0 1px 4px rgba(0,0,0,0.1);max-width:1100px;margin-top:28px;">
            <h2 style="font-size:16px;margin:0 0 4px;color:#58c21c;">
                📰 <?php esc_html_e('Section: Tin Tức Mới Nhất', 'voya'); ?>
            </h2>
            <p style="color:#888;font-size:12px;margin:0 0 16px;">
                <?php esc_html_e('Hiển thị 6 bài viết (2 hàng × 3 cột) ở cuối trang chủ, trước footer. Để trống = tự động lấy 6 bài mới nhất.', 'voya'); ?>
            </p>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">

                <p style="margin:0;">
                    <label><strong><?php esc_html_e('Tiêu đề section:', 'voya'); ?></strong></label><br>
                    <input type="text" name="vy_news_title" value="<?php echo esc_attr($news_title); ?>"
                        style="width:100%;margin-top:4px;">
                </p>

                <p style="margin:0;">
                    <label><strong><?php esc_html_e('URL nút "Xem thêm":', 'voya'); ?></strong></label><br>
                    <input type="url" name="vy_news_view_more" value="<?php echo esc_attr($news_vm); ?>"
                        style="width:100%;margin-top:4px;" placeholder="<?php echo esc_attr(home_url('/blog/')); ?>">
                    <span
                        style="font-size:11px;color:#aaa;display:block;margin-top:3px;"><?php esc_html_e('Để trống = tự động lấy URL trang blog', 'voya'); ?></span>
                </p>

                <div></div><!-- spacer -->

            </div>

            <div style="margin-top:16px;">
                <label><strong><?php esc_html_e('Chọn bài viết (tối đa 6 bài):', 'voya'); ?></strong></label><br>
                <select name="vy_news_ids[]" multiple class="vy-select2-posts"
                    style="width:100%;margin-top:6px;max-width:600px;">
                    <?php $render_post_options($news_ids); ?>
                </select>
                <p style="font-size:11px;color:#aaa;margin:6px 0 0;">
                    <?php esc_html_e('Gõ tên bài viết để tìm kiếm. Chỉ lưu 6 bài đầu tiên.', 'voya'); ?></p>
            </div>

            <!-- Preview current selected -->
            <?php if (!empty($news_ids)): ?>
            <div style="margin-top:16px;padding:12px;background:#f9f9f9;border-radius:4px;border:1px solid #e8e8e8;">
                <strong style="font-size:12px;color:#555;"><?php esc_html_e('Đang chọn:', 'voya'); ?></strong>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
                    <?php foreach (array_slice($news_ids, 0, 6) as $nid):
                                    $np = get_post(absint($nid));
                                    if (!$np)
                                        continue;
                                    $nimg = get_the_post_thumbnail_url($np->ID, 'thumbnail');
                                    ?>
                    <div
                        style="display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #ddd;border-radius:4px;padding:6px 10px;font-size:12px;">
                        <?php if ($nimg): ?>
                        <img src="<?php echo esc_url($nimg); ?>" width="36" height="36"
                            style="object-fit:cover;border-radius:3px;">
                        <?php endif; ?>
                        <span><?php echo esc_html(wp_trim_words($np->post_title, 6, '…')); ?></span>
                        <span style="color:#aaa;">#<?php echo esc_html($nid); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- end news section -->


        <p style="margin-top:24px;">
            <input type="submit" class="button button-primary button-large"
                value="<?php esc_attr_e('Lưu thay đổi', 'voya'); ?>">
        </p>

    </form>
</div><!-- .wrap -->

<?php
}


/* ============================================================
   5. HELPER FUNCTIONS — dùng trong front-page.php
   ============================================================ */

/**
 * Lấy IDs sản phẩm cho home sections
 * @param string $section 'games' | 'apps' | 'news'
 * @return array
 */
if (!function_exists('vy_get_home_section_ids')) {
    function vy_get_home_section_ids($section)
    {
        $option_map = [
            'games' => 'vy_home_game_ids',
            'apps' => 'vy_home_app_ids',
            'news' => 'vy_home_news_ids',
        ];

        $option = $option_map[$section] ?? '';
        if (!$option)
            return [];

        $ids = json_decode(get_option($option, '[]'), true);
        return is_array($ids) ? array_values(array_filter(array_map('absint', $ids))) : [];
    }
}