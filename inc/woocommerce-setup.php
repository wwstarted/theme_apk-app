<?php
/**
 * WooCommerce Integration Setup
 * - Hide "Add to Cart" button
 * - Custom meta boxes: Download URL, Rating, Product Details, Relations
 * - Helper functions
 * - Shortcode: [vy_products_grid]
 */

if (!defined('ABSPATH'))
    exit;

// ============================================================
// 1. REMOVE ADD TO CART BUTTON
// ============================================================
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);


// ============================================================
// 2. REGISTER CUSTOM META BOXES
// ============================================================
add_action('add_meta_boxes', function () {

    // Download URL + Button Text
    add_meta_box(
        'vy_product_download_url',
        __('Download URL', 'vy-theme'),
        ['VY_Product_Meta', 'render_download_url_box'],
        'product',
        'normal',
        'high'
    );

    // Rating Score
    add_meta_box(
        'vy_product_rating_score',
        __('Product Rating', 'vy-theme'),
        ['VY_Product_Meta', 'render_rating_score_box'],
        'product',
        'normal',
        'high'
    );

    // Product Details (version, size, OS, publisher, etc.)
    add_meta_box(
        'vy_product_details',
        __('Product Details (Info Table)', 'vy-theme'),
        ['VY_Product_Meta', 'render_product_details_box'],
        'product',
        'normal',
        'high'
    );

    // Popular & Related IDs for single product sliders
    add_meta_box(
        'vy_product_relations',
        __('Slider: Phổ Biến & Liên Quan (để trống = tự động)', 'vy-theme'),
        ['VY_Product_Meta', 'render_relations_box'],
        'product',
        'normal',
        'default'
    );
});


// ============================================================
// 3. META BOX RENDER METHODS
// ============================================================
class VY_Product_Meta
{
    /**
     * Download URL + Button Text
     */
    public static function render_download_url_box($post)
    {
        wp_nonce_field('vy_product_meta_nonce', 'vy_product_meta_nonce');

        $download_url = get_post_meta($post->ID, '_vy_download_url', true);
        $download_text = get_post_meta($post->ID, '_vy_download_text', true) ?: __('Tải xuống', 'vy-theme');
        ?>
<div style="padding:10px;">
    <p>
        <label for="vy_download_url"><strong><?php _e('Download URL:', 'vy-theme'); ?></strong></label><br>
        <input type="url" id="vy_download_url" name="vy_download_url" value="<?php echo esc_url($download_url); ?>"
            style="width:100%;padding:8px;margin-top:5px;" placeholder="https://example.com/download/file.apk">
    </p>
    <p>
        <label for="vy_download_text"><strong><?php _e('Download Button Text:', 'vy-theme'); ?></strong></label><br>
        <input type="text" id="vy_download_text" name="vy_download_text" value="<?php echo esc_attr($download_text); ?>"
            style="width:100%;padding:8px;margin-top:5px;" placeholder="<?php esc_attr_e('Tải xuống', 'vy-theme'); ?>">
    </p>
</div>
<?php
    }

    /**
     * Rating Score (0–5)
     */
    public static function render_rating_score_box($post)
    {
        $rating = (float) get_post_meta($post->ID, '_vy_product_rating', true);
        ?>
<div style="padding:10px;">
    <p>
        <label for="vy_product_rating"><strong><?php _e('Rating Score (0–5):', 'vy-theme'); ?></strong></label><br>
        <input type="number" id="vy_product_rating" name="vy_product_rating" min="0" max="5" step="0.1"
            value="<?php echo esc_attr($rating); ?>" style="width:150px;padding:8px;margin-top:5px;">
        <span style="font-size:12px;color:#666;display:block;margin-top:5px;">
            <?php _e('e.g. 4.5, 5.0, 3.7', 'vy-theme'); ?>
        </span>
    </p>
</div>
<?php
    }

