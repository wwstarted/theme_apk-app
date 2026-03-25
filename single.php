<?php
/**
 * VOYA — single.php
 * Blog single post — APK Site style
 * Layout: compact header + 2-col (content | sidebar)
 */

get_header();

while (have_posts()):
    the_post();

    $post_id = get_the_ID();
    $thumb_id = get_post_thumbnail_id($post_id);

    // Featured image for header
    $banner_url = '';
    if ($thumb_id) {
        $banner_url = wp_get_attachment_image_url($thumb_id, 'large')
            ?: wp_get_attachment_image_url($thumb_id, 'full');
    }

    $categories = get_the_category();
    $cat = $categories[0] ?? null;
    $tags = get_the_tags();

    $author_id = (int) get_the_author_meta('ID');
    $author_name = get_the_author();
    $author_bio = get_the_author_meta('description')
        ?: __('Chuyên viết về tải game APK, game mod và ứng dụng di động. Chia sẻ thông tin đơn giản, giúp bạn tìm và tối ưu trải nghiệm game một cách hiệu quả.', 'voya');
    $author_url = get_author_posts_url($author_id);
    $author_avatar = get_avatar_url($author_id, ['size' => 72]);
    $author_fb = get_the_author_meta('facebook', $author_id);
    $author_tw = get_the_author_meta('twitter', $author_id);
    $author_ig = get_the_author_meta('instagram', $author_id);

    $created_date = get_the_date('Y-m-d H:i:s');
    $created_nice = get_the_date('d/m/Y');
    $modified_date = get_the_modified_date('Y-m-d H:i:s');
    $modified_nice = get_the_modified_date('d/m/Y');

    $post_url_enc = rawurlencode(get_permalink());
    $post_title_enc = rawurlencode(get_the_title());

    $prev_post = get_previous_post();
    $next_post = get_next_post();

    $blog_page_id = get_option('page_for_posts');
    $blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');

    $reading_time = max(1, (int) ceil(str_word_count(wp_strip_all_tags(get_the_content())) / 200));

    $short_desc = has_excerpt()
        ? get_the_excerpt()
        : wp_trim_words(wp_strip_all_tags(get_the_content()), 28, '…');
    ?>

