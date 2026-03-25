<?php
/**
 * VOYA — footer.php
 * Clone layout: happymod.cx
 * 4 cols: Logo | Bio + Social | Danh mục | Về chúng tôi
 * Light gray background, green accent, dark bottom bar
 */

$vy_logo_id = get_option('vy_footer_logo_id', '');
$vy_bio = get_option('vy_footer_bio', '');
$vy_contact = get_option('vy_footer_contact', '');
$vy_copyright = get_option('vy_footer_copyright', '');

// Col 3: Danh mục — list of product categories
$vy_cat_title = get_option('vy_footer_cat_title', 'Danh mục');
$cat_links_raw = get_option('vy_footer_cat_links', '');  // JSON array [{label, url}]

// Col 4: Về chúng tôi — static links
$vy_about_title = get_option('vy_footer_about_title', 'Về chúng tôi');
$about_links_raw = get_option('vy_footer_about_links', '');  // JSON array [{label, url}]

// Social
$socials = [
    'facebook' => ['icon' => 'fa-facebook-f', 'class' => 'fb'],
    'twitter' => ['icon' => 'fa-x-twitter', 'class' => 'tw'],
    'pinterest' => ['icon' => 'fa-pinterest-p', 'class' => 'pt'],
    'linkedin' => ['icon' => 'fa-linkedin-in', 'class' => 'li'],
    'telegram' => ['icon' => 'fa-telegram', 'class' => 'tg'],
    'youtube' => ['icon' => 'fa-youtube', 'class' => 'yt'],
    'instagram' => ['icon' => 'fa-instagram', 'class' => 'ig'],
];

// Parse JSON link lists (fallback to defaults if empty)
$cat_links = [];
if ($cat_links_raw) {
    $decoded = json_decode($cat_links_raw, true);
    if (is_array($decoded))
        $cat_links = $decoded;
}
if (empty($cat_links)) {
    // Fallback: top-level product categories
    $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'number' => 6]);
    if (!is_wp_error($terms) && $terms) {
        foreach ($terms as $t) {
            $cat_links[] = ['label' => $t->name, 'url' => get_term_link($t)];
        }
    }
}

$about_links = [];
if ($about_links_raw) {
    $decoded = json_decode($about_links_raw, true);
    if (is_array($decoded))
        $about_links = $decoded;
}
if (empty($about_links)) {
    $about_links = [
        ['label' => 'Giới thiệu', 'url' => home_url('/gioi-thieu/')],
        ['label' => 'Liên hệ', 'url' => home_url('/lien-he/')],
        ['label' => 'Chính sách bảo mật', 'url' => home_url('/chinh-sach/')],
        ['label' => 'DMCA', 'url' => home_url('/dmca/')],
    ];
}
?>

<footer class="vy-footer" role="contentinfo">

    <!-- ============================================================
         FOOTER MAIN — 4 columns
    ============================================================ -->
    <div class="vy-footer__main">
        <div class="vy-footer__container">
            <div class="vy-footer__grid">

                <!-- COL 1: Logo / Icon -->
                <div class="vy-footer__col vy-footer__col--logo">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="vy-footer__logo-link">
                        <?php if ($vy_logo_id):
                            $logo_url = wp_get_attachment_image_url($vy_logo_id, 'full');
                            if ($logo_url): ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>"
                            class="vy-footer__logo-img" loading="lazy">
                        <?php endif;
                        elseif (has_custom_logo()):
                            $logo_data = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full');
                            if ($logo_data): ?>
                        <img src="<?php echo esc_url($logo_data[0]); ?>" alt="<?php bloginfo('name'); ?>"
                            class="vy-footer__logo-img" loading="lazy">
                        <?php endif;
                        else: ?>
                        <span class="vy-footer__logo-text"><?php bloginfo('name'); ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- COL 2: Bio + Social -->
                <div class="vy-footer__col vy-footer__col--bio">
                    <?php if ($vy_bio): ?>
                    <div class="vy-footer__bio">
                        <?php echo wp_kses_post($vy_bio); ?>
                    </div>
                    <?php else: ?>
                    <div class="vy-footer__bio">
                        <p>
                            <a
                                href="<?php echo esc_url(home_url('/')); ?>"><strong><?php bloginfo('name'); ?></strong></a>
                            <?php esc_html_e(' là nền tảng tải game mod và app miễn phí, cài đặt nhanh chóng và an toàn, giúp bạn khám phá kho apk phong phú mọi lúc.', 'voya'); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($vy_contact): ?>
                    <p class="vy-footer__contact"><?php echo wp_kses_post($vy_contact); ?></p>
                    <?php endif; ?>

                    <!-- Social Icons -->
                    <?php
                    $has_social = false;
                    foreach ($socials as $key => $s) {
                        if (get_option("vy_footer_social_$key")) {
                            $has_social = true;
                            break;
                        }
                    }
                    if ($has_social): ?>
                    <div class="vy-footer__socials">
                        <?php foreach ($socials as $key => $s):
                                    $url = get_option("vy_footer_social_$key");
                                    if (!$url)
                                        continue; ?>
                        <a href="<?php echo esc_url($url); ?>"
                            class="vy-footer__social vy-footer__social--<?php echo esc_attr($s['class']); ?>"
                            target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr(ucfirst($key)); ?>">
                            <i class="fa-brands <?php echo esc_attr($s['icon']); ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- COL 3: Danh mục -->
                <div class="vy-footer__col vy-footer__col--cats">
                    <h3 class="vy-footer__col-title">
                        <?php echo esc_html($vy_cat_title); ?>
                    </h3>
                    <?php if (!empty($cat_links)): ?>
                    <ul class="vy-footer__links">
                        <?php foreach ($cat_links as $link): ?>
                        <li>
                            <a href="<?php echo esc_url($link['url']); ?>">
                                <?php echo esc_html($link['label']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

                <!-- COL 4: Về chúng tôi -->
                <div class="vy-footer__col vy-footer__col--about">
                    <h3 class="vy-footer__col-title">
                        <?php echo esc_html($vy_about_title); ?>
                    </h3>
                    <?php if (!empty($about_links)): ?>
                    <ul class="vy-footer__links">
                        <?php foreach ($about_links as $link): ?>
                        <li>
                            <a href="<?php echo esc_url($link['url']); ?>">
                                <?php echo esc_html($link['label']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>

            </div><!-- /.vy-footer__grid -->
        </div><!-- /.vy-footer__container -->
    </div><!-- /.vy-footer__main -->


    <!-- ============================================================
         FOOTER BOTTOM — Copyright bar
    ============================================================ -->
    <div class="vy-footer__bottom">
        <div class="vy-footer__container">
            <div class="vy-footer__copyright">
                <?php
                $copy = $vy_copyright;
                if (!$copy) {
                    $copy = 'Copyright ' . gmdate('Y') . ' &copy; <strong>' . get_bloginfo('name') . ' &ndash; All Rights Reserved</strong>';
                }
                echo wp_kses_post($copy);
                ?>
            </div>
        </div>
    </div><!-- /.vy-footer__bottom -->

</footer><!-- .vy-footer -->


<!-- ============================================================
     BACK TO TOP BUTTON
============================================================ -->
<a id="vy-back-to-top" href="#" aria-label="<?php esc_attr_e('Lên đầu trang', 'voya'); ?>">
    <i class="fa-solid fa-angle-up"></i>
</a>

<?php wp_footer(); ?>
</body>

</html>