    /**
     * Product Details: version, size, downloads, OS, publisher, license, package
     */
    public static function render_product_details_box($post)
    {
        $fields = [
            '_vy_version' => [
                'label' => __('Phiên bản', 'vy-theme'),
                'type' => 'text',
                'placeholder' => 'VD: 7.9.0',
            ],
            '_vy_file_size' => [
                'label' => __('Dung lượng', 'vy-theme'),
                'type' => 'text',
                'placeholder' => 'VD: 157 MB',
            ],
            '_vy_download_count' => [
                'label' => __('Lượt tải', 'vy-theme'),
                'type' => 'number',
                'placeholder' => 'VD: 500000000',
            ],
            '_vy_os_requirement' => [
                'label' => __('Yêu cầu OS', 'vy-theme'),
                'type' => 'text',
                'placeholder' => 'VD: Android 7.0+',
            ],
            '_vy_publisher' => [
                'label' => __('Nhà phát hành', 'vy-theme'),
                'type' => 'text',
                'placeholder' => 'VD: AxesInMotion Racing',
            ],
            '_vy_license' => [
                'label' => __('Giấy phép', 'vy-theme'),
                'type' => 'text',
                'placeholder' => 'VD: Miễn Phí',
            ],
            '_vy_package_name' => [
                'label' => __('Tên gói (Package Name)', 'vy-theme'),
                'type' => 'text',
                'placeholder' => 'VD: com.aim.racing',
            ],
        ];
        ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 20px;padding:10px;">
    <?php foreach ($fields as $meta_key => $cfg):
                        $val = get_post_meta($post->ID, $meta_key, true);
                        $input_id = esc_attr(ltrim($meta_key, '_'));
                        ?>
    <p style="margin:0;">
        <label for="<?php echo $input_id; ?>">
            <strong><?php echo esc_html($cfg['label']); ?></strong>
        </label><br>
        <input type="<?php echo esc_attr($cfg['type']); ?>" id="<?php echo $input_id; ?>"
            name="<?php echo esc_attr($meta_key); ?>" value="<?php echo esc_attr($val); ?>"
            style="width:100%;padding:6px 8px;margin-top:4px;border:1px solid #ddd;border-radius:3px;"
            placeholder="<?php echo esc_attr($cfg['placeholder']); ?>">
    </p>
    <?php endforeach; ?>
</div>
<?php
    }

    /**
     * Popular & Related product IDs for single product sliders
     */
    public static function render_relations_box($post)
    {
        $popular_ids = (array) get_post_meta($post->ID, '_vy_popular_ids', true);
        $related_ids = (array) get_post_meta($post->ID, '_vy_related_ids', true);

        $popular_raw = implode(', ', array_filter($popular_ids));
        $related_raw = implode(', ', array_filter($related_ids));
        ?>
<div style="padding:10px;">
    <p>
        <label for="vy_popular_ids_raw">
            <strong><?php _e('Game phổ biến — Product IDs (phân cách bằng dấu phẩy)', 'vy-theme'); ?></strong>
        </label><br>
        <input type="text" id="vy_popular_ids_raw" name="_vy_popular_ids_raw"
            value="<?php echo esc_attr($popular_raw); ?>" style="width:100%;padding:8px;margin-top:5px;"
            placeholder="VD: 12, 34, 56, 78">
    </p>
    <p>
        <label for="vy_related_ids_raw">
            <strong><?php _e('Game liên quan — Product IDs', 'vy-theme'); ?></strong>
        </label><br>
        <input type="text" id="vy_related_ids_raw" name="_vy_related_ids_raw"
            value="<?php echo esc_attr($related_raw); ?>" style="width:100%;padding:8px;margin-top:5px;"
            placeholder="VD: 90, 11, 22, 33">
    </p>
    <p style="font-size:12px;color:#888;margin-top:4px;">
        <?php _e('Để trống = tự động (phổ biến theo rating, liên quan theo cùng danh mục).', 'vy-theme'); ?>
    </p>
</div>
<?php
    }
}


