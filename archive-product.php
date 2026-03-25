<?php
/**
 * WooCommerce product archive template
 * Applies to Shop archive and product category archives.
 * Layout: 9/3 col — Main (tabs + grid 2col + pagination) | Sidebar (terms + tabs + compact list)
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ============================================================
   HELPER: Star rating HTML — main grid style (5 star images)
   ============================================================ */
if (!function_exists('vy_archive_product_stars')) {
    function vy_archive_product_stars($product_id)
    {
        $rating = (float) get_post_meta($product_id, '_vy_product_rating', true);
        $rating = min(5, max(0, $rating));
        $full_stars = (int) floor($rating);

        $html = '<div class="rating" data-stars="' . esc_attr($rating) . '">';
        for ($i = 0; $i < 5; $i++) {
            $icon = $i < $full_stars ? 'star-full.png' : 'star-empty.png';
            $html .= '<img src="' . esc_url(get_theme_file_uri('/images/' . $icon)) . '" alt="star">';
        }
        $html .= '</div>';

        return $html;
    }
}

/* ============================================================
   HELPER: Star rating HTML — sidebar style (number + 1 star icon)
   ============================================================ */
if (!function_exists('vy_sidebar_product_stars')) {
    function vy_sidebar_product_stars($product_id)
    {
        $rating = (float) get_post_meta($product_id, '_vy_product_rating', true);
        $rating = min(5, max(0, $rating));
        $display = $rating > 0 ? number_format($rating, 1) : '0';

        return '<div class="apk-stars">'
            . esc_html($display)
            . '<img src="' . esc_url(get_theme_file_uri('/images/star-full.png')) . '" alt="star">'
            . '</div>';
    }
}

/* ============================================================
   HELPER: Main grid card (thumbnail + info + box-bottom)
   ============================================================ */
if (!function_exists('vy_archive_product_card')) {
    function vy_archive_product_card($product_id)
    {
        $download_url = get_post_meta($product_id, '_vy_download_url', true);
        $button_url = $download_url ?: get_permalink($product_id);
        $terms = get_the_terms($product_id, 'product_cat');
        ?>
<article class="apk-item">
    <div class="apk-thumbnail">
        <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
            <?php
                            if (has_post_thumbnail($product_id)) {
                                echo get_the_post_thumbnail($product_id, 'thumbnail', ['alt' => get_the_title($product_id)]);
                            } else {
                                echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title($product_id)) . '">';
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
                                    foreach (array_slice($terms, 0, 3) as $term) {
                                        $chunks[] = '<a href="' . esc_url(get_term_link($term)) . '" rel="tag">'
                                            . esc_html($term->name) . '</a>';
                                    }
                                    echo wp_kses_post(implode(', ', $chunks));
                                    ?>
        </p>
        <?php endif; ?>

        <div class="box-bottom">
            <?php echo vy_archive_product_stars($product_id); ?>
            <a class="apk-download-btn" href="<?php echo esc_url($button_url); ?>"
                <?php echo $download_url ? 'target="_blank" rel="noopener"' : ''; ?>>
                <?php esc_html_e('Tải xuống', 'voya'); ?>
            </a>
        </div>
    </div>
</article>
<?php
    }
}

/* ============================================================
   HELPER: Sidebar compact card (thumbnail + title + category + stars number)
   ============================================================ */
if (!function_exists('vy_sidebar_product_card')) {
    function vy_sidebar_product_card($product_id)
    {
        $terms = get_the_terms($product_id, 'product_cat');
        ?>
<div class="apk-item">
    <div class="apk-thumbnail">
        <a href="<?php echo esc_url(get_permalink($product_id)); ?>">
            <?php
                            if (has_post_thumbnail($product_id)) {
                                echo get_the_post_thumbnail($product_id, 'thumbnail', ['alt' => get_the_title($product_id)]);
                            } else {
                                echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title($product_id)) . '">';
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
                                        $chunks[] = '<a href="' . esc_url(get_term_link($term)) . '" rel="tag">'
                                            . esc_html($term->name) . '</a>';
                                    }
                                    echo wp_kses_post(implode(', ', $chunks));
                                    ?>
        </p>
        <?php endif; ?>

        <?php echo vy_sidebar_product_stars($product_id); ?>
    </div>
</div>
<?php
    }
}

