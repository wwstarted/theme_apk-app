<?php
/**
 * Template Name: Archive Product (Mua Proxy)
 */
get_header();

// ── SSR Params ────────────────────────────────────────────────────────────
$paged = max(1, (int) get_query_var('paged'));
$posts_per_page = 12;

// Tag filter từ ?ptag=...
$selected_tag = isset($_GET['ptag'])
    ? sanitize_text_field(wp_unslash($_GET['ptag']))
    : 'all';
if (empty($selected_tag))
    $selected_tag = 'all';

// Base URL luôn là /mua-proxy/
$base_url = home_url('/mua-proxy/');

$search_term = isset($_GET['proxy_s']) ? sanitize_text_field($_GET['proxy_s']) : '';
$sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'bestseller';

// ── Map sort → WP_Query orderby ───────────────────────────────────────────
$orderby = 'date';
$order = 'DESC';
switch ($sort_by) {
    case 'name-az':
        $orderby = 'title';
        $order = 'ASC';
        break;
    case 'name-za':
        $orderby = 'title';
        $order = 'DESC';
        break;
    default:
        $orderby = 'date';
        $order = 'DESC';
        break;
}

// ── WP_Query ──────────────────────────────────────────────────────────────
$args = [
    'post_type' => 'product',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish',
    'orderby' => $orderby,
    'order' => $order,
    'tax_query' => [
        [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => 'mua-proxy',
        ],
    ],
];

if ($selected_tag !== 'all' && !empty($selected_tag)) {
    $args['tax_query']['relation'] = 'AND';
    $args['tax_query'][] = [
        'taxonomy' => 'product_tag',
        'field' => 'slug',
        'terms' => $selected_tag,
    ];
}

if (!empty($search_term)) {
    $args['s'] = $search_term;
}

$products_query = new WP_Query($args);

// ── Lấy tất cả tags có sản phẩm trong mua-proxy ──────────────────────────
$all_product_ids_in_cat = get_posts([
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'no_found_rows' => true,
    'tax_query' => [
        [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => 'mua-proxy',
        ]
    ],
]);

$available_tags = [];
if (!empty($all_product_ids_in_cat)) {
    $raw_tags = wp_get_object_terms($all_product_ids_in_cat, 'product_tag', [
        'orderby' => 'name',
        'order' => 'ASC',
    ]);
    $unique_tags = [];
    foreach ($raw_tags as $tag) {
        $unique_tags[$tag->term_id] = $tag;
    }
    $available_tags = array_values($unique_tags);
}

// ── Tag icons ─────────────────────────────────────────────────────────────
$tag_icons = [
    'proxy-dan-cu-ips' => 'fa-house',
    'proxy-dan-cu-xoay' => 'fa-rotate',
];

// ── Helper: build filter URL ──────────────────────────────────────────────
function vp_archive_filter_url(array $params = []): string
{
    global $base_url;
    $tag = $params['ptag'] ?? null;
    $sort = $params['sort'] ?? null;
    $search = $params['proxy_s'] ?? null;

    $query_args = array_filter([
        'ptag' => ($tag && $tag !== 'all') ? $tag : null,
        'sort' => ($sort && $sort !== 'bestseller') ? $sort : null,
        'proxy_s' => !empty($search) ? $search : null,
    ], fn($v) => $v !== null && $v !== '');

    return $query_args ? add_query_arg($query_args, $base_url) : $base_url;
}

// ── USD rate ──────────────────────────────────────────────────────────────
$usd_rate = (float) get_option('vieproxy_usd_rate', 25000);
if ($usd_rate <= 0)
    $usd_rate = 25000;

// ── Hero description — dùng desc của category mua-proxy ──────────────────
$mua_proxy_term = get_term_by('slug', 'mua-proxy', 'product_cat');
$hero_desc = '';

