<?php
/**
 * VOYA — archive.php
 * Blog archive page cho APK site
 * post_type: post | Layout: sidebar (filter/sort/tags) + main (article grid 2-col)
 */

get_header();

// ── Filter / sort state ──────────────────────────────────────
$current_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
$current_sort = isset($_GET['sort']) ? sanitize_key($_GET['sort']) : 'date_desc';
$current_tag = isset($_GET['tag']) ? intval($_GET['tag']) : 0;

// Detect WP context
if (!$current_cat && is_category()) {
    $q_obj = get_queried_object();
    $current_cat = $q_obj ? $q_obj->term_id : 0;
}
if (!$current_tag && is_tag()) {
    $q_obj = get_queried_object();
    $current_tag = $q_obj ? $q_obj->term_id : 0;
}

// ── Sort map ─────────────────────────────────────────────────
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

// ── Pagination ───────────────────────────────────────────────
$paged = max(1, get_query_var('paged') ?: (get_query_var('page') ?: 1));
$per_page = 10;

// ── Build WP_Query ───────────────────────────────────────────
$q_args = [
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'paged' => $paged,
    'orderby' => $sort['orderby'],
    'order' => $sort['order'],
    'no_found_rows' => false,
];

if ($current_cat)
    $q_args['cat'] = $current_cat;
if ($current_tag)
    $q_args['tag_id'] = $current_tag;

if (is_tag()) {
    $to = get_queried_object();
    if ($to)
        $q_args['tag_id'] = $to->term_id;
}
if (is_author()) {
    $ao = get_queried_object();
    if ($ao)
        $q_args['author'] = $ao->ID;
}
if (is_year())
    $q_args['year'] = get_query_var('year');
if (is_month()) {
    $q_args['year'] = get_query_var('year');
    $q_args['monthnum'] = get_query_var('monthnum');
}
if (is_search())
    $q_args['s'] = get_search_query();

$archive_q = new WP_Query($q_args);
$total_posts = $archive_q->found_posts;
$max_pages = $archive_q->max_num_pages;
$all_posts = $archive_q->posts;
wp_reset_postdata();

// ── Page title ───────────────────────────────────────────────
if ($current_cat && ($fcat = get_category($current_cat))) {
    $page_title = $fcat->name;
} elseif (is_category()) {
    $page_title = single_cat_title('', false);
} elseif (is_tag()) {
    $page_title = 'Tag: ' . single_tag_title('', false);
} elseif (is_author()) {
    $page_title = get_the_author_meta('display_name', get_queried_object_id());
} elseif (is_search()) {
    $page_title = sprintf(__('Tìm kiếm: %s', 'voya'), get_search_query());
} else {
    $page_title = __('Blog', 'voya');
}

// ── Blog base URL ─────────────────────────────────────────────
$blog_page_id = get_option('page_for_posts');
$base_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');

// ── Current archive URL (for sort links) ────────────────────
if (is_category() && isset($fcat))
    $current_archive_url = get_category_link($fcat->term_id);
elseif (is_tag())
    $current_archive_url = get_tag_link(get_queried_object_id());
elseif (is_author())
    $current_archive_url = get_author_posts_url(get_queried_object_id());
elseif ($current_cat)
    $current_archive_url = get_category_link($current_cat);
else
    $current_archive_url = $base_url;

// ── Sort URL builder ─────────────────────────────────────────
function vy_blog_sort_url($archive_url, $sort_key)
{
    $params = $sort_key !== 'date_desc' ? ['sort' => $sort_key] : [];
    return $params ? add_query_arg($params, $archive_url) : $archive_url;
}
?>

