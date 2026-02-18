<?php
/**
 * Plugin Name: Workwear AJAX Product Search
 * Description: Realtime AJAX zoekbalk voor WooCommerce producten, geschikt voor Elementor via shortcode.
 * Version: 1.0.0
 * Author: Codex Assistant
 * Requires Plugins: woocommerce
 * Text Domain: workwear-ajax-search
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Workwear_Ajax_Search {
    private const VERSION = '1.0.0';
    private const NONCE_ACTION = 'workwear_ajax_search_nonce';

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_shortcode('workwear_ajax_search', [$this, 'render_shortcode']);

        add_action('wp_ajax_workwear_ajax_product_search', [$this, 'handle_product_search']);
        add_action('wp_ajax_nopriv_workwear_ajax_product_search', [$this, 'handle_product_search']);
    }

    public function register_assets(): void {
        wp_register_style(
            'workwear-ajax-search',
            plugin_dir_url(__FILE__) . 'assets/css/search.css',
            [],
            self::VERSION
        );

        wp_register_script(
            'workwear-ajax-search',
            plugin_dir_url(__FILE__) . 'assets/js/search.js',
            [],
            self::VERSION,
            true
        );

        wp_localize_script('workwear-ajax-search', 'workwearAjaxSearch', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
            'minChars' => 2,
            'strings' => [
                'typeToSearch' => __('Typ minimaal 2 tekens om te zoeken…', 'workwear-ajax-search'),
                'loading' => __('Zoeken…', 'workwear-ajax-search'),
                'noResults' => __('Geen producten gevonden.', 'workwear-ajax-search'),
                'viewAll' => __('Bekijk alle resultaten', 'workwear-ajax-search'),
            ],
        ]);
    }

    public function render_shortcode(array $atts = []): string {
        if (!class_exists('WooCommerce')) {
            return '<p>' . esc_html__('WooCommerce moet actief zijn voor de zoekbalk.', 'workwear-ajax-search') . '</p>';
        }

        $atts = shortcode_atts([
            'placeholder' => __('Zoek bedrijfskleding, merk of artikel…', 'workwear-ajax-search'),
            'max_results' => 8,
            'show_price' => 'yes',
            'show_image' => 'yes',
        ], $atts, 'workwear_ajax_search');

        wp_enqueue_style('workwear-ajax-search');
        wp_enqueue_script('workwear-ajax-search');

        $input_id = 'wwas-search-input-' . wp_rand(1000, 9999);

        ob_start();
        ?>
        <div
            class="wwas-search"
            data-max-results="<?php echo esc_attr((int) $atts['max_results']); ?>"
            data-show-price="<?php echo esc_attr($atts['show_price'] === 'yes' ? 'yes' : 'no'); ?>"
            data-show-image="<?php echo esc_attr($atts['show_image'] === 'yes' ? 'yes' : 'no'); ?>"
        >
            <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>">
                <?php esc_html_e('Zoeken naar producten', 'workwear-ajax-search'); ?>
            </label>
            <input
                class="wwas-search__input"
                id="<?php echo esc_attr($input_id); ?>"
                type="search"
                placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                autocomplete="off"
                spellcheck="false"
            >
            <div class="wwas-search__results" hidden>
                <div class="wwas-search__status"></div>
                <ul class="wwas-search__list"></ul>
                <a class="wwas-search__all" href="#" hidden></a>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    public function handle_product_search(): void {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        if (!class_exists('WooCommerce')) {
            wp_send_json_error(['message' => __('WooCommerce ontbreekt.', 'workwear-ajax-search')], 400);
        }

        $term = isset($_POST['term']) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';
        $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 8;
        $show_price = isset($_POST['showPrice']) ? sanitize_text_field(wp_unslash($_POST['showPrice'])) : 'yes';
        $show_image = isset($_POST['showImage']) ? sanitize_text_field(wp_unslash($_POST['showImage'])) : 'yes';

        if (mb_strlen($term) < 2) {
            wp_send_json_success([
                'results' => [],
                'count' => 0,
                'searchUrl' => wc_get_page_permalink('shop'),
            ]);
        }

        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => max(1, min(20, $limit)),
            's' => $term,
            'meta_query' => [
                [
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                ],
            ],
            'tax_query' => [
                [
                    'taxonomy' => 'product_visibility',
                    'field' => 'name',
                    'terms' => ['exclude-from-search'],
                    'operator' => 'NOT IN',
                ],
            ],
        ]);

        $results = [];

        foreach ($query->posts as $post) {
            $product = wc_get_product($post->ID);

            if (!$product instanceof WC_Product) {
                continue;
            }

            $results[] = [
                'id' => $product->get_id(),
                'title' => $product->get_name(),
                'url' => get_permalink($product->get_id()),
                'priceHtml' => $show_price === 'yes' ? wp_kses_post($product->get_price_html()) : '',
                'image' => $show_image === 'yes' ? wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail') : '',
                'sku' => $product->get_sku(),
            ];
        }

        wp_reset_postdata();

        $search_url = add_query_arg('s', rawurlencode($term), wc_get_page_permalink('shop'));

        wp_send_json_success([
            'results' => $results,
            'count' => count($results),
            'searchUrl' => esc_url_raw($search_url),
        ]);
    }
}

new Workwear_Ajax_Search();