if ($mua_proxy_term && !is_wp_error($mua_proxy_term) && !empty($mua_proxy_term->description)) {
    $hero_desc = wp_strip_all_tags($mua_proxy_term->description);
}
if (empty($hero_desc)) {
    $hero_desc = 'Khám phá danh mục Proxy với đa dạng giải pháp IP chất lượng cao, bảo mật và ổn định, đáp ứng mọi nhu cầu từ ẩn danh, thu thập dữ liệu một cách an toàn và hiệu quả.';
}

// ── SEO Content section — dùng term meta _vp_cat_seo_content ─────────────
$proxy_archive_content = ['has_toc' => false, 'toc' => '', 'content' => ''];

if ($mua_proxy_term && !is_wp_error($mua_proxy_term)) {
    $raw_seo = get_term_meta($mua_proxy_term->term_id, '_vp_cat_seo_content', true);
    if (!empty(trim(wp_strip_all_tags((string) $raw_seo)))) {
        $rendered_seo = apply_filters('the_content', wp_kses_post($raw_seo));
        if (function_exists('vp_build_toc_data_from_content')) {
            $proxy_archive_content = vp_build_toc_data_from_content($rendered_seo, 'mua-proxy');
        } else {
            $proxy_archive_content['content'] = $rendered_seo;
        }
    }
}

// ── Collect + price products ──────────────────────────────────────────────
$page_products = [];

if ($products_query->have_posts()):
    while ($products_query->have_posts()):
        $products_query->the_post();

        $product_id = get_the_ID();
        $product_name = get_the_title();
        $product_url = get_permalink();

        $thumb_id = get_post_thumbnail_id();
        $thumb_url = $thumb_id
            ? wp_get_attachment_image_url($thumb_id, 'medium')
            : get_template_directory_uri() . '/images/placeholder.png';

        $vp_meta = function_exists('vieproxy_get_product_meta') ? vieproxy_get_product_meta($product_id) : [];
        $pricing_cfg = $vp_meta['pricing_config'] ?? [];
        if (empty($pricing_cfg))
            continue;

        $ptype = $pricing_cfg['pricing_type'] ?? 'ip_time';
        $default_plan_idx = (int) ($pricing_cfg['default_plan_index'] ?? 0);
        $default_qty = (int) ($pricing_cfg['default_qty'] ?? 1);
        $unit_label = $pricing_cfg['unit_label'] ?? 'IPs';
        $price_usd = 0;
        $price_vnd = 0;
        $dtt_qty = 0;

        if ($ptype === 'time_only') {
            $time_plans = $pricing_cfg['time_plans'] ?? [];
            if (empty($time_plans))
                continue;
            $plan = $time_plans[$default_plan_idx] ?? $time_plans[0];
            $price_vnd = (float) ($plan['price_vnd'] ?? 0);
            if (!$price_vnd)
                $price_vnd = round((float) ($plan['price'] ?? 0) * $usd_rate);
            $price_usd = round($price_vnd / $usd_rate, 2);
            $unit_label = $plan['label'] ?? $unit_label;

        } elseif ($ptype === 'dollar_time') {
            $dtt_tiers = $pricing_cfg['dollar_time_tiers'] ?? [];
            $dt_plans = $pricing_cfg['duration_plans_dt'] ?? [];
            if (empty($dtt_tiers) || empty($dt_plans))
                continue;
            $dt_plan = $dt_plans[$default_plan_idx] ?? $dt_plans[0];
            $months = (float) ($dt_plan['months'] ?? 1);
            $dtt_tier = $dtt_tiers[0];
            $dtt_qty = (float) ($dtt_tier['qty'] ?? $dtt_tier['label'] ?? 10);
            $price_unit = (float) ($dtt_tier['price_per_unit'] ?? $dtt_tier['amount'] ?? 0);
            $price_usd = ($months == 0) ? $dtt_qty * $price_unit : $dtt_qty * $price_unit * $months;
            $price_vnd = round($price_usd * $usd_rate);
            $unit_label = $pricing_cfg['dt_unit_label'] ?? '$';

        } else {
            $plans = $pricing_cfg['duration_plans'] ?? [];
            $tiers = $pricing_cfg['qty_tiers'] ?? [];
            if (empty($plans) || empty($tiers))
                continue;
            $plan = $plans[$default_plan_idx] ?? $plans[0];
            $months = (float) ($plan['months'] ?? 1);
            $tier = $tiers[0];
            foreach ($tiers as $t) {
                if ((int) ($t['value'] ?? 0) === $default_qty) {
                    $tier = $t;
                    break;
                }
            }
            $price_per_unit = (float) ($tier['price_per_unit'] ?? 0);
            $price_usd = ($months == 0) ? $default_qty * $price_per_unit : $default_qty * $price_per_unit * $months;
            $price_vnd = round($price_usd * $usd_rate);
        }

        $features = [];
        if (!empty($vp_meta['features'])) {
            $features = $vp_meta['features'];
        }
        if (empty($features)) {
            $highlights = get_post_meta($product_id, '_vieproxy_product_highlights', true);
            if (!empty($highlights) && is_array($highlights)) {
                $features = array_values(array_filter(array_map('sanitize_text_field', $highlights)));
            }
        }
        if (empty($features)) {
            $_product = wc_get_product($product_id);
            if ($_product) {
                $count = 0;
                foreach ($_product->get_attributes() as $attr) {
                    if ($count >= 3)
                        break;
                    $attr_name = wc_attribute_label($attr->get_name());
                    $terms = $attr->get_terms();
                    $attr_value = $terms ? implode(', ', wp_list_pluck($terms, 'name')) : '';
                    if (empty($attr_value))
                        continue;
                    $features[] = $attr_name . ': ' . $attr_value;
                    $count++;
                }
            }
        }
        if (empty($features)) {
            $features = ['Kết nối ổn định, tốc độ cao', 'Hỗ trợ HTTP, SOCKS5', 'Hỗ trợ 24/7'];
        }

        if ($ptype === 'time_only') {
            $price_label = $unit_label;
        } elseif ($ptype === 'dollar_time') {
            $price_label = '$' . (int) $dtt_qty . ' ' . $unit_label;
        } else {
            $price_label = $default_qty . ' ' . $unit_label;
        }

        $page_products[] = compact(
            'product_id',
            'product_name',
            'product_url',
            'thumb_url',
            'price_usd',
            'price_vnd',
            'default_qty',
            'unit_label',
            'price_label',
            'features'
        );

    endwhile;
    wp_reset_postdata();

    if ($sort_by === 'price-low') {
        usort($page_products, fn($a, $b) => $a['price_vnd'] <=> $b['price_vnd']);
    } elseif ($sort_by === 'price-high') {
        usort($page_products, fn($a, $b) => $b['price_vnd'] <=> $a['price_vnd']);
    }