/* ============================================================
   SETUP QUERIES
   ============================================================ */
$current_term = is_tax('product_cat') ? get_queried_object() : null;
$tax_filter = [];

if ($current_term && !is_wp_error($current_term)) {
    $tax_filter[] = [
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => [$current_term->term_id],
        'include_children' => true,
    ];
}

// Popular (main): order by rating meta, fallback to comment_count
$popular_main_args = [
    'post_type' => 'product',
    'posts_per_page' => 10,
    'meta_key' => '_vy_product_rating',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
];
if (!empty($tax_filter)) {
    $popular_main_args['tax_query'] = $tax_filter;
}
$popular_main_query = new WP_Query($popular_main_args);

// Sidebar latest
$sidebar_new_args = [
    'post_type' => 'product',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC',
];
if (!empty($tax_filter)) {
    $sidebar_new_args['tax_query'] = $tax_filter;
}
$sidebar_new_query = new WP_Query($sidebar_new_args);

// Sidebar popular
$sidebar_popular_args = [
    'post_type' => 'product',
    'posts_per_page' => 10,
    'meta_key' => '_vy_product_rating',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
];
if (!empty($tax_filter)) {
    $sidebar_popular_args['tax_query'] = $tax_filter;
}
$sidebar_popular_query = new WP_Query($sidebar_popular_args);

// Archive title
$archive_title = $current_term ? $current_term->name : woocommerce_page_title(false);

// Sidebar term list — sub-cats nếu có, fallback ra top-level
$term_list_args = ['taxonomy' => 'product_cat', 'hide_empty' => true];
if ($current_term) {
    $term_list_args['parent'] = $current_term->term_id;
}
$sidebar_terms = get_terms($term_list_args);
if (empty($sidebar_terms) || is_wp_error($sidebar_terms)) {
    $term_list_args['parent'] = 0;
    $sidebar_terms = get_terms($term_list_args);
}

// Category label for sidebar title
$cat_label = $current_term ? esc_html($current_term->name) : __('Sản phẩm', 'voya');

get_header();
?>

