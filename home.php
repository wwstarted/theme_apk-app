<?php
/**
 * VOYA — home.php
 * Blog posts index (tất cả bài viết, không lọc category/tag)
 * Tương tự archive-product.php cho WooCommerce
 *
 * WordPress dùng file này khi:
 * Settings → Reading → "Your homepage displays" = Static page
 * → "Posts page" được chọn
 */

get_header();

// ── Filter / sort state ──────────────────────────────────────
$current_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
$current_sort = isset($_GET['sort']) ? sanitize_key($_GET['sort']) : 'date_desc';

$sort_map = [
    'date_desc' => ['orderby' => 'date', 'order' => 'DESC'],
    'date_asc' => ['orderby' => 'date', 'order' => 'ASC'],
    'title_asc' => ['orderby' => 'title', 'order' => 'ASC'],
    'popular' => ['orderby' => 'comment_count', 'order' => 'DESC'],
];
$sort = $sort_map[$current_sort] ?? $sort_map['date_desc'];

// ── Sidebar data ─────────────────────────────────────────────
$all_cats = get_categories([
    'hide_empty' => true,
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 20,
]);

$all_tags = get_tags([
    'hide_empty' => true,
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 40,
]);

// ── Base URL (blog page) ──────────────────────────────────────
$blog_page_id = get_option('page_for_posts');
$base_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');

// ── Build query ───────────────────────────────────────────────
$paged = max(1, get_query_var('paged') ?: (get_query_var('page') ?: 1));
$per_page = get_option('posts_per_page', 10);

if ($current_cat || $current_sort !== 'date_desc') {
    // Custom query khi có filter/sort
    $query_args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'orderby' => $sort['orderby'],
        'order' => $sort['order'],
        'no_found_rows' => false,
    ];
    if ($current_cat)
        $query_args['cat'] = $current_cat;

    $custom_q = new WP_Query($query_args);
    $total_posts = $custom_q->found_posts;
    $max_pages = $custom_q->max_num_pages;
    $use_custom = true;
} else {
    // Dùng main WP query (chuẩn WP, pagination đúng)
    global $wp_query;
    $custom_q = null;
    $total_posts = $wp_query->found_posts;
    $max_pages = $wp_query->max_num_pages;
    $use_custom = false;
}

// ── FIX: dùng 1 biến $loop_query tránh lỗi syntax if/else lồng while ──
$loop_query = $use_custom ? $custom_q : $GLOBALS['wp_query'];

// ── Sort URL builder ─────────────────────────────────────────
function vy_home_sort_url($base, $cat_id, $sort_key)
{
    if ($cat_id) {
        $url = get_category_link($cat_id);
        $params = $sort_key !== 'date_desc' ? ['sort' => $sort_key] : [];
        return $params ? add_query_arg($params, $url) : $url;
    }
    $params = $sort_key !== 'date_desc' ? ['sort' => $sort_key] : [];
    return $params ? add_query_arg($params, $base) : $base;
}

// ── Current archive URL (for pagination) ─────────────────────
$current_archive_url = $current_cat
    ? get_category_link($current_cat)
    : $base_url;
?>

