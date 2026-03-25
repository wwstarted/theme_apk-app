<?php
/**
 * VOYA — single-product.php
 * WooCommerce Single Product Template
 * Layout: 9/3 col — Main | Sidebar
 */

if (!defined('ABSPATH'))
    exit;

/* ============================================================
   HELPER: Sidebar compact card
   ============================================================ */
if (!function_exists('vy_single_sidebar_card')) {
    function vy_single_sidebar_card($product_id)
    {
        $terms = get_the_terms($product_id, 'product_cat');
        $rating = min(5, max(0, (float) get_post_meta($product_id, '_vy_product_rating', true)));
        $display = $rating > 0 ? number_format($rating, 1) : '0';
        ?>
<div class="apk-item">
    <div class="apk-thumbnail">
        <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
            <?php
                            if (has_post_thumbnail($product_id)) {
                                echo get_the_post_thumbnail($product_id, 'thumbnail', ['alt' => get_the_title($product_id), 'loading' => 'lazy']);
                            } else {
                                echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title($product_id)) . '" loading="lazy">';
                            }
                            ?>
        </a>
    </div>
    <div class="apk-info">
        <h3>
            <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
                <?php echo esc_html(get_the_title($product_id)); ?>
            </a>
        </h3>
        <?php if ($terms && !is_wp_error($terms)): ?>
        <p class="apk-category">
            <?php
                                    $chunks = [];
                                    foreach (array_slice($terms, 0, 2) as $term) {
                                        $chunks[] = '<a href="' . esc_url(get_term_link($term)) . '" rel="tag">' . esc_html($term->name) . '</a>';
                                    }
                                    echo wp_kses_post(implode(', ', $chunks));
                                    ?>
        </p>
        <?php endif; ?>
        <div class="apk-stars">
            <?php echo esc_html($display); ?>
            <img src="<?php echo esc_url(get_theme_file_uri('/images/star-full.png')); ?>" alt="star">
        </div>
    </div>
</div>
<?php
    }
}

/* ============================================================
   HELPER: Single slider card (compact for 2-row column layout)
   ============================================================ */
if (!function_exists('vy_single_slider_card')) {
    function vy_single_slider_card($product_id)
    {
        $download_url = get_post_meta($product_id, '_vy_download_url', true);
        $button_url = $download_url ?: get_permalink($product_id);
        $terms = get_the_terms($product_id, 'product_cat');
        $rating = min(5, max(0, floatval(get_post_meta($product_id, '_vy_product_rating', true) ?: 0)));
        $full_stars = (int) $rating;
        ?>
<div class="apk-item apk-slide-item">
    <div class="apk-thumbnail">
        <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
            <?php
                            if (has_post_thumbnail($product_id)) {
                                echo get_the_post_thumbnail($product_id, 'thumbnail', ['alt' => get_the_title($product_id), 'loading' => 'lazy']);
                            } else {
                                echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title($product_id)) . '" loading="lazy">';
                            }
                            ?>
        </a>
    </div>
    <div class="apk-info">
        <h3>
            <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
                <?php echo esc_html(get_the_title($product_id)); ?>
            </a>
        </h3>
        <?php if ($terms && !is_wp_error($terms)): ?>
        <p class="apk-category">
            <?php
                                    $chunks = [];
                                    foreach (array_slice($terms, 0, 2) as $t) {
                                        $chunks[] = '<a href="' . esc_url(get_term_link($t)) . '" rel="tag">' . esc_html($t->name) . '</a>';
                                    }
                                    echo wp_kses_post(implode(', ', $chunks));
                                    ?>
        </p>
        <?php endif; ?>
        <div class="box-bottom">
            <div class="rating" data-stars="<?php echo esc_attr($rating); ?>">
                <?php for ($i = 0; $i < 5; $i++):
                                    $icon = $i < $full_stars ? 'star-full.png' : 'star-empty.png'; ?>
                <img src="<?php echo esc_url(get_theme_file_uri('/images/' . $icon)); ?>" alt="star">
                <?php endfor; ?>
            </div>
            <a class="apk-download-btn" href="<?php echo esc_url($button_url); ?>"
                <?php echo $download_url ? 'target="_blank" rel="noopener"' : ''; ?>>
                <?php esc_html_e('Tải xuống', 'voya'); ?>
            </a>
        </div>
    </div>
</div>
<?php
    }
}