<main class="vy-archive-page vy-archive-page--product">
    <div class="container">
        <div class="row vy-archive-row">

            <!-- ================================================
                 MAIN CONTENT — 9 cols
            ================================================ -->
            <div class="col medium-9 small-12 large-9 vy-archive-main-col">
                <div class="col-inner">

                    <h1 class="vy-archive-title"><?php echo esc_html($archive_title); ?></h1>

                    <div class="vy-tabs" data-vy-tabs>
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="tab is-active">
                                <button type="button" data-tab-target="latest" role="tab" aria-selected="true">
                                    <?php esc_html_e('Mới nhất', 'voya'); ?>
                                </button>
                            </li>
                            <li class="tab">
                                <button type="button" data-tab-target="popular" role="tab" aria-selected="false">
                                    <?php esc_html_e('Phổ biến nhất', 'voya'); ?>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-panels">

                            <!-- Tab: Mới nhất (main WP_Query — paginated) -->
                            <div class="panel is-active" data-tab-panel="latest" role="tabpanel">
                                <div class="apk-grid archive-apk-grid">
                                    <?php if (have_posts()):
                                        while (have_posts()):
                                            the_post(); ?>
                                    <?php vy_archive_product_card(get_the_ID()); ?>
                                    <?php endwhile; else: ?>
                                    <p class="vy-no-results"><?php esc_html_e('Không có sản phẩm nào.', 'voya'); ?></p>
                                    <?php endif; ?>
                                </div>

                                <nav class="archive-pagination" aria-label="<?php esc_attr_e('Phân trang', 'voya'); ?>">
                                    <?php
                                    echo wp_kses_post(paginate_links([
                                        'total' => max(1, (int) $GLOBALS['wp_query']->max_num_pages),
                                        'current' => max(1, get_query_var('paged') ? (int) get_query_var('paged') : 1),
                                        'mid_size' => 1,
                                        'prev_text' => __('Trước', 'voya'),
                                        'next_text' => __('Tiếp theo', 'voya'),
                                    ]));
                                    ?>
                                </nav>
                            </div>

                            <!-- Tab: Phổ biến nhất (separate query — no pagination) -->
                            <div class="panel" data-tab-panel="popular" role="tabpanel">
                                <div class="apk-grid archive-apk-grid">
                                    <?php if ($popular_main_query->have_posts()):
                                        while ($popular_main_query->have_posts()):
                                            $popular_main_query->the_post(); ?>
                                    <?php vy_archive_product_card(get_the_ID()); ?>
                                    <?php endwhile; else: ?>
                                    <p class="vy-no-results"><?php esc_html_e('Không có dữ liệu phổ biến.', 'voya'); ?>
                                    </p>
                                    <?php endif;
                                    wp_reset_postdata(); ?>
                                </div>
                            </div>

                        </div><!-- /.tab-panels -->
                    </div><!-- /.vy-tabs -->

                </div><!-- /.col-inner -->
            </div><!-- /.vy-archive-main-col -->


            <!-- ================================================
                 SIDEBAR — 3 cols
            ================================================ -->
            <div class="col medium-3 small-12 large-3 vy-archive-sidebar-col">
                <div class="col-inner">
                    <aside class="sidebar-apk">

                        <!-- Category title -->
                        <h2 class="sidebar-title">
                            <?php printf(esc_html__('Danh Mục %s', 'voya'), $cat_label); ?>
                        </h2>

                        <!-- Term pills -->
                        <?php if (!empty($sidebar_terms) && !is_wp_error($sidebar_terms)): ?>
                        <div class="term-list sidebar-term-list">
                            <?php foreach ($sidebar_terms as $term): ?>
                            <a href="<?php echo esc_url(get_term_link($term)); ?>" class="term-item">
                                <?php echo esc_html($term->name); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Sidebar tabs: Mới nhất / Phổ biến -->
                        <div class="vy-tabs vy-tabs--sidebar" data-vy-tabs>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="tab is-active">
                                    <button type="button" data-tab-target="sb-latest" role="tab" aria-selected="true">
                                        <?php esc_html_e('Mới nhất', 'voya'); ?>
                                    </button>
                                </li>
                                <li class="tab">
                                    <button type="button" data-tab-target="sb-popular" role="tab" aria-selected="false">
                                        <?php esc_html_e('Phổ biến', 'voya'); ?>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-panels">

                                <!-- Sidebar: Mới nhất -->
                                <div class="panel is-active" data-tab-panel="sb-latest" role="tabpanel">
                                    <div class="apk-sidebar-list">
                                        <?php if ($sidebar_new_query->have_posts()):
                                            while ($sidebar_new_query->have_posts()):
                                                $sidebar_new_query->the_post(); ?>
                                        <?php vy_sidebar_product_card(get_the_ID()); ?>
                                        <?php endwhile; endif;
                                        wp_reset_postdata(); ?>
                                    </div>
                                </div>

                                <!-- Sidebar: Phổ biến -->
                                <div class="panel" data-tab-panel="sb-popular" role="tabpanel">
                                    <div class="apk-sidebar-list">
                                        <?php if ($sidebar_popular_query->have_posts()):
                                            while ($sidebar_popular_query->have_posts()):
                                                $sidebar_popular_query->the_post(); ?>
                                        <?php vy_sidebar_product_card(get_the_ID()); ?>
                                        <?php endwhile; endif;
                                        wp_reset_postdata(); ?>
                                    </div>
                                </div>

                            </div><!-- /.tab-panels -->
                        </div><!-- /.vy-tabs--sidebar -->

                    </aside>
                </div><!-- /.col-inner -->
            </div><!-- /.vy-archive-sidebar-col -->

        </div><!-- /.row -->
    </div><!-- /.container -->
</main>

<?php get_footer(); ?>