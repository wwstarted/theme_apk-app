<?php
/**
 * VOYA — search.php
 * Trang kết quả tìm kiếm
 * Layout: 75/25 — Main (tabs: Products + Posts) | Sidebar (Latest/Popular products)
 */

get_header();

$search_query = get_search_query(); // đã escape
$paged = max(1, get_query_var('paged') ?: (get_query_var('page') ?: 1));
$per_page = 12;

// ── Query: Products ──────────────────────────────────────────
$product_query = new WP_Query([
    'post_type' => 'product',
    'post_status' => 'publish',
    's' => $search_query,
    'posts_per_page' => $per_page,
    'paged' => $paged,
]);

// ── Query: Blog Posts ─────────────────────────────────────────
$post_query = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    's' => $search_query,
    'posts_per_page' => $per_page,
    'paged' => $paged,
]);

$product_count = $product_query->found_posts;
$post_count = $post_query->found_posts;
$total_count = $product_count + $post_count;

// Xác định tab mặc định: ưu tiên products nếu có kết quả
$default_tab = $product_count > 0 ? 'products' : ($post_count > 0 ? 'posts' : 'products');

// ── Sidebar queries ────────────────────────────────────────────
$sidebar_new_query = new WP_Query([
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => 12,
    'orderby' => 'date',
    'order' => 'DESC',
]);
$sidebar_pop_query = new WP_Query([
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => 12,
    'meta_key' => '_vy_product_rating',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
]);
?>