// ============================================================
// 4. SAVE ALL META BOX DATA
// ============================================================
add_action('save_post_product', function ($post_id) {

    // Verify nonce
    if (
        !isset($_POST['vy_product_meta_nonce']) ||
        !wp_verify_nonce($_POST['vy_product_meta_nonce'], 'vy_product_meta_nonce')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    // ── Download URL & Button Text ──
    if (isset($_POST['vy_download_url'])) {
        update_post_meta($post_id, '_vy_download_url', sanitize_url($_POST['vy_download_url']));
    }
    if (isset($_POST['vy_download_text'])) {
        update_post_meta($post_id, '_vy_download_text', sanitize_text_field($_POST['vy_download_text']));
    }

    // ── Rating ──
    if (isset($_POST['vy_product_rating'])) {
        $rating = min(5, max(0, floatval($_POST['vy_product_rating'])));
        update_post_meta($post_id, '_vy_product_rating', $rating);
    }

    // ── Product Details ──
    $detail_fields = [
        '_vy_version',
        '_vy_file_size',
        '_vy_download_count',
        '_vy_os_requirement',
        '_vy_publisher',
        '_vy_license',
        '_vy_package_name',
    ];
    foreach ($detail_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // ── Popular IDs ──
    if (isset($_POST['_vy_popular_ids_raw'])) {
        $ids = array_values(array_filter(array_map('absint', explode(',', $_POST['_vy_popular_ids_raw']))));
        update_post_meta($post_id, '_vy_popular_ids', $ids);
    }

    // ── Related IDs ──
    if (isset($_POST['_vy_related_ids_raw'])) {
        $ids = array_values(array_filter(array_map('absint', explode(',', $_POST['_vy_related_ids_raw']))));
        update_post_meta($post_id, '_vy_related_ids', $ids);
    }
});


// ============================================================
// 5. HELPER: Get formatted download button
// ============================================================
if (!function_exists('vy_get_product_download_button')) {
    function vy_get_product_download_button($product_id)
    {
        $download_url = get_post_meta($product_id, '_vy_download_url', true);
        if (empty($download_url))
            return '';

        $download_text = get_post_meta($product_id, '_vy_download_text', true) ?: __('Tải xuống', 'vy-theme');

        return sprintf(
            '<a class="apk-download-btn" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url($download_url),
            esc_html($download_text)
        );
    }
}


// ============================================================
// 6. HELPER: Get product star rating HTML (5 star images)
// ============================================================
if (!function_exists('vy_get_product_star_rating')) {
    function vy_get_product_star_rating($product_id)
    {
        $rating = min(5, max(0, (float) get_post_meta($product_id, '_vy_product_rating', true)));
        $full_stars = (int) floor($rating);
        $has_half = ($rating - $full_stars) >= 0.5;
        $empty = 5 - $full_stars - ($has_half ? 1 : 0);

        $star_src = get_theme_file_uri('images/star-full.png');
        $empty_src = get_theme_file_uri('images/star-empty.png');

        $html = '<div class="rating" data-stars="' . esc_attr($rating) . '">';

        for ($i = 0; $i < $full_stars; $i++) {
            $html .= '<img src="' . esc_url($star_src) . '" alt="star" class="star-fill">';
        }
        if ($has_half) {
            // Render as full for now (replace with half-star image if available)
            $html .= '<img src="' . esc_url($star_src) . '" alt="star" class="star-fill">';
        }
        for ($i = 0; $i < $empty; $i++) {
            $html .= '<img src="' . esc_url($empty_src) . '" alt="star-empty" class="star-empty">';
        }

        $html .= '</div>';
        return $html;
    }
}


// ============================================================
// 7. HELPER: Get product categories HTML
// ============================================================
if (!function_exists('vy_get_product_categories_html')) {
    function vy_get_product_categories_html($product_id, $limit = 3)
    {
        $product = wc_get_product($product_id);
        if (!$product)
            return '';

        $cat_ids = array_slice($product->get_category_ids(), 0, $limit);
        if (empty($cat_ids))
            return '';

        $chunks = [];
        foreach ($cat_ids as $cat_id) {
            $cat = get_term($cat_id, 'product_cat');
            if ($cat && !is_wp_error($cat)) {
                $chunks[] = sprintf(
                    '<a href="%s" rel="tag">%s</a>',
                    esc_url(get_term_link($cat_id, 'product_cat')),
                    esc_html($cat->name)
                );
            }
        }

        return '<p class="apk-category">' . implode(', ', $chunks) . '</p>';
    }
}


// ============================================================
// 8. SHORTCODE: [vy_products_grid]
// ============================================================
add_shortcode('vy_products_grid', 'vy_products_grid_shortcode');

function vy_products_grid_shortcode($atts)
{
    $atts = shortcode_atts([
        'category' => '',
        'per_page' => 12,
        'orderby' => 'date',
        'order' => 'DESC',
        'title' => '',
        'view_more_text' => __('Xem thêm', 'vy-theme'),
        'view_more_url' => '',
    ], $atts, 'vy_products_grid');

    $args = [
        'post_type' => 'product',
        'posts_per_page' => intval($atts['per_page']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
    ];

    if (!empty($atts['category'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => array_map('trim', explode(',', $atts['category'])),
            ]
        ];
    }

    $query = new WP_Query($args);
    ob_start();

    if (!empty($atts['title'])):
        echo '<h2 class="apk-section-title">' . esc_html($atts['title']) . '</h2>';
    endif;

    if ($query->have_posts()): ?>
<div class="apk-grid">
    <?php while ($query->have_posts()):
                        $query->the_post();
                        $pid = get_the_ID();
                        $download_url = get_post_meta($pid, '_vy_download_url', true);
                        $thumb_id = get_post_thumbnail_id($pid);
                        ?>
    <div class="apk-item">
        <div class="apk-thumbnail">
            <?php if ($thumb_id):
                                        echo wp_get_attachment_image($thumb_id, 'woocommerce_thumbnail', false, [
                                            'alt' => esc_attr(get_the_title()),
                                            'loading' => 'lazy',
                                            'decoding' => 'async',
                                        ]);
                                    else:
                                        echo wc_placeholder_img();
                                    endif; ?>
        </div>
        <div class="apk-info">
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <?php echo vy_get_product_categories_html($pid, 3); ?>
            <div class="box-bottom">
                <?php echo vy_get_product_star_rating($pid); ?>
                <?php if ($download_url):
                                            echo vy_get_product_download_button($pid); endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile;
                    wp_reset_postdata(); ?>
</div>

<?php if (!empty($atts['view_more_url']) && !empty($atts['view_more_text'])): ?>
<div class="apk-view-more">
    <a href="<?php echo esc_url($atts['view_more_url']); ?>" class="btn">
        <?php echo esc_html($atts['view_more_text']); ?>
    </a>
</div>
<?php endif;

    else: ?>
<p class="apk-no-products"><?php _e('Không có sản phẩm nào được tìm thấy.', 'vy-theme'); ?></p>
<?php endif;

    return ob_get_clean();
}