<main class="vy-single-blog" id="vy-single-main">

    <!-- ════════════════════════════════════════════
         PAGE HEADER — compact, no full banner
    ════════════════════════════════════════════ -->
    <div class="vy-single-blog__header">
        <div class="vy-single-blog__header-inner">
            <!-- Breadcrumb -->
            <nav class="vy-single-blog__breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <?php esc_html_e('Trang chủ', 'voya'); ?>
                </a>
                <span>/</span>
                <a href="<?php echo esc_url($blog_url); ?>">
                    <?php esc_html_e('Blog', 'voya'); ?>
                </a>
                <?php if ($cat): ?>
                <span>/</span>
                <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
                    <?php echo esc_html($cat->name); ?>
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         BODY: content + sidebar
    ════════════════════════════════════════════ -->
    <div class="vy-single-blog__body">
        <div class="vy-single-blog__container">
            <div class="vy-single-blog__layout">

                <!-- ── MAIN CONTENT ── -->
                <div class="vy-single-blog__main">
                    <article class="vy-single-blog__article" id="vy-article" itemscope
                        itemtype="https://schema.org/BlogPosting">

                        <meta itemprop="headline" content="<?php the_title_attribute(); ?>">
                        <meta itemprop="datePublished" content="<?php echo esc_attr(get_the_date('c')); ?>">
                        <meta itemprop="author" content="<?php echo esc_attr($author_name); ?>">

                        <!-- Category badge -->
                        <?php if ($cat): ?>
                        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                            class="vy-single-blog__cat-badge">
                            <?php echo esc_html($cat->name); ?>
                        </a>
                        <?php endif; ?>

                        <!-- Title -->
                        <h1 class="vy-single-blog__title" itemprop="headline">
                            <?php the_title(); ?>
                        </h1>

                        <!-- Meta bar -->
                        <div class="vy-single-blog__meta">
                            <span class="vy-single-blog__meta-item">
                                <i class="fa-regular fa-calendar-days"></i>
                                <time datetime="<?php echo esc_attr($created_date); ?>" itemprop="datePublished">
                                    <?php echo esc_html($created_nice); ?>
                                </time>
                            </span>
                            <span class="vy-single-blog__meta-item">
                                <i class="fa-regular fa-user"></i>
                                <a href="<?php echo esc_url($author_url); ?>" itemprop="author">
                                    <?php echo esc_html($author_name); ?>
                                </a>
                            </span>
                            <span class="vy-single-blog__meta-item">
                                <i class="fa-regular fa-clock"></i>
                                <?php echo esc_html($reading_time); ?> phút đọc
                            </span>
                            <?php if ($modified_date !== $created_date): ?>
                            <span class="vy-single-blog__meta-item vy-single-blog__meta-item--updated">
                                <i class="fa-solid fa-rotate"></i>
                                <?php esc_html_e('Cập nhật:', 'voya'); ?>
                                <time datetime="<?php echo esc_attr($modified_date); ?>">
                                    <?php echo esc_html($modified_nice); ?>
                                </time>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Featured image -->
                        <?php if ($banner_url): ?>
                        <div class="vy-single-blog__featured-img" itemprop="image">
                            <img src="<?php echo esc_url($banner_url); ?>" alt="<?php the_title_attribute(); ?>"
                                loading="eager" class="vy-single-blog__featured-img-el">
                        </div>
                        <?php endif; ?>

                        <!-- Short excerpt -->
                        <?php if ($short_desc): ?>
                        <p class="vy-single-blog__excerpt" itemprop="description">
                            <?php echo esc_html($short_desc); ?>
                        </p>
                        <?php endif; ?>

                        <!-- TOC — JS inject -->
                        <aside class="vy-toc" id="vy-toc" aria-label="Mục lục" style="display:none;" hidden>
                            <div class="vy-toc__head" role="button" tabindex="0" aria-expanded="true"
                                aria-controls="vy-toc-list">
                                <span class="vy-toc__title">
                                    <i class="fa-solid fa-list-ul" aria-hidden="true"></i>
                                    <?php esc_html_e('Mục lục', 'voya'); ?>
                                </span>
                                <button class="vy-toc__toggle" type="button" aria-label="Thu gọn mục lục">
                                    <i class="fa-solid fa-chevron-up vy-toc__arrow" aria-hidden="true"></i>
                                </button>
                            </div>
                            <nav class="vy-toc__body" id="vy-toc-list">
                                <ol class="vy-toc__list"></ol>
                            </nav>
                        </aside>

                        <!-- Article body -->
                        <div class="vy-single-blog__content" itemprop="articleBody">
                            <?php the_content(); ?>
                        </div>

                        <!-- Multi-page -->
                        <?php wp_link_pages([
                                'before' => '<div class="vy-single-blog__pages"><span>' . __('Trang:', 'voya') . '</span>',
                                'after' => '</div>',
                                'link_before' => '<span>',
                                'link_after' => '</span>',
                            ]); ?>

                        <!-- ── Footer: Tags + Share ── -->
                        <footer class="vy-single-blog__footer">

                            <?php if ($tags): ?>
                            <div class="vy-single-blog__tags">
                                <?php foreach ($tags as $tag): ?>
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                                    class="vy-single-blog__tag">
                                    #
                                    <?php echo esc_html($tag->name); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <div class="vy-single-blog__share" role="group" aria-label="Chia sẻ bài viết">
                                <span class="vy-single-blog__share-label">
                                    <?php esc_html_e('Chia sẻ', 'voya'); ?>
                                </span>
                                <div class="vy-single-blog__share-links">
                                    <a href="https://www.facebook.com/sharer.php?u=<?php echo $post_url_enc; ?>"
                                        class="vy-share-link vy-share-link--fb" target="_blank"
                                        rel="noopener noreferrer" aria-label="Chia sẻ Facebook">
                                        <i class="fa-brands fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo $post_url_enc; ?>&text=<?php echo $post_title_enc; ?>"
                                        class="vy-share-link vy-share-link--tw" target="_blank"
                                        rel="noopener noreferrer" aria-label="Chia sẻ Twitter">
                                        <i class="fa-brands fa-x-twitter"></i>
                                    </a>
                                    <a href="https://t.me/share/url?url=<?php echo $post_url_enc; ?>"
                                        class="vy-share-link vy-share-link--tg" target="_blank"
                                        rel="noopener noreferrer" aria-label="Chia sẻ Telegram">
                                        <i class="fa-brands fa-telegram"></i>
                                    </a>
                                    <a href="https://pinterest.com/pin/create/button/?url=<?php echo $post_url_enc; ?>"
                                        class="vy-share-link vy-share-link--pt" target="_blank"
                                        rel="noopener noreferrer" aria-label="Chia sẻ Pinterest">
                                        <i class="fa-brands fa-pinterest-p"></i>
                                    </a>
                                </div>
                            </div>

                        </footer>

                        <!-- ── Prev / Next ── -->
                        <?php if ($prev_post || $next_post): ?>
                        <nav class="vy-single-blog__nav" aria-label="Điều hướng bài viết">
                            <?php if ($prev_post):
                                        $prev_img = get_the_post_thumbnail_url($prev_post->ID, 'thumbnail');
                                        ?>
                            <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>"
                                class="vy-blog-nav-item vy-blog-nav-item--prev" rel="prev">
                                <i class="fa-solid fa-arrow-left"></i>
                                <?php if ($prev_img): ?>
                                <div class="vy-blog-nav-item__thumb">
                                    <img src="<?php echo esc_url($prev_img); ?>"
                                        alt="<?php echo esc_attr($prev_post->post_title); ?>" loading="lazy">
                                </div>
                                <?php endif; ?>
                                <div class="vy-blog-nav-item__text">
                                    <span class="vy-blog-nav-item__label">
                                        <?php esc_html_e('Bài trước', 'voya'); ?>
                                    </span>
                                    <span class="vy-blog-nav-item__title">
                                        <?php echo esc_html(wp_trim_words($prev_post->post_title, 7, '…')); ?>
                                    </span>
                                </div>
                            </a>
                            <?php else: ?>
                            <div class="vy-blog-nav-item vy-blog-nav-item--empty"></div>
                            <?php endif; ?>

                            <?php if ($next_post):
                                        $next_img = get_the_post_thumbnail_url($next_post->ID, 'thumbnail');
                                        ?>
                            <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>"
                                class="vy-blog-nav-item vy-blog-nav-item--next" rel="next">
                                <div class="vy-blog-nav-item__text">
                                    <span class="vy-blog-nav-item__label">
                                        <?php esc_html_e('Bài tiếp theo', 'voya'); ?>
                                    </span>
                                    <span class="vy-blog-nav-item__title">
                                        <?php echo esc_html(wp_trim_words($next_post->post_title, 7, '…')); ?>
                                    </span>
                                </div>
                                <?php if ($next_img): ?>
                                <div class="vy-blog-nav-item__thumb">
                                    <img src="<?php echo esc_url($next_img); ?>"
                                        alt="<?php echo esc_attr($next_post->post_title); ?>" loading="lazy">
                                </div>
                                <?php endif; ?>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <?php else: ?>
                            <div class="vy-blog-nav-item vy-blog-nav-item--empty"></div>
                            <?php endif; ?>
                        </nav>
                        <?php endif; ?>

                        <!-- ── Author Box ── -->
                        <div class="vy-single-blog__author">
                            <div class="vy-single-blog__author-avatar">
                                <a href="<?php echo esc_url($author_url); ?>">
                                    <img src="<?php echo esc_url($author_avatar); ?>"
                                        alt="<?php echo esc_attr($author_name); ?>" width="64" height="64"
                                        loading="lazy">
                                </a>
                            </div>
                            <div class="vy-single-blog__author-body">
                                <div class="vy-single-blog__author-top">
                                    <h5 class="vy-single-blog__author-name">
                                        <a href="<?php echo esc_url($author_url); ?>">
                                            <?php echo esc_html($author_name); ?>
                                        </a>
                                    </h5>
                                    <div class="vy-single-blog__author-social">
                                        <?php if ($author_fb): ?>
                                        <a href="<?php echo esc_url($author_fb); ?>" target="_blank" rel="noopener"
                                            aria-label="Facebook">
                                            <i class="fa-brands fa-facebook-f"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($author_tw): ?>
                                        <a href="<?php echo esc_url($author_tw); ?>" target="_blank" rel="noopener"
                                            aria-label="Twitter">
                                            <i class="fa-brands fa-x-twitter"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($author_ig): ?>
                                        <a href="<?php echo esc_url($author_ig); ?>" target="_blank" rel="noopener"
                                            aria-label="Instagram">
                                            <i class="fa-brands fa-instagram"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!$author_fb && !$author_tw && !$author_ig): ?>
                                        <a href="<?php echo esc_url($author_url); ?>"
                                            class="vy-single-blog__author-all">
                                            <?php esc_html_e('Xem tất cả bài viết', 'voya'); ?>
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="vy-single-blog__author-bio">
                                    <?php echo esc_html($author_bio); ?>
                                </p>
                            </div>
                        </div>

                        <!-- ── Comments ── -->
                        <?php if (comments_open() || get_comments_number()): ?>
                        <div class="vy-single-blog__comments">
                            <?php comments_template(); ?>
                        </div>
                        <?php endif; ?>

                    </article>
                </div><!-- /.vy-single-blog__main -->


                <!-- ── SIDEBAR ── -->
                <aside class="vy-single-blog__sidebar" aria-label="Sidebar">
                    <div class="vy-single-blog__sidebar-sticky">

                        <!-- Related posts -->
                        <?php
                            $rel_args = $cat ? [
                                'category__in' => [$cat->term_id],
                                'post__not_in' => [$post_id],
                                'posts_per_page' => 5,
                                'orderby' => 'rand',
                                'post_status' => 'publish',
                                'no_found_rows' => true,
                            ] : [
                                'post__not_in' => [$post_id],
                                'posts_per_page' => 5,
                                'orderby' => 'date',
                                'post_status' => 'publish',
                                'no_found_rows' => true,
                            ];
                            $rel_q = new WP_Query($rel_args);
                            if ($rel_q->have_posts()): ?>
                        <div class="vy-blog-widget">
                            <div class="vy-blog-widget__head">
                                <i class="fa-solid fa-fire"></i>
                                <?php esc_html_e('Bài viết liên quan', 'voya'); ?>
                            </div>
                            <div class="vy-blog-sidebar-list">
                                <?php while ($rel_q->have_posts()):
                                            $rel_q->the_post();
                                            $ri = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'); ?>
                                <div class="vy-blog-sidebar-item">
                                    <a href="<?php the_permalink(); ?>" class="vy-blog-sidebar-link">
                                        <div class="vy-blog-sidebar-thumb">
                                            <?php if ($ri): ?>
                                            <img src="<?php echo esc_url($ri); ?>" alt="<?php the_title_attribute(); ?>"
                                                loading="lazy">
                                            <?php else: ?>
                                            <div class="vy-blog-sidebar-thumb__placeholder">
                                                <i class="fa-regular fa-image"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="vy-blog-sidebar-info">
                                            <span class="vy-blog-sidebar-title">
                                                <?php the_title(); ?>
                                            </span>
                                            <span class="vy-blog-sidebar-date">
                                                <i class="fa-regular fa-calendar-days"></i>
                                                <?php echo get_the_date('d/m/Y'); ?>
                                            </span>
                                        </div>
                                    </a>
                                </div>
                                <?php endwhile;
                                        wp_reset_postdata(); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Latest posts -->
                        <?php $lat_q = new WP_Query([
                                'posts_per_page' => 5,
                                'post__not_in' => [$post_id],
                                'post_status' => 'publish',
                                'orderby' => 'date',
                                'order' => 'DESC',
                                'no_found_rows' => true,
                                'ignore_sticky_posts' => true,
                            ]);
                            if ($lat_q->have_posts()): ?>
                        <div class="vy-blog-widget">
                            <div class="vy-blog-widget__head">
                                <i class="fa-solid fa-bolt"></i>
                                <?php esc_html_e('Bài viết mới nhất', 'voya'); ?>
                            </div>
                            <div class="vy-blog-sidebar-list">
                                <?php while ($lat_q->have_posts()):
                                            $lat_q->the_post();
                                            $li = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'); ?>
                                <div class="vy-blog-sidebar-item">
                                    <a href="<?php the_permalink(); ?>" class="vy-blog-sidebar-link">
                                        <div class="vy-blog-sidebar-thumb">
                                            <?php if ($li): ?>
                                            <img src="<?php echo esc_url($li); ?>" alt="<?php the_title_attribute(); ?>"
                                                loading="lazy">
                                            <?php else: ?>
                                            <div class="vy-blog-sidebar-thumb__placeholder">
                                                <i class="fa-regular fa-image"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="vy-blog-sidebar-info">
                                            <span class="vy-blog-sidebar-title">
                                                <?php the_title(); ?>
                                            </span>
                                            <span class="vy-blog-sidebar-date">
                                                <i class="fa-regular fa-calendar-days"></i>
                                                <?php echo get_the_date('d/m/Y'); ?>
                                            </span>
                                        </div>
                                    </a>
                                </div>
                                <?php endwhile;
                                        wp_reset_postdata(); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Categories -->
                        <div class="vy-blog-widget">
                            <div class="vy-blog-widget__head">
                                <i class="fa-solid fa-layer-group"></i>
                                <?php esc_html_e('Danh mục', 'voya'); ?>
                            </div>
                            <div class="vy-blog-widget__body vy-blog-widget__body--cats">
                                <?php
                                    $sidebar_cats = get_categories(['hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 10]);
                                    foreach ($sidebar_cats as $sc):
                                        $is_cur = $cat && $cat->term_id === $sc->term_id;
                                        ?>
                                <a href="<?php echo esc_url(get_category_link($sc->term_id)); ?>"
                                    class="vy-blog-cat-link <?php echo $is_cur ? 'is-active' : ''; ?>">
                                    <span class="vy-blog-cat-dot"></span>
                                    <?php echo esc_html($sc->name); ?>
                                    <span class="vy-blog-cat-count">
                                        <?php echo esc_html($sc->count); ?>
                                    </span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Tags -->
                        <?php if ($tags): ?>
                        <div class="vy-blog-widget">
                            <div class="vy-blog-widget__head">
                                <i class="fa-solid fa-tags"></i>
                                <?php esc_html_e('Tags', 'voya'); ?>
                            </div>
                            <div class="vy-blog-widget__body">
                                <div class="vy-blog-tags">
                                    <?php foreach ($tags as $tag): ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="vy-blog-tag">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div><!-- /.vy-single-blog__sidebar-sticky -->
                </aside>

            </div><!-- /.vy-single-blog__layout -->
        </div>
    </div>

</main>

<?php endwhile; ?>
<?php get_footer(); ?>