/* ============================================================
   HELPER: Render slider block
   10 items → 5 columns × 2 rows per column
   ============================================================ */
if (!function_exists('vy_render_single_slider')) {
    function vy_render_single_slider($title, $query)
    {
        if (!$query->have_posts())
            return;
        $posts = $query->posts;
        wp_reset_postdata();
        $columns = array_chunk($posts, 2); // 2 items per column
        ?>
<div class="single-slider-section">
    <h3 class="single-slider-title"><?php echo esc_html($title); ?></h3>
    <div class="vy-apk-slider">
        <!-- Viewport clips overflow -->
        <div class="vy-apk-slider__viewport">
            <!-- Track slides horizontally -->
            <div class="vy-apk-slider__track">
                <?php foreach ($columns as $col): ?>
                <div class="vy-apk-slider__col">
                    <?php foreach ($col as $post):
                                                vy_single_slider_card($post->ID); endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="vy-apk-slider__btn vy-apk-slider__btn--prev" aria-label="<?php esc_attr_e('Trước', 'voya'); ?>">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="vy-apk-slider__btn vy-apk-slider__btn--next" aria-label="<?php esc_attr_e('Tiếp', 'voya'); ?>">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>
<?php
    }
}

/* ============================================================
   SETUP DATA
   ============================================================ */
if (!have_posts()) {
    wp_redirect(home_url('/'));
    exit;
}
the_post();

$product_id = get_the_ID();
$product = wc_get_product($product_id);
$download_url = get_post_meta($product_id, '_vy_download_url', true);
$rating = min(5, max(0, floatval(get_post_meta($product_id, '_vy_product_rating', true) ?: 0)));
$full_stars = (int) $rating;
$file_size = get_post_meta($product_id, '_vy_file_size', true);
$download_count = get_post_meta($product_id, '_vy_download_count', true);
$version = get_post_meta($product_id, '_vy_version', true);
$os_req = get_post_meta($product_id, '_vy_os_requirement', true);
$publisher = get_post_meta($product_id, '_vy_publisher', true);
$license = get_post_meta($product_id, '_vy_license', true) ?: __('Miễn Phí', 'voya');
$package_name = get_post_meta($product_id, '_vy_package_name', true);
$terms = get_the_terms($product_id, 'product_cat');

// Author
$author_id = (int) get_post_field('post_author', $product_id);
$author_name = get_the_author_meta('display_name', $author_id) ?: 'HappyMod Team';
$author_url = get_author_posts_url($author_id);
$author_bio = get_the_author_meta('description', $author_id);
$author_avatar = get_avatar($author_id, 80, '', $author_name);
$author_fb = get_the_author_meta('facebook', $author_id);
$author_tw = get_the_author_meta('twitter', $author_id);

// Manual IDs
$manual_popular = array_filter(array_map('absint', (array) get_post_meta($product_id, '_vy_popular_ids', true)));
$manual_related = array_filter(array_map('absint', (array) get_post_meta($product_id, '_vy_related_ids', true)));

// Queries
$popular_query = new WP_Query(!empty($manual_popular) ? [
    'post_type' => 'product',
    'posts_per_page' => count($manual_popular),
    'post__in' => $manual_popular,
    'orderby' => 'post__in',
    'ignore_sticky_posts' => true,
] : [
    'post_type' => 'product',
    'posts_per_page' => 10,
    'meta_key' => '_vy_product_rating',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
    'post__not_in' => [$product_id],
]);

