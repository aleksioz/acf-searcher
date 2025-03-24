<?php

/**
 * This class makes response to the AJAX request for the search form.
 * It prepares the query based on the search parameters and returns the results.
 *
 * @package ACF_Searcher
 */

namespace ACF_Searcher;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class AjaxRequest {

    /**
     * Prepare the query based on the search parameters
     *
     * @return WP_Query
     */
    private static function prepare_the_query() {

        $paged = isset($_POST['page']) ? sanitize_text_field($_POST['page']) : 1;

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 60,
            'paged' => $paged,
            'tax_query' => [
                [
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_POST['category']),
                ],
            ],

            's' => sanitize_text_field($_POST['search']),
            
            // Custom fields args here, with following logic:
            // 1. Selecting (from the front) more than one field will narrow the search
            // 2. If the field is not available in the post, it will match any selected value
            
            'meta_query' => [
                'relation' => 'AND', // narrow the search
                [
                    'relation' => 'OR',
                    $_POST['rasa'] ? [
                        'key' => 'rasa',
                        'value' => sanitize_text_field($_POST['rasa']),
                        'compare' => '='
                    ] : [],
                    $_POST['rasa'] ? [
                        'key' => 'rasa',
                        'value' => '',
                        'compare' => '='
                    ] : []
                ],
                [
                    'relation' => 'OR',
                    $_POST['pol'] ? [
                        'key' => 'pol',
                        'value' => sanitize_text_field($_POST['pol']),
                        'compare' => '='
                    ] : [],
                    $_POST['pol'] ? [
                        'key' => 'pol',
                        'value' => '',
                        'compare' => '='
                    ] : []
                ],
                [
                    'relation' => 'OR',
                    $_POST['velicina'] ? [
                        'key' => 'velicina',
                        'value' => sanitize_text_field($_POST['velicina']),
                        'compare' => '='
                    ] : [],
                    $_POST['velicina'] ? [
                        'key' => 'velicina',
                        'value' => '',
                        'compare' => '='
                    ] : []
                ],
                [
                    'relation' => 'OR',
                    $_POST['boja'] ? [
                        'key' => 'boja',
                        'value' => sanitize_text_field($_POST['boja']),
                        'compare' => '='
                    ] : [],
                    $_POST['boja'] ? [
                        'key' => 'boja',
                        'value' => '',
                        'compare' => '='
                    ] : []
                ],
                [
                    'relation' => 'OR',
                    $_POST['cip'] ? [
                        'key' => 'cip',
                        'value' => sanitize_text_field($_POST['cip']),
                        'compare' => '='
                    ] : [],
                    $_POST['cip'] ? [
                        'key' => 'cip',
                        'value' => '',
                        'compare' => '='
                    ] : []
                ],
                [
                    $_POST['datum'] ? [
                        'key' => 'datum',
                        'value' => date('Ymd', strtotime('-' . sanitize_text_field($_POST['datum']) . ' months')),
                        'compare' => '>'
                    ] : []
                ]
            ]
        ];

        return new \WP_Query($args);
    }



    /**
     * Handle the AJAX request
     *
     * @return void
     */
    public static function response() {

        $query = self::prepare_the_query();

        if ($query->have_posts()): ?>

            <div class="acf-searcher-results-grid">

            <?php while ($query->have_posts()): ?>

                <?php

                    $query->the_post();
                    $permalink = get_permalink();
                    $thumbnail = has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'medium' ) : '';
                    $title = get_the_title();
                    $excerpt = get_the_excerpt();

                ?>

                <div class="acf-searcher-item">
                    <a href="<?= $permalink ?>">
                        <div class="post-thumbnail"> <?= $thumbnail ?> </div>
                        <h2 class="post-title"> <?= $title ?> </h2>
                    </a>
                    <div class="post-excerpt"> <?= $excerpt ?> </div>
                </div>

            <?php endwhile; ?>
            
            </div>

        <?php else: ?>

            <h3>Nema rezultata!</h3>

        <?php endif;
        
        wp_die();
    }
        
}