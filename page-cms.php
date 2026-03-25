<?php
/**
 * Template Name: CMS Page
 * VOYA — page-cms.php
 * Dùng cho: Giới thiệu, Chính sách, Liên hệ, DMCA,...
 * Layout: breadcrumb bar + content card (no sidebar)
 */

get_header();

while (have_posts()):
    the_post();

    $page_title = get_the_title();
    $parent = wp_get_post_parent_id(get_the_ID());
    ?>

<main class="vy-cms-page">

    <!-- ════════════════════════════
         BREADCRUMB BAR
    ════════════════════════════ -->
    <div class="vy-cms-breadcrumb-bar">
        <div class="vy-cms-container">
            <nav class="vy-cms-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <i class="fa-solid fa-house"></i>
                    <?php esc_html_e('Trang chủ', 'voya'); ?>
                </a>
                <span class="vy-cms-breadcrumb__sep">/</span>
                <?php if ($parent): ?>
                <a href="<?php echo esc_url(get_permalink($parent)); ?>">
                    <?php echo esc_html(get_the_title($parent)); ?>
                </a>
                <span class="vy-cms-breadcrumb__sep">/</span>
                <?php endif; ?>
                <span>
                    <?php echo esc_html($page_title); ?>
                </span>
            </nav>
        </div>
    </div>

    <!-- ════════════════════════════
         CONTENT
    ════════════════════════════ -->
    <div class="vy-cms-body">
        <div class="vy-cms-container">
            <article class="vy-cms-article">

                <!-- Page title -->
                <h1 class="vy-cms-title">
                    <?php the_title(); ?>
                </h1>

                <!-- Page content -->
                <div class="vy-cms-content entry-content">
                    <?php the_content(); ?>
                </div>

                <!-- Pagination nếu page có nhiều trang -->
                <?php wp_link_pages([
                        'before' => '<div class="vy-cms-pages"><span>' . __('Trang:', 'voya') . '</span>',
                        'after' => '</div>',
                        'link_before' => '<span>',
                        'link_after' => '</span>',
                    ]); ?>

            </article>
        </div>
    </div>

</main>

<?php
endwhile;
get_footer();
?>