$related_tax_query = [];
if ($terms && !is_wp_error($terms)) {
    $related_tax_query = [['taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => wp_list_pluck($terms, 'term_id')]];
}
$related_query = new WP_Query(!empty($manual_related) ? [
    'post_type' => 'product',
    'posts_per_page' => count($manual_related),
    'post__in' => $manual_related,
    'orderby' => 'post__in',
    'ignore_sticky_posts' => true,
] : [
    'post_type' => 'product',
    'posts_per_page' => 10,
    'post__not_in' => [$product_id],
    'orderby' => 'rand',
    'tax_query' => $related_tax_query,
]);

$sidebar_new_query = new WP_Query([
    'post_type' => 'product',
    'posts_per_page' => 12,
    'orderby' => 'date',
    'order' => 'DESC',
    'post__not_in' => [$product_id],
]);
$sidebar_pop_query = new WP_Query([
    'post_type' => 'product',
    'posts_per_page' => 12,
    'meta_key' => '_vy_product_rating',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
    'post__not_in' => [$product_id],
]);

get_header();
?>

<main class="vy-single-page vy-single-page--product">
    <div class="container">
        <div class="row vy-single-row">

            <!-- ════════════════════════════════════════════
                 MAIN CONTENT — 9 cols
            ════════════════════════════════════════════ -->
            <div class="col medium-9 small-12 large-9 vy-single-main-col">
                <div class="col-inner">

                    <!-- POST HEADER -->
                    <div class="post-header">
                        <div class="post-thumbnail">
                            <?php if (has_post_thumbnail()):
                                the_post_thumbnail('thumbnail', ['alt' => get_the_title()]);
                            else:
                                echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title()) . '">';
                            endif; ?>
                        </div>
                        <div class="post-info">
                            <h1 class="post-title"><?php the_title(); ?></h1>
                            <div class="post-meta">
                                <?php if ($download_count): ?>
                                <span><i
                                        class="fa-solid fa-download"></i><?php echo esc_html(number_format((int) $download_count)); ?></span>
                                <?php endif; ?>
                                <span><i
                                        class="fa-solid fa-star"></i><?php echo esc_html($rating > 0 ? number_format($rating, 1) : '0'); ?></span>
                                <span><i
                                        class="fa-regular fa-calendar"></i><?php echo esc_html(get_the_date('d/m/Y')); ?></span>
                                <?php if ($terms && !is_wp_error($terms)): ?>
                                <span><i class="fa-solid fa-tag"></i>
                                    <?php
                                        $tl = [];
                                        foreach ($terms as $term)
                                            $tl[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                                        echo wp_kses_post(implode(', ', $tl));
                                        ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- INFO TABLE -->
                    <div class="product-info-table-wrap">
                        <table class="post-info-table">
                            <tbody>
                                <tr>
                                    <td colspan="2" class="title"><?php esc_html_e('Thông Tin Chi Tiết', 'voya'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><?php esc_html_e('Ngày tạo', 'voya'); ?></td>
                                    <td><?php echo esc_html(get_the_date('d/m/Y')); ?></td>
                                </tr>
                                <?php if ($file_size): ?><tr>
                                    <td class="label"><?php esc_html_e('Dung lượng', 'voya'); ?></td>
                                    <td><?php echo esc_html($file_size); ?></td>
                                </tr><?php endif; ?>
                                <?php if ($download_count): ?><tr>
                                    <td class="label"><?php esc_html_e('Lượt tải', 'voya'); ?></td>
                                    <td><?php echo esc_html(number_format((int) $download_count)); ?></td>
                                </tr><?php endif; ?>
                                <?php if ($terms && !is_wp_error($terms)): ?>
                                <tr>
                                    <td class="label"><?php esc_html_e('Danh mục', 'voya'); ?></td>
                                    <td>
                                        <div class="apk-cat"><?php
                                    $cl = [];
                                    foreach ($terms as $term)
                                        $cl[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                                    echo wp_kses_post(implode(' ', $cl));
                                    ?></div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($version): ?><tr>
                                    <td class="label"><?php esc_html_e('Phiên bản', 'voya'); ?></td>
                                    <td><?php echo esc_html($version); ?></td>
                                </tr><?php endif; ?>
                                <?php if ($os_req): ?><tr>
                                    <td class="label"><?php esc_html_e('Yêu cầu OS', 'voya'); ?></td>
                                    <td><?php echo esc_html($os_req); ?></td>
                                </tr><?php endif; ?>
                                <?php if ($publisher): ?><tr>
                                    <td class="label"><?php esc_html_e('Nhà phát hành', 'voya'); ?></td>
                                    <td><?php echo esc_html($publisher); ?></td>
                                </tr><?php endif; ?>
                                <tr>
                                    <td class="label"><?php esc_html_e('Giấy phép', 'voya'); ?></td>
                                    <td><?php echo esc_html($license); ?></td>
                                </tr>
                                <?php if ($package_name): ?><tr>
                                    <td class="label"><?php esc_html_e('Tên gói', 'voya'); ?></td>
                                    <td class="pkg-name"><?php echo esc_html($package_name); ?></td>
                                </tr><?php endif; ?>
                                <tr>
                                    <td class="label"><?php esc_html_e('Đánh giá', 'voya'); ?></td>
                                    <td><?php echo esc_html(($rating > 0 ? number_format($rating, 1) : '0') . '/5'); ?>
                                        <img src="<?php echo esc_url(get_theme_file_uri('/images/star-full.png')); ?>"
                                            alt="star" class="inline-star">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label"><?php esc_html_e('Cập nhật', 'voya'); ?></td>
                                    <td><?php echo esc_html(get_the_modified_date('d/m/Y')); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- POST CONTENT -->
                    <div class="post-content-wrapper">
                        <div class="post-content entry-content">
                            <?php the_content(); ?>
                        </div>
                    </div>

                    <!-- DOWNLOAD CTA -->
                    <?php if ($download_url): ?>
                    <div class="single-download-wrap">
                        <a href="<?php echo esc_url($download_url); ?>" class="single-download-btn" target="_blank"
                            rel="noopener noreferrer">
                            <i class="fa-solid fa-download"></i>
                            <?php esc_html_e('Tải về ngay', 'voya'); ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- AUTHOR CARD -->
                    <div class="author-card">
                        <div class="author-top">
                            <div class="author-avatar">
                                <?php echo $author_avatar; ?>
                            </div>
                            <div class="author-info">
                                <h2 class="author-name">
                                    <a
                                        href="<?php echo esc_url($author_url); ?>"><?php echo esc_html($author_name); ?></a>
                                </h2>
                                <?php if ($author_fb || $author_tw): ?>
                                <div class="author-social">
                                    <?php if ($author_fb): ?><a href="<?php echo esc_url($author_fb); ?>"
                                        target="_blank" rel="noopener" aria-label="Facebook"><i
                                            class="fa-brands fa-facebook"></i></a><?php endif; ?>
                                    <?php if ($author_tw): ?><a href="<?php echo esc_url($author_tw); ?>"
                                        target="_blank" rel="noopener" aria-label="Twitter"><i
                                            class="fa-brands fa-twitter"></i></a><?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="author-description">
                            <?php echo wp_kses_post($author_bio ?: __('Chuyên viết về tải game APK, game mod và ứng dụng di động. Chia sẻ thông tin đơn giản, giúp bạn tìm và tối ưu trải nghiệm game một cách hiệu quả.', 'voya')); ?>
                        </div>
                    </div>

                    <!-- COMMENTS -->
                    <?php if (comments_open() || get_comments_number()): ?>
                    <div class="single-comments-wrap">
                        <?php comments_template(); ?>
                    </div>
                    <?php endif; ?>

                    <!-- SLIDERS -->
                    <?php vy_render_single_slider(__('Game phổ biến', 'voya'), $popular_query); ?>
                    <?php vy_render_single_slider(__('Game liên quan', 'voya'), $related_query); ?>

                </div>
            </div><!-- /.vy-single-main-col -->


            <!-- ════════════════════════════════════════════
                 SIDEBAR — 3 cols
            ════════════════════════════════════════════ -->
            <div class="col medium-3 small-12 large-3 vy-single-sidebar-col">
                <div class="col-inner">
                    <aside class="sidebar-apk">
                        <div class="vy-tabs vy-tabs--sidebar" data-vy-tabs>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="tab is-active">
                                    <button type="button" data-tab-target="sb-new" role="tab" aria-selected="true">
                                        <?php esc_html_e('Mới nhất', 'voya'); ?>
                                    </button>
                                </li>
                                <li class="tab">
                                    <button type="button" data-tab-target="sb-pop" role="tab" aria-selected="false">
                                        <?php esc_html_e('Phổ biến', 'voya'); ?>
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-panels">
                                <div class="panel is-active" data-tab-panel="sb-new" role="tabpanel">
                                    <div class="apk-sidebar-list">
                                        <?php
                                        if ($sidebar_new_query->have_posts()):
                                            while ($sidebar_new_query->have_posts()):
                                                $sidebar_new_query->the_post();
                                                vy_single_sidebar_card(get_the_ID());
                                            endwhile;
                                            wp_reset_postdata();
                                        endif;
                                        ?>
                                    </div>
                                </div>
                                <div class="panel" data-tab-panel="sb-pop" role="tabpanel">
                                    <div class="apk-sidebar-list">
                                        <?php
                                        if ($sidebar_pop_query->have_posts()):
                                            while ($sidebar_pop_query->have_posts()):
                                                $sidebar_pop_query->the_post();
                                                vy_single_sidebar_card(get_the_ID());
                                            endwhile;
                                            wp_reset_postdata();
                                        endif;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

        </div>
    </div>
</main>

<?php get_footer(); ?>