<main class="vy-blog-archive" id="vy-blog-main">

    <!-- ════════════════════════════════════════════
         PAGE HEADER
    ════════════════════════════════════════════ -->
    <div class="vy-blog-header">
        <div class="vy-blog-header__inner">
            <div class="vy-blog-header__left">
                <h1 class="vy-blog-header__title">
                    <?php
                    if ($current_cat && ($fcat = get_category($current_cat))) {
                        echo esc_html($fcat->name);
                    } else {
                        esc_html_e('Blog', 'voya');
                    }
                    ?>
                </h1>
                <nav class="vy-blog-breadcrumb" aria-label="Breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Trang chủ', 'voya'); ?></a>
                    <span>/</span>
                    <?php if ($current_cat && ($fcat = get_category($current_cat))): ?>
                    <a href="<?php echo esc_url($base_url); ?>"><?php esc_html_e('Blog', 'voya'); ?></a>
                    <span>/</span>
                    <span><?php echo esc_html($fcat->name); ?></span>
                    <?php else: ?>
                    <span><?php esc_html_e('Blog', 'voya'); ?></span>
                    <?php endif; ?>
                </nav>
            </div>
            <?php if ($total_posts > 0): ?>
            <div class="vy-blog-header__count">
                <span><?php echo number_format_i18n($total_posts); ?></span>
                <?php esc_html_e('bài viết', 'voya'); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         BODY: Sidebar + Main grid
    ════════════════════════════════════════════ -->
    <div class="vy-blog-body">
        <div class="vy-blog-container">
            <div class="vy-blog-layout">

                <!-- ── SIDEBAR ── -->
                <aside class="vy-blog-sidebar" aria-label="Bộ lọc bài viết">

                    <div class="vy-blog-sidebar__inner" id="vy-blog-sidebar-inner">

                        <!-- Categories -->
                        <div class="vy-blog-widget">
                            <div class="vy-blog-widget__head">
                                <i class="fa-solid fa-layer-group"></i>
                                <?php esc_html_e('Danh mục', 'voya'); ?>
                            </div>
                            <div class="vy-blog-widget__body">
                                <ul class="vy-blog-cat-list">

                                    <!-- Tất cả -->
                                    <li>
                                        <a href="<?php echo esc_url($base_url); ?>"
                                            class="vy-blog-cat-link <?php echo !$current_cat ? 'is-active' : ''; ?>">
                                            <span class="vy-blog-cat-dot"></span>
                                            <?php esc_html_e('Tất cả bài viết', 'voya'); ?>
                                        </a>
                                    </li>

                                    <?php foreach ($all_cats as $ci):
                                        $cat_url = get_category_link($ci->term_id);
                                        if ($current_sort !== 'date_desc') {
                                            $cat_url = add_query_arg('sort', $current_sort, $cat_url);
                                        }
                                        $is_active = $current_cat === $ci->term_id;
                                        ?>
                                    <li>
                                        <a href="<?php echo esc_url($cat_url); ?>"
                                            class="vy-blog-cat-link <?php echo $is_active ? 'is-active' : ''; ?>">
                                            <span class="vy-blog-cat-dot"></span>
                                            <?php echo esc_html($ci->name); ?>
                                            <span class="vy-blog-cat-count"><?php echo esc_html($ci->count); ?></span>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>

                                </ul>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="vy-blog-widget">
                            <div class="vy-blog-widget__head">
                                <i class="fa-solid fa-arrow-up-wide-short"></i>
                                <?php esc_html_e('Sắp xếp', 'voya'); ?>
                            </div>
                            <div class="vy-blog-widget__body">
                                <ul class="vy-blog-cat-list">
                                    <?php
                                    $sort_labels = [
                                        'date_desc' => __('Mới nhất', 'voya'),
                                        'date_asc' => __('Cũ nhất', 'voya'),
                                        'title_asc' => __('A → Z', 'voya'),
                                        'popular' => __('Phổ biến', 'voya'),
                                    ];
                                    foreach ($sort_labels as $sk => $sl):
                                        $sort_url = vy_home_sort_url($base_url, $current_cat, $sk);
                                        $is_active = $current_sort === $sk;
                                        ?>
                                    <li>
                                        <a href="<?php echo esc_url($sort_url); ?>"
                                            class="vy-blog-cat-link <?php echo $is_active ? 'is-active' : ''; ?>">
                                            <span class="vy-blog-cat-dot"></span>
                                            <?php echo esc_html($sl); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Tags -->
                        <?php if (!empty($all_tags)): ?>
                        <div class="vy-blog-widget">
                            <div class="vy-blog-widget__head">
                                <i class="fa-solid fa-tags"></i>
                                <?php esc_html_e('Tags', 'voya'); ?>
                            </div>
                            <div class="vy-blog-widget__body">
                                <div class="vy-blog-tags">
                                    <?php foreach ($all_tags as $tag): ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="vy-blog-tag">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div><!-- /#vy-blog-sidebar-inner -->
                </aside>

                <!-- ── MAIN CONTENT ── -->
                <div class="vy-blog-main">

                    <?php if ($loop_query->have_posts()): ?>

                    <div class="vy-blog-grid" id="vy-blog-grid">

                        <?php while ($loop_query->have_posts()):
                                $loop_query->the_post(); ?>
                        <?php
                                $p_id = get_the_ID();
                                $p_img = get_the_post_thumbnail_url($p_id, 'large') ?: get_the_post_thumbnail_url($p_id, 'full');
                                $p_cats = get_the_category($p_id);
                                $p_author = get_the_author_meta('display_name', get_post_field('post_author', $p_id));
                                $p_excerpt = get_post_field('post_excerpt', $p_id)
                                    ?: wp_trim_words(strip_tags(get_post_field('post_content', $p_id)), 22, '…');
                                $p_date = get_the_date('d/m/Y');
                                $read_time = max(1, ceil(str_word_count(strip_tags(get_post_field('post_content', $p_id))) / 200));
                                ?>

                        <article class="vy-blog-card" itemscope itemtype="https://schema.org/BlogPosting">

                            <!-- Thumbnail -->
                            <a href="<?php the_permalink(); ?>" class="vy-blog-card__img-link" tabindex="-1"
                                aria-hidden="true">
                                <div class="vy-blog-card__img-wrap">
                                    <?php if ($p_img): ?>
                                    <img src="<?php echo esc_url($p_img); ?>" alt="<?php the_title_attribute(); ?>"
                                        loading="lazy" class="vy-blog-card__img" itemprop="image">
                                    <?php else: ?>
                                    <div class="vy-blog-card__img-placeholder">
                                        <i class="fa-regular fa-newspaper"></i>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($p_cats)): ?>
                                    <span class="vy-blog-card__cat-badge">
                                        <?php echo esc_html($p_cats[0]->name); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </a>

                            <!-- Info -->
                            <div class="vy-blog-card__body">

                                <div class="vy-blog-card__meta">
                                    <span class="vy-blog-card__date">
                                        <i class="fa-regular fa-calendar-days"></i>
                                        <time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>"
                                            itemprop="datePublished">
                                            <?php echo esc_html($p_date); ?>
                                        </time>
                                    </span>
                                    <span class="vy-blog-card__read">
                                        <i class="fa-regular fa-clock"></i>
                                        <?php echo esc_html($read_time); ?> min
                                    </span>
                                </div>

                                <h2 class="vy-blog-card__title" itemprop="headline">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>

                                <p class="vy-blog-card__excerpt" itemprop="description">
                                    <?php echo esc_html(wp_trim_words($p_excerpt, 22, '…')); ?>
                                </p>

                                <div class="vy-blog-card__foot">
                                    <?php if ($p_author): ?>
                                    <span class="vy-blog-card__author" itemprop="author">
                                        <?php echo get_avatar(get_post_field('post_author', $p_id), 22, '', '', ['class' => 'vy-blog-card__avatar']); ?>
                                        <a
                                            href="<?php echo esc_url(get_author_posts_url(get_post_field('post_author', $p_id))); ?>">
                                            <?php echo esc_html($p_author); ?>
                                        </a>
                                    </span>
                                    <?php endif; ?>
                                    <a href="<?php the_permalink(); ?>" class="vy-blog-card__more">
                                        <?php esc_html_e('Đọc thêm', 'voya'); ?>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>

                            </div><!-- /.vy-blog-card__body -->
                        </article>

                        <?php endwhile; ?>
                        <?php if ($use_custom)
                                wp_reset_postdata(); ?>

                    </div><!-- /.vy-blog-grid -->

                    <!-- Pagination -->
                    <?php if ($max_pages > 1):
                            $paged_base = add_query_arg('paged', '%#%', $current_archive_url);
                            if ($current_sort !== 'date_desc')
                                $paged_base = add_query_arg('sort', $current_sort, $paged_base);
                            $pages = paginate_links([
                                'base' => $paged_base,
                                'format' => '',
                                'current' => $paged,
                                'total' => $max_pages,
                                'prev_text' => '<i class="fa-solid fa-chevron-left"></i>',
                                'next_text' => '<i class="fa-solid fa-chevron-right"></i>',
                                'type' => 'array',
                                'end_size' => 2,
                                'mid_size' => 2,
                            ]);
                            if ($pages): ?>
                    <nav class="vy-blog-pagination" aria-label="Phân trang">
                        <ul class="vy-blog-pagination__list">
                            <?php foreach ($pages as $pg): ?>
                            <li><?php echo $pg; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <?php endif; endif; ?>

                    <?php else: ?>

                    <!-- Empty state -->
                    <div class="vy-blog-empty">
                        <i class="fa-regular fa-newspaper vy-blog-empty__icon"></i>
                        <h3><?php esc_html_e('Chưa có bài viết nào', 'voya'); ?></h3>
                        <p><?php esc_html_e('Thử thay đổi bộ lọc hoặc quay lại trang blog.', 'voya'); ?></p>
                        <a href="<?php echo esc_url($base_url); ?>" class="vy-blog-empty__btn">
                            <?php esc_html_e('Xem tất cả bài viết', 'voya'); ?>
                        </a>
                    </div>

                    <?php endif; ?>

                </div><!-- /.vy-blog-main -->

            </div><!-- /.vy-blog-layout -->
        </div>
    </div>

</main>

<?php get_footer(); ?>