<main class="vy-blog-archive" id="vy-blog-main">

    <!-- ════════════════════════════════════════════
         PAGE HEADER — title bar (no full hero)
    ════════════════════════════════════════════ -->
    <div class="vy-blog-header">
        <div class="vy-blog-header__inner">
            <div class="vy-blog-header__left">
                <h1 class="vy-blog-header__title"><?php echo esc_html($page_title); ?></h1>
                <!-- Breadcrumb -->
                <nav class="vy-blog-breadcrumb" aria-label="Breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Trang chủ', 'voya'); ?></a>
                    <span>/</span>
                    <?php if ($page_title !== __('Blog', 'voya')): ?>
                    <a href="<?php echo esc_url($base_url); ?>"><?php esc_html_e('Blog', 'voya'); ?></a>
                    <span>/</span>
                    <span><?php echo esc_html($page_title); ?></span>
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

                <!-- ────────────────────────────
                     SIDEBAR
                ──────────────────────────── -->
                <aside class="vy-blog-sidebar" aria-label="Bộ lọc bài viết">

                    <!-- Mobile toggle button — injected by JS -->

                    <div class="vy-blog-sidebar__inner" id="vy-blog-sidebar-inner">

                        <!-- Filter: Categories -->
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
                                            class="vy-blog-cat-link <?php echo (!$current_cat && !is_category() && !is_tag() && !is_author()) ? 'is-active' : ''; ?>">
                                            <span class="vy-blog-cat-dot"></span>
                                            <?php esc_html_e('Tất cả bài viết', 'voya'); ?>
                                        </a>
                                    </li>

                                    <?php foreach ($all_cats as $ci):
                                        $cat_url = get_category_link($ci->term_id);
                                        $is_active = $current_cat === $ci->term_id
                                            || (is_category() && get_queried_object_id() === $ci->term_id);
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

                        <!-- Filter: Sort -->
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
                                        $sort_url = vy_blog_sort_url($current_archive_url, $sk);
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

                        <!-- Tags cloud -->
                        <?php if (!empty($all_tags)): ?>
                        <div class="vy-blog-widget">
                            <div class="vy-blog-widget__head">
                                <i class="fa-solid fa-tags"></i>
                                <?php esc_html_e('Tags', 'voya'); ?>
                            </div>
                            <div class="vy-blog-widget__body">
                                <div class="vy-blog-tags">
                                    <?php foreach ($all_tags as $tag):
                                            $is_active_tag = is_tag() && get_queried_object_id() === $tag->term_id;
                                            ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                                        class="vy-blog-tag <?php echo $is_active_tag ? 'is-active' : ''; ?>">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div><!-- /.vy-blog-sidebar__inner -->
                </aside>

                <!-- ────────────────────────────
                     MAIN CONTENT
                ──────────────────────────── -->
                <div class="vy-blog-main">

                    <?php if (!empty($all_posts)): ?>

                    <!-- Article grid: 2 cols -->
                    <div class="vy-blog-grid" id="vy-blog-grid">
                        <?php foreach ($all_posts as $p):
                                $p_img = get_the_post_thumbnail_url($p->ID, 'large') ?: get_the_post_thumbnail_url($p->ID, 'full');
                                $p_cats = get_the_category($p->ID);
                                $p_author = get_the_author_meta('display_name', $p->post_author);
                                $p_excerpt = get_post_field('post_excerpt', $p->ID)
                                    ?: wp_trim_words(strip_tags(get_post_field('post_content', $p->ID)), 22, '…');
                                $p_date = get_the_date('d/m/Y', $p->ID);
                                $read_time = max(1, ceil(str_word_count(strip_tags(get_post_field('post_content', $p->ID))) / 200));
                                ?>
                        <article class="vy-blog-card" itemscope itemtype="https://schema.org/BlogPosting">

                            <!-- Thumbnail -->
                            <a href="<?php echo esc_url(get_permalink($p->ID)); ?>" class="vy-blog-card__img-link"
                                tabindex="-1" aria-hidden="true">
                                <div class="vy-blog-card__img-wrap">
                                    <?php if ($p_img): ?>
                                    <img src="<?php echo esc_url($p_img); ?>"
                                        alt="<?php echo esc_attr($p->post_title); ?>" loading="lazy"
                                        class="vy-blog-card__img" itemprop="image">
                                    <?php else: ?>
                                    <div class="vy-blog-card__img-placeholder">
                                        <i class="fa-regular fa-newspaper"></i>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Category badge -->
                                    <?php if (!empty($p_cats)): ?>
                                    <span class="vy-blog-card__cat-badge">
                                        <?php echo esc_html($p_cats[0]->name); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </a>

                            <!-- Info -->
                            <div class="vy-blog-card__body">

                                <!-- Meta: date + read time -->
                                <div class="vy-blog-card__meta">
                                    <span class="vy-blog-card__date">
                                        <i class="fa-regular fa-calendar-days"></i>
                                        <time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $p->ID)); ?>"
                                            itemprop="datePublished">
                                            <?php echo esc_html($p_date); ?>
                                        </time>
                                    </span>
                                    <span class="vy-blog-card__read">
                                        <i class="fa-regular fa-clock"></i>
                                        <?php echo esc_html($read_time); ?> min
                                    </span>
                                </div>

                                <!-- Title -->
                                <h2 class="vy-blog-card__title" itemprop="headline">
                                    <a href="<?php echo esc_url(get_permalink($p->ID)); ?>">
                                        <?php echo esc_html($p->post_title); ?>
                                    </a>
                                </h2>

                                <!-- Excerpt -->
                                <p class="vy-blog-card__excerpt" itemprop="description">
                                    <?php echo esc_html(wp_trim_words($p_excerpt, 22, '…')); ?>
                                </p>

                                <!-- Footer: author + read more -->
                                <div class="vy-blog-card__foot">
                                    <?php if ($p_author): ?>
                                    <span class="vy-blog-card__author" itemprop="author">
                                        <?php echo get_avatar($p->post_author, 22, '', '', ['class' => 'vy-blog-card__avatar']); ?>
                                        <a href="<?php echo esc_url(get_author_posts_url($p->post_author)); ?>">
                                            <?php echo esc_html($p_author); ?>
                                        </a>
                                    </span>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url(get_permalink($p->ID)); ?>" class="vy-blog-card__more">
                                        <?php esc_html_e('Đọc thêm', 'voya'); ?>
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>

                            </div><!-- /.vy-blog-card__body -->
                        </article>
                        <?php endforeach; ?>
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
                        <p><?php esc_html_e('Thử thay đổi bộ lọc hoặc xem tất cả bài viết.', 'voya'); ?></p>
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