<main class="vy-search-page">
    <div class="container">

        <!-- Search Header -->
        <div class="vy-search-header">
            <h1 class="vy-search-header__query">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <?php if ($search_query): ?>
                    <?php esc_html_e('Kết quả tìm kiếm:', 'voya'); ?>
                    <strong>"<?php echo esc_html($search_query); ?>"</strong>
                <?php else: ?>
                    <?php esc_html_e('Tất cả kết quả', 'voya'); ?>
                <?php endif; ?>
            </h1>
            <?php if ($total_count > 0): ?>
                <div class="vy-search-header__count">
                    <?php echo number_format($total_count); ?> kết quả
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_count === 0): ?>
            <!-- No results -->
            <div class="vy-search-empty">
                <div class="vy-search-empty__icon"><i class="fa-regular fa-face-sad-tear"></i></div>
                <h2 class="vy-search-empty__title">Không tìm thấy kết quả</h2>
                <p class="vy-search-empty__desc">
                    Không có game, app hoặc bài viết nào khớp với
                    <strong>"<?php echo esc_html($search_query); ?>"</strong>.<br>
                    Hãy thử từ khóa khác hoặc rút gọn từ khóa tìm kiếm.
                </p>
                <a href="<?php echo home_url('/'); ?>" class="vy-search-empty__btn">
                    <i class="fa-solid fa-house"></i> Về trang chủ
                </a>
            </div>

        <?php else: ?>

            <div class="vy-search-row">

                <!-- MAIN CONTENT — 75% -->
                <div class="vy-search-main-col">

                    <div class="vy-tabs vy-search-tabs" data-vy-tabs>
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="tab <?php echo $default_tab === 'products' ? 'is-active' : ''; ?>">
                                <button type="button" data-tab-target="sr-products" role="tab"
                                    aria-selected="<?php echo $default_tab === 'products' ? 'true' : 'false'; ?>">
                                    <i class="fa-solid fa-gamepad"></i>
                                    Game &amp; App
                                    <?php if ($product_count > 0): ?>
                                        <span class="vy-search-tab-count"><?php echo number_format($product_count); ?></span>
                                    <?php endif; ?>
                                </button>
                            </li>
                            <li class="tab <?php echo $default_tab === 'posts' ? 'is-active' : ''; ?>">
                                <button type="button" data-tab-target="sr-posts" role="tab"
                                    aria-selected="<?php echo $default_tab === 'posts' ? 'true' : 'false'; ?>">
                                    <i class="fa-regular fa-newspaper"></i>
                                    Bài viết
                                    <?php if ($post_count > 0): ?>
                                        <span class="vy-search-tab-count"><?php echo number_format($post_count); ?></span>
                                    <?php endif; ?>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-panels">

                            <!-- TAB: Products -->
                            <div class="panel <?php echo $default_tab === 'products' ? 'is-active' : ''; ?>"
                                data-tab-panel="sr-products" role="tabpanel">

                                <?php if ($product_query->have_posts()): ?>
                                    <div class="apk-grid search-apk-grid">
                                        <?php while ($product_query->have_posts()):
                                            $product_query->the_post();
                                            $pid = get_the_ID();
                                            $download_url = get_post_meta($pid, '_vy_download_url', true);
                                            $thumb_id = get_post_thumbnail_id($pid);
                                            ?>
                                            <div class="apk-item">
                                                <div class="apk-thumbnail">
                                                    <a href="<?php the_permalink(); ?>">
                                                        <?php if ($thumb_id):
                                                            echo wp_get_attachment_image($thumb_id, 'woocommerce_thumbnail', false, [
                                                                'alt' => esc_attr(get_the_title()),
                                                                'loading' => 'lazy',
                                                            ]);
                                                        else:
                                                            echo wc_placeholder_img();
                                                        endif; ?>
                                                    </a>
                                                </div>
                                                <div class="apk-info">
                                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                    <?php echo vy_get_product_categories_html($pid, 2); ?>
                                                    <div class="box-bottom">
                                                        <?php echo vy_get_product_star_rating($pid); ?>
                                                        <?php if ($download_url):
                                                            echo vy_get_product_download_button($pid);
                                                        endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile;
                                        wp_reset_postdata(); ?>
                                    </div>

                                    <?php if ($product_query->max_num_pages > 1): ?>
                                        <div class="search-pagination">
                                            <?php echo paginate_links([
                                                'base' => add_query_arg('paged', '%#%'),
                                                'format' => '',
                                                'current' => $paged,
                                                'total' => $product_query->max_num_pages,
                                                'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                                                'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                                            ]); ?>
                                        </div>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <div class="vy-search-no-results">
                                        <i class="fa-regular fa-face-sad-tear"></i>
                                        <p>Không tìm thấy game/app nào khớp với
                                            <strong>"<?php echo esc_html($search_query); ?>"</strong>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- TAB: Blog Posts -->
                            <div class="panel <?php echo $default_tab === 'posts' ? 'is-active' : ''; ?>"
                                data-tab-panel="sr-posts" role="tabpanel">

                                <?php if ($post_query->have_posts()): ?>
                                    <div class="vy-search-posts-list">
                                        <?php while ($post_query->have_posts()):
                                            $post_query->the_post(); ?>
                                            <article class="vy-search-post-item">
                                                <?php if (has_post_thumbnail()): ?>
                                                    <a class="vy-search-post-item__thumb" href="<?php the_permalink(); ?>">
                                                        <?php the_post_thumbnail('medium', ['loading' => 'lazy', 'alt' => esc_attr(get_the_title())]); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <div class="vy-search-post-item__body">
                                                    <div class="vy-search-post-item__meta">
                                                        <?php
                                                        $cats = get_the_category();
                                                        if ($cats):
                                                            foreach (array_slice($cats, 0, 2) as $cat):
                                                                ?>
                                                                <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                                                                    class="vy-search-post-item__cat">
                                                                    <?php echo esc_html($cat->name); ?>
                                                                </a>
                                                            <?php endforeach; endif; ?>
                                                        <span class="vy-search-post-item__date">
                                                            <i class="fa-regular fa-calendar"></i>
                                                            <?php echo get_the_date('d/m/Y'); ?>
                                                        </span>
                                                    </div>
                                                    <h2 class="vy-search-post-item__title">
                                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                    </h2>
                                                    <p class="vy-search-post-item__excerpt">
                                                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                                    </p>
                                                    <a href="<?php the_permalink(); ?>" class="vy-search-post-item__readmore">
                                                        Đọc tiếp <i class="fa-solid fa-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </article>
                                        <?php endwhile;
                                        wp_reset_postdata(); ?>
                                    </div>

                                    <?php if ($post_query->max_num_pages > 1): ?>
                                        <div class="search-pagination">
                                            <?php echo paginate_links([
                                                'base' => add_query_arg('paged', '%#%'),
                                                'format' => '',
                                                'current' => $paged,
                                                'total' => $post_query->max_num_pages,
                                                'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                                                'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                                            ]); ?>
                                        </div>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <div class="vy-search-no-results">
                                        <i class="fa-regular fa-face-sad-tear"></i>
                                        <p>Không tìm thấy bài viết nào khớp với
                                            <strong>"<?php echo esc_html($search_query); ?>"</strong>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div><!-- /.tab-panels -->
                    </div><!-- /.vy-tabs -->

                </div><!-- /.vy-search-main-col -->


                <!-- SIDEBAR — 25% -->
                <div class="vy-search-sidebar-col">
                    <aside class="sidebar-apk">
                        <div class="vy-tabs vy-tabs--sidebar" data-vy-tabs>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="tab is-active">
                                    <button type="button" data-tab-target="sb-new" role="tab" aria-selected="true">Mới
                                        nhất</button>
                                </li>
                                <li class="tab">
                                    <button type="button" data-tab-target="sb-pop" role="tab" aria-selected="false">Phổ
                                        biến</button>
                                </li>
                            </ul>
                            <div class="tab-panels">

                                <div class="panel is-active" data-tab-panel="sb-new" role="tabpanel">
                                    <div class="apk-sidebar-list">
                                        <?php
                                        if ($sidebar_new_query->have_posts()):
                                            while ($sidebar_new_query->have_posts()):
                                                $sidebar_new_query->the_post();
                                                $spid = get_the_ID();
                                                $sterms = get_the_terms($spid, 'product_cat');
                                                $srat = min(5, max(0, (float) get_post_meta($spid, '_vy_product_rating', true)));
                                                ?>
                                                <div class="apk-item">
                                                    <div class="apk-thumbnail">
                                                        <a href="<?php the_permalink(); ?>">
                                                            <?php if (has_post_thumbnail()):
                                                                the_post_thumbnail('thumbnail', ['alt' => esc_attr(get_the_title()), 'loading' => 'lazy']);
                                                            else:
                                                                echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy">';
                                                            endif; ?>
                                                        </a>
                                                    </div>
                                                    <div class="apk-info">
                                                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                        <?php if ($sterms && !is_wp_error($sterms)): ?>
                                                            <p class="apk-category">
                                                                <?php
                                                                $chunks = [];
                                                                foreach (array_slice($sterms, 0, 2) as $t)
                                                                    $chunks[] = '<a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a>';
                                                                echo wp_kses_post(implode(', ', $chunks));
                                                                ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <div class="apk-stars">
                                                            <?php echo esc_html($srat > 0 ? number_format($srat, 1) : '0'); ?>
                                                            <img src="<?php echo esc_url(get_theme_file_uri('/images/star-full.png')); ?>"
                                                                alt="star">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile;
                                            wp_reset_postdata();
                                        endif; ?>
                                    </div>
                                </div>

                                <div class="panel" data-tab-panel="sb-pop" role="tabpanel">
                                    <div class="apk-sidebar-list">
                                        <?php
                                        if ($sidebar_pop_query->have_posts()):
                                            while ($sidebar_pop_query->have_posts()):
                                                $sidebar_pop_query->the_post();
                                                $spid = get_the_ID();
                                                $sterms = get_the_terms($spid, 'product_cat');
                                                $srat = min(5, max(0, (float) get_post_meta($spid, '_vy_product_rating', true)));
                                                ?>
                                                <div class="apk-item">
                                                    <div class="apk-thumbnail">
                                                        <a href="<?php the_permalink(); ?>">
                                                            <?php if (has_post_thumbnail()):
                                                                the_post_thumbnail('thumbnail', ['alt' => esc_attr(get_the_title()), 'loading' => 'lazy']);
                                                            else:
                                                                echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy">';
                                                            endif; ?>
                                                        </a>
                                                    </div>
                                                    <div class="apk-info">
                                                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                        <?php if ($sterms && !is_wp_error($sterms)): ?>
                                                            <p class="apk-category">
                                                                <?php
                                                                $chunks = [];
                                                                foreach (array_slice($sterms, 0, 2) as $t)
                                                                    $chunks[] = '<a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a>';
                                                                echo wp_kses_post(implode(', ', $chunks));
                                                                ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <div class="apk-stars">
                                                            <?php echo esc_html($srat > 0 ? number_format($srat, 1) : '0'); ?>
                                                            <img src="<?php echo esc_url(get_theme_file_uri('/images/star-full.png')); ?>"
                                                                alt="star">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile;
                                            wp_reset_postdata();
                                        endif; ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </aside>
                </div><!-- /.vy-search-sidebar-col -->

            </div><!-- /.vy-search-row -->
        <?php endif; ?>

    </div><!-- /.container -->
</main>

<?php get_footer(); ?>