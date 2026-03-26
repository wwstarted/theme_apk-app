<?php
/**
 * VOYA — front-page.php
 * Homepage
 * Sections:
 * - Section 1: Games (New/Latest) - 24 items, 3 columns
 * - Section 2: Apps (New/Latest) - 24 items, 3 columns
 * - Section 3: Categories
 * - Section 4: Tin tức mới nhất - 6 bài, 3×2 grid
 */

get_header();
?>

<main id="vy-main" class="vy-main-content">

    <!-- SECTION BANNER -->
    <section class="section vy-banner-section" id="section_banner">
        <div class="section-bg fill"></div>
        <div class="section-content relative">
            <div class="vy-banner-inner">
                <h1 class="vy-banner-title">
                    <?php esc_html_e('Trang Tải Game Mod, App, Apk Miễn Phí, Uy Tín', 'voya'); ?>
                    <span class="vy-banner-emoji" aria-hidden="true">🎮 📱 ⚡</span>
                </h1>
            </div>
        </div>
    </section>

    <!-- ============================================================
         SECTION 1 – GAMES (New/Latest)
    ============================================================ -->
    <section class="section" id="section_869393426">
        <div class="section-bg fill"></div>
        <div class="section-content relative">
            <div class="row">
                <div class="col small-12 large-12">
                    <div class="col-inner">
                        <div class="row">
                            <div class="col small-12 large-12">
                                <div class="col-inner text-left">
                                    <h2><?php esc_html_e('GAME Mới Cập Nhật', 'voya'); ?></h2>

                                    <div class="apk-grid">
                                        <?php
                                        $games_args = array(
                                            'post_type' => 'product',
                                            'posts_per_page' => 24,
                                            'orderby' => 'date',
                                            'order' => 'DESC',
                                            'tax_query' => array(
                                                array(
                                                    'taxonomy' => 'product_cat',
                                                    'field' => 'slug',
                                                    'terms' => 'game',
                                                ),
                                            ),
                                        );

                                        $games_query = new WP_Query($games_args);

                                        if ($games_query->have_posts()):
                                            while ($games_query->have_posts()):
                                                $games_query->the_post();
                                                $download_url = get_post_meta(get_the_ID(), '_vy_download_url', true);
                                                $rating = floatval(get_post_meta(get_the_ID(), '_vy_product_rating', true) ?: 0);
                                                $full_stars = (int) $rating;
                                                $terms = get_the_terms(get_the_ID(), 'product_cat');
                                                ?>
                                        <div class="apk-item">
                                            <div class="apk-thumbnail">
                                                <?php
                                                        if (has_post_thumbnail()) {
                                                            the_post_thumbnail('thumbnail', array('alt' => get_the_title()));
                                                        } else {
                                                            echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title()) . '">';
                                                        }
                                                        ?>
                                            </div>
                                            <div class="apk-info">
                                                <h3>
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </h3>
                                                <?php if ($terms && !is_wp_error($terms)): ?>
                                                <p class="apk-category">
                                                    <?php
                                                                foreach (array_slice($terms, 0, 2) as $term) {
                                                                    echo '<a href="' . esc_url(get_term_link($term)) . '" rel="tag">' . esc_html($term->name) . '</a>';
                                                                }
                                                                ?>
                                                </p>
                                                <?php endif; ?>
                                                <div class="box-bottom">
                                                    <div class="rating" data-stars="<?php echo esc_attr($rating); ?>">
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <img src="<?php echo esc_url(get_theme_file_uri($i < $full_stars ? '/images/star-full.png' : '/images/star-empty.png')); ?>"
                                                            alt="star">
                                                        <?php endfor; ?>
                                                    </div>
                                                    <?php if ($download_url): ?>
                                                    <a class="apk-download-btn"
                                                        href="<?php echo esc_url($download_url); ?>" target="_blank"
                                                        rel="noopener">
                                                        <?php esc_html_e('Tải xuống', 'voya'); ?>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                            endwhile;
                                            wp_reset_postdata();
                                        endif;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
        #section_869393426 {
            padding: 30px 0;
        }

        @media (max-width: 768px) {
            #section_869393426 {
                padding: 25px 0;
            }
        }

        @media (max-width: 480px) {
            #section_869393426 {
                padding: 20px 0;
            }
        }
        </style>
    </section>


    <!-- ============================================================
         SECTION 2 – APPS (New/Latest)
    ============================================================ -->
    <section class="section" id="section_1243651119">
        <div class="section-bg fill"></div>
        <div class="section-content relative">
            <div class="row">
                <div class="col small-12 large-12">
                    <div class="col-inner">
                        <div class="row">
                            <div class="col small-12 large-12">
                                <div class="col-inner text-left">
                                    <h2><?php esc_html_e('APP Mới Cập Nhật', 'voya'); ?></h2>

                                    <div class="apk-grid">
                                        <?php
                                        $apps_args = array(
                                            'post_type' => 'product',
                                            'posts_per_page' => 24,
                                            'orderby' => 'date',
                                            'order' => 'DESC',
                                            'tax_query' => array(
                                                array(
                                                    'taxonomy' => 'product_cat',
                                                    'field' => 'slug',
                                                    'terms' => 'app',
                                                ),
                                            ),
                                        );

                                        $apps_query = new WP_Query($apps_args);

                                        if ($apps_query->have_posts()):
                                            while ($apps_query->have_posts()):
                                                $apps_query->the_post();
                                                $download_url = get_post_meta(get_the_ID(), '_vy_download_url', true);
                                                $rating = floatval(get_post_meta(get_the_ID(), '_vy_product_rating', true) ?: 0);
                                                $full_stars = (int) $rating;
                                                $terms = get_the_terms(get_the_ID(), 'product_cat');
                                                ?>
                                        <div class="apk-item">
                                            <div class="apk-thumbnail">
                                                <?php
                                                        if (has_post_thumbnail()) {
                                                            the_post_thumbnail('thumbnail', array('alt' => get_the_title()));
                                                        } else {
                                                            echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr(get_the_title()) . '">';
                                                        }
                                                        ?>
                                            </div>
                                            <div class="apk-info">
                                                <h3>
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </h3>
                                                <?php if ($terms && !is_wp_error($terms)): ?>
                                                <p class="apk-category">
                                                    <?php
                                                                foreach (array_slice($terms, 0, 2) as $term) {
                                                                    echo '<a href="' . esc_url(get_term_link($term)) . '" rel="tag">' . esc_html($term->name) . '</a>';
                                                                }
                                                                ?>
                                                </p>
                                                <?php endif; ?>
                                                <div class="box-bottom">
                                                    <div class="rating" data-stars="<?php echo esc_attr($rating); ?>">
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <img src="<?php echo esc_url(get_theme_file_uri($i < $full_stars ? '/images/star-full.png' : '/images/star-empty.png')); ?>"
                                                            alt="star">
                                                        <?php endfor; ?>
                                                    </div>
                                                    <?php if ($download_url): ?>
                                                    <a class="apk-download-btn"
                                                        href="<?php echo esc_url($download_url); ?>" target="_blank"
                                                        rel="noopener">
                                                        <?php esc_html_e('Tải xuống', 'voya'); ?>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                            endwhile;
                                            wp_reset_postdata();
                                        endif;
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
        #section_1243651119 {
            padding: 30px 0;
        }

        @media (max-width: 768px) {
            #section_1243651119 {
                padding: 25px 0;
            }
        }

        @media (max-width: 480px) {
            #section_1243651119 {
                padding: 20px 0;
            }
        }
        </style>
    </section>


    <!-- ============================================================
         SECTION 3 – CATEGORIES
    ============================================================ -->
    <section class="section" id="section_categories">
        <div class="section-bg fill"></div>
        <div class="section-content relative">
            <div class="row">
                <div class="col small-12 large-12">
                    <div class="col-inner">
                        <div class="row">
                            <div class="col small-12 large-12">
                                <div class="col-inner text-left">
                                    <h2><?php esc_html_e('Danh Mục', 'voya'); ?></h2>

                                    <?php
                                    $game_cats = get_terms(array(
                                        'taxonomy' => 'product_cat',
                                        'parent' => 0,
                                        'number' => 20,
                                        'hide_empty' => true,
                                    ));
                                    ?>

                                    <?php if ($game_cats && !is_wp_error($game_cats)): ?>
                                    <div class="term-list">
                                        <?php foreach ($game_cats as $cat): ?>
                                        <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="term-item">
                                            <?php echo esc_html($cat->name); ?>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
        #section_categories {
            padding: 30px 0;
        }

        @media (max-width: 768px) {
            #section_categories {
                padding: 25px 0;
            }
        }

        @media (max-width: 480px) {
            #section_categories {
                padding: 20px 0;
            }
        }
        </style>
    </section>


    <!-- ============================================================
         SECTION 4 – TIN TỨC MỚI NHẤT
    ============================================================ -->
    <?php
    // ── Load news data ────────────────────────────────────────────
    $news_title = get_option('vy_home_news_title', 'Tin tức mới nhất');
    $news_ids_raw = get_option('vy_home_news_ids', '');
    $news_ids = json_decode($news_ids_raw, true);
    $news_view_more = get_option('vy_home_news_view_more', '');

    // Blog URL fallback
    $blog_page_id = get_option('page_for_posts');
    $blog_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/blog/');
    $news_view_more = $news_view_more ?: $blog_url;

    // Build query
    if (!empty($news_ids) && is_array($news_ids)) {
        $news_args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 6,
            'post__in' => array_slice(array_map('absint', $news_ids), 0, 6),
            'orderby' => 'post__in',
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
        ];
    } else {
        // Fallback: 6 bài mới nhất
        $news_args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 6,
            'orderby' => 'date',
            'order' => 'DESC',
            'no_found_rows' => true,
        ];
    }

    $news_query = new WP_Query($news_args);
    ?>

    <?php if ($news_query->have_posts()): ?>
    <section class="vy-home-news">
        <div class="vy-home-container">

            <div class="vy-home-news__header">
                <h2 class="apk-section-title vy-home-news__title">
                    <?php echo esc_html($news_title); ?>
                </h2>
            </div>

            <div class="vy-home-news__grid">
                <?php while ($news_query->have_posts()):
                        $news_query->the_post();
                        $n_id = get_the_ID();
                        $n_img = get_the_post_thumbnail_url($n_id, 'large') ?: get_the_post_thumbnail_url($n_id, 'full');
                        $n_cats = get_the_category($n_id);
                        $n_date = get_the_date('d/m/Y', $n_id);
                        $n_excerpt = get_post_field('post_excerpt', $n_id)
                            ?: wp_trim_words(strip_tags(get_post_field('post_content', $n_id)), 18, '…');
                        ?>
                <article class="vy-news-card">

                    <a href="<?php the_permalink(); ?>" class="vy-news-card__img-link">
                        <div class="vy-news-card__img-wrap">
                            <?php if ($n_img): ?>
                            <img src="<?php echo esc_url($n_img); ?>" alt="<?php the_title_attribute(); ?>"
                                loading="lazy" class="vy-news-card__img">
                            <?php else: ?>
                            <div class="vy-news-card__img-placeholder">
                                <i class="fa-regular fa-newspaper"></i>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($n_cats)): ?>
                            <span class="vy-news-card__cat">
                                <?php echo esc_html($n_cats[0]->name); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="vy-news-card__body">
                        <div class="vy-news-card__date">
                            <i class="fa-regular fa-calendar-days"></i>
                            <?php echo esc_html($n_date); ?>
                        </div>
                        <h3 class="vy-news-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="vy-news-card__excerpt">
                            <?php echo esc_html(wp_trim_words($n_excerpt, 18, '…')); ?>
                        </p>
                    </div>

                </article>
                <?php endwhile;
                    wp_reset_postdata(); ?>
            </div><!-- /.vy-home-news__grid -->

            <div class="apk-view-more">
                <a href="<?php echo esc_url($news_view_more); ?>" class="btn">
                    <?php esc_html_e('Xem tất cả bài viết', 'voya'); ?>
                </a>
            </div>

        </div>
    </section>
    <?php endif; ?>


</main>

<?php get_footer(); ?>