endif;

$total_in_db = $products_query->found_posts;
$actual_count = count($page_products);
$start_display = ($paged - 1) * $posts_per_page + 1;
$end_display = $start_display + $actual_count - 1;
?>

<section class="proxy-hero">
    <div class="wrapper">
        <nav class="proxy-hero__breadcrumb" aria-label="breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="breadcrumb-link">Trang chủ</a>
            <span class="breadcrumb-sep"><i class="fa-solid fa-chevron-right"></i></span>
            <span class="breadcrumb-current">Mua Proxy</span>
        </nav>
        <h1 class="proxy-hero__title">MUA PROXY</h1>
        <p class="proxy-hero__desc">
            <?php echo esc_html($hero_desc); ?>
        </p>
    </div>
</section>

<div class="main-content bg-[#f9fdff]">
    <div class="product-container-v2 wrapper">

        <div class="product-header-v2">
            <h2 class="product-title-v2">Danh sách sản phẩm</h2>
            <button class="filter-btn-mobile" id="openFilterModal">
                <i class="fa-solid fa-sliders"></i> Bộ lọc
            </button>
        </div>

        <div class="product-layout-v2">

            <aside class="product-sidebar-v2">
                <div class="sidebar-header-v2">
                    <h2 class="sidebar-title-v2">Chọn danh mục</h2>
                    <a href="<?php echo esc_url($base_url); ?>" class="clear-filter-v2">Xóa lọc</a>
                </div>
                <div class="filter-group-v2">
                    <div class="filter-options-v2">

                        <a href="<?php echo esc_url(vp_archive_filter_url(['sort' => $sort_by !== 'bestseller' ? $sort_by : null])); ?>"
                            class="filter-option-v2 <?php echo ($selected_tag === 'all') ? 'active' : ''; ?>">
                            <i class="fa-solid fa-border-all"></i>
                            <span>Tất cả</span>
                        </a>

                        <?php foreach ($available_tags as $tag_term):
                            $t_slug = $tag_term->slug;
                            $icon = $tag_icons[$t_slug] ?? 'fa-circle';
                            $is_active = ($selected_tag === $t_slug);
                            $tag_url = vp_archive_filter_url([
                                'ptag' => $t_slug,
                                'sort' => $sort_by !== 'bestseller' ? $sort_by : null,
                                'proxy_s' => !empty($search_term) ? $search_term : null,
                            ]);
                            ?>
                        <a href="<?php echo esc_url($tag_url); ?>"
                            class="filter-option-v2 <?php echo $is_active ? 'active' : ''; ?>"
                            data-tag="<?php echo esc_attr($t_slug); ?>">
                            <i class="fa-solid <?php echo esc_attr($icon); ?>"></i>
                            <span>
                                <?php echo esc_html(ucfirst(strtolower($tag_term->name))); ?>
                            </span>
                        </a>
                        <?php endforeach; ?>

                    </div>
                </div>
            </aside>

            <section class="products-section-v2">

                <form method="get" action="<?php echo esc_url($base_url); ?>" class="product-controls"
                    id="proxyFilterForm">

                    <?php if ($selected_tag !== 'all'): ?>
                    <input type="hidden" name="ptag" value="<?php echo esc_attr($selected_tag); ?>" />
                    <?php endif; ?>

                    <div class="product-controls__left">
                        <div class="product-search-wrapper">
                            <i class="fa-solid fa-magnifying-glass search-icon"></i>
                            <input type="text" name="proxy_s" class="product-search-input"
                                placeholder="Tìm kiếm proxy..." value="<?php echo esc_attr($search_term); ?>"
                                id="proxySearchInput" />
                        </div>
                    </div>

                    <span class="product-count-label" id="productCountLabel">
                        <?php
                        if ($actual_count === 0) {
                            echo 'Không tìm thấy sản phẩm';
                        } else {
                            echo "Hiển thị {$start_display}–{$end_display} trên {$total_in_db} sản phẩm";
                        }
                        ?>
                    </span>

                    <div class="product-sort-wrapper">
                        <label for="sortSelect" class="sort-label">Sắp xếp theo:</label>
                        <select name="sort" id="sortSelect" class="product-sort-select" onchange="this.form.submit()">
                            <option value="bestseller" <?php selected($sort_by, 'bestseller'); ?>>Bán chạy nhất
                            </option>
                            <option value="latest" <?php selected($sort_by, 'latest'); ?>>Mới cập nhật</option>
                            <option value="price-low" <?php selected($sort_by, 'price-low'); ?>>Giá thấp đến cao
                            </option>
                            <option value="price-high" <?php selected($sort_by, 'price-high'); ?>>Giá cao đến thấp
                            </option>
                            <option value="name-az" <?php selected($sort_by, 'name-az'); ?>>Tên từ A → Z</option>
                            <option value="name-za" <?php selected($sort_by, 'name-za'); ?>>Tên từ Z → A</option>
                        </select>
                    </div>

                </form>

                <div class="products-grid-v2" id="productsGrid">
                    <?php if (!empty($page_products)): ?>
                    <?php foreach ($page_products as $p): ?>
                    <a href="<?php echo esc_url($p['product_url']); ?>" class="product-card-v2"
                        data-product-id="<?php echo esc_attr($p['product_id']); ?>">
                        <div class="product-logo-v2">
                            <img src="<?php echo esc_url($p['thumb_url']); ?>"
                                alt="<?php echo esc_attr($p['product_name']); ?>" />
                        </div>
                        <h3 class="product-name-v2">
                            <?php echo esc_html($p['product_name']); ?>
                        </h3>
                        <ul class="pricing-card__features">
                            <?php foreach (array_slice($p['features'], 0, 3) as $feat):
                                        $feat_text = is_array($feat) ? ($feat['text'] ?? $feat['title'] ?? '') : $feat;
                                        if (empty($feat_text))
                                            continue; ?>
                            <li class="pricing-card__feature">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>
                                    <?php echo esc_html($feat_text); ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="product-info-grid-v2">
                            <div class="product-price-col-v2">
                                <span class="price-amount-v2">
                                    <?php echo number_format($p['price_vnd'], 0, ',', '.'); ?>đ
                                </span>
                                <span class="price-sep-v2">/</span>
                                <span class="price-package-v2">
                                    <?php echo esc_html($p['price_label']); ?>
                                </span>
                            </div>
                            <div class="product-action-col-v2">
                                <span class="product-detail-btn">Chi tiết</span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="no-products-v2">
                        <p>Không tìm thấy sản phẩm nào.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($products_query->max_num_pages > 1): ?>
                <div class="pagination-v2">
                    <?php
                        if ($paged > 1):
                            $prev_args = ['paged' => $paged - 1];
                            if ($selected_tag !== 'all')
                                $prev_args['ptag'] = $selected_tag;
                            if (!empty($search_term))
                                $prev_args['proxy_s'] = $search_term;
                            if ($sort_by !== 'bestseller')
                                $prev_args['sort'] = $sort_by;
                            ?>
                    <a href="<?php echo esc_url(add_query_arg($prev_args, $base_url)); ?>" class="pagination-btn">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    <?php else: ?>
                    <button class="pagination-btn" disabled><i class="fa-solid fa-chevron-left"></i></button>
                    <?php endif;

                        $add_args = [];
                        if ($selected_tag !== 'all')
                            $add_args['ptag'] = $selected_tag;
                        if (!empty($search_term))
                            $add_args['proxy_s'] = $search_term;
                        if ($sort_by !== 'bestseller')
                            $add_args['sort'] = $sort_by;

                        $pagination_args = [
                            'base' => add_query_arg('paged', '%#%', $base_url),
                            'format' => '?paged=%#%',
                            'current' => max(1, $paged),
                            'total' => $products_query->max_num_pages,
                            'type' => 'array',
                            'end_size' => 1,
                            'mid_size' => 2,
                            'prev_next' => false,
                        ];
                        if (!empty($add_args))
                            $pagination_args['add_args'] = $add_args;

                        $links = paginate_links($pagination_args);
                        if ($links):
                            foreach ($links as $link):
                                $is_current = strpos($link, 'current') !== false;
                                $is_dots = strpos($link, 'dots') !== false;
                                if ($is_dots): ?>
                    <span class="pagination-ellipsis">...</span>
                    <?php else:
                                    $class = $is_current ? 'pagination-btn active' : 'pagination-btn';
                                    $custom = preg_replace('/class=["\']page-numbers[^"\']*["\']/', 'class="' . $class . '"', $link);
                                    echo $custom;
                                endif;
                            endforeach;
                        endif;

                        if ($paged < $products_query->max_num_pages):
                            $next_args = ['paged' => $paged + 1];
                            if ($selected_tag !== 'all')
                                $next_args['ptag'] = $selected_tag;
                            if (!empty($search_term))
                                $next_args['proxy_s'] = $search_term;
                            if ($sort_by !== 'bestseller')
                                $next_args['sort'] = $sort_by;
                            ?>
                    <a href="<?php echo esc_url(add_query_arg($next_args, $base_url)); ?>" class="pagination-btn">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    <?php else: ?>
                    <button class="pagination-btn" disabled><i class="fa-solid fa-chevron-right"></i></button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </section>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     SEO CONTENT SECTION
     Nội dung từ term meta _vp_cat_seo_content
     của category mua-proxy
══════════════════════════════════════════ -->
<?php if (!empty($proxy_archive_content['content'])): ?>
<section class="proxy-archive-content-section">
    <div class="wrapper">
        <div
            class="proxy-archive-content-layout<?php echo !empty($proxy_archive_content['has_toc']) ? ' has-toc' : ''; ?>">

            <?php if (!empty($proxy_archive_content['has_toc'])): ?>
            <aside class="proxy-archive-toc-wrap" aria-label="Table of contents">
                <div class="proxy-archive-toc-card" id="proxyArchiveTocCard">
                    <div class="proxy-archive-toc-head">
                        <h2 class="proxy-archive-toc-title">Mục lục nội dung</h2>
                        <button type="button" class="proxy-archive-toc-toggle" id="proxyArchiveTocToggle"
                            aria-expanded="false" aria-controls="proxyArchiveTocContent" aria-label="Hiện mục lục"
                            title="Hiện mục lục">
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="proxy-archive-toc-content" id="proxyArchiveTocContent">
                        <?php echo $proxy_archive_content['toc']; // phpcs:ignore ?>
                    </div>
                </div>
            </aside>
            <?php endif; ?>

            <div class="proxy-archive-content-body entry-content">
                <?php echo $proxy_archive_content['content']; // phpcs:ignore ?>
            </div>

        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mobile Filter Modal -->
<div class="filter-modal-v2" id="filterModal">
    <div class="filter-modal-content-v2">
        <div class="filter-modal-header-v2">
            <h2 class="filter-modal-title-v2">Bộ lọc</h2>
            <div class="filter-modal-close-v2" id="closeFilterModal">
                <i class="fa-solid fa-xmark"></i>
            </div>
        </div>
        <div class="filter-modal-body-v2">
            <div class="filter-group-v2">
                <h3 class="filter-group-title-v2">Chọn danh mục</h3>
                <div class="filter-options-v2">
                    <a href="<?php echo esc_url(vp_archive_filter_url(['sort' => $sort_by !== 'bestseller' ? $sort_by : null])); ?>"
                        class="filter-option-v2 <?php echo ($selected_tag === 'all') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-border-all"></i><span>Tất cả</span>
                    </a>
                    <?php foreach ($available_tags as $tag_term):
                        $t_slug = $tag_term->slug;
                        $icon = $tag_icons[$t_slug] ?? 'fa-circle';
                        $is_active = ($selected_tag === $t_slug);
                        $tag_url = vp_archive_filter_url([
                            'ptag' => $t_slug,
                            'sort' => $sort_by !== 'bestseller' ? $sort_by : null,
                            'proxy_s' => !empty($search_term) ? $search_term : null,
                        ]);
                        ?>
                    <a href="<?php echo esc_url($tag_url); ?>"
                        class="filter-option-v2 <?php echo $is_active ? 'active' : ''; ?>">
                        <i class="fa-solid <?php echo esc_attr($icon); ?>"></i>
                        <span>
                            <?php echo esc_html(ucfirst(strtolower($tag_term->name))); ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="filter-group-v2">
                <h3 class="filter-group-title-v2">Sắp xếp theo</h3>
                <div class="filter-sort-links">
                    <?php
                    $sort_options = [
                        'bestseller' => 'Bán chạy nhất',
                        'latest' => 'Mới cập nhật',
                        'price-low' => 'Giá thấp đến cao',
                        'price-high' => 'Giá cao đến thấp',
                        'name-az' => 'Tên từ A → Z',
                        'name-za' => 'Tên từ Z → A',
                    ];
                    foreach ($sort_options as $val => $label):
                        $sort_url = vp_archive_filter_url([
                            'sort' => $val !== 'bestseller' ? $val : null,
                            'ptag' => $selected_tag !== 'all' ? $selected_tag : null,
                            'proxy_s' => !empty($search_term) ? $search_term : null,
                        ]);
                        ?>
                    <a href="<?php echo esc_url($sort_url); ?>"
                        class="filter-sort-link <?php echo ($sort_by === $val) ? 'active' : ''; ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="filter-modal-footer-v2">
            <button class="btn-cancel-v2" id="cancelFilter">Đóng</button>
        </div>
    </div>
</div>

<?php get_footer(); ?>