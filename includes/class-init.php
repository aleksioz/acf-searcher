<?php

class Init {

    public static $instance = null;

    public static function instance() {
        // Initialize the plugin
        if (null === self::$instance) {
            self::$instance = new self();
        } 
        return self::$instance;
    }

    private function __construct() {

        // Make Pas block available
        require_once ACF_SEARCHER_PATH . 'includes/class-pas-create.php';
        PasCreate::instance();

        add_shortcode('acf_searcher', [$this, 'render_search_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_nopriv_acf_search', [$this, 'handle_ajax_request']);
        add_action('wp_ajax_acf_search', [$this, 'handle_ajax_request']);
    }

    public static function render_search_form($atts) {

        $atts = shortcode_atts([
            'category' => ''
        ], $atts, 'acf_searcher');

        if(empty($atts['category'])) {
            echo '<p>Kategorija je obavezna!</p>';
            return;
        }

        ?>
        <div class="acf-searcher-instructions">
            <h4>Dobrodošli u pretragu oglasa! Molimo vas da koristite sledeće opcije za pretragu:</h4>
            <ul>
                <li><strong>Izborni elementi:</strong> Koristite padajuće menije i dugmiće za odabir rase, pola, veličine...</li>
                <li><strong>Datum:</strong> Unesite broj meseci za pretragu oglasa objavljenih u nazad toliko meseci.</li>
                <li><strong>Tekstualna pretraga:</strong> Unesite ključne reči koje se tačno pojavljuju u naslovu ili tekstu oglasa. Ovo može biti korisno samo ako znate tačan izraz koji tražite. Inače ostavite ovo polje prazno</li>
                <li><strong>Napomena:</strong> <i>Svako selektovano polje dodatno sužava pretragu, dok ostavljanje polja praznim proširuje pretragu.</i></li>
            </ul>
        </div>
        <form id="acf-search-form">
            <input type="text" name="search" placeholder="Traži u tekstu i naslovu">
            <?php
            $fields = acf_get_fields('group_677f86d30fbf0');
            if ($fields) {
                foreach ($fields as $field) {

                    if($field['type'] === 'select' || $field['type'] === 'radio') {
                            echo '<select name="' . esc_attr($field['name']) . '" class="operator">';
                            echo '<option value="" selected style="color: gray;">' . esc_html($field['label']) . '</option>';
                            foreach ($field['choices'] as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                            }
                            echo '</select>';
                    }
                    if ($field['type'] === 'date_picker') {
                        echo '<input type="number" name="' . esc_attr($field['name']) . '" placeholder="Period pretrage (meseci u nazad)" class="acf-searcher-date">';
                    }
                }
                echo '<input type="hidden" name="category" value="' . esc_attr($atts['category']) . '">';
            }
            ?>
            <button type="submit">Potraži..</button>
        </form>
        <div id="acf-search-results"></div>
        <?php
        return;
    }

    public function enqueue_scripts() {
        wp_enqueue_script('select2-js', ACF_SEARCHER_URL . '/assets/js/select2.min.js', ['jquery'], null, true);
        
        wp_enqueue_style('select2-css', ACF_SEARCHER_URL . '/assets/css/select2.min.css', [], null);
        wp_enqueue_style('style', ACF_SEARCHER_URL . '/assets/css/style.css', [], null);

        wp_enqueue_script('acf-searcher-script', ACF_SEARCHER_URL . 'assets/js/script.js', ['jquery'], '1.0.0', true);
        wp_localize_script('acf-searcher-script', 'acf_searcher', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
    }

    public function handle_ajax_request() {

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_POST['category']),
                ],
            ],
            's' => sanitize_text_field($_POST['search']),
            // custom field query args here
            'meta_query' => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    $_POST['rasa'] ? [
                        'key' => 'rasa',
                        'value' => sanitize_text_field($_POST['rasa']),
                        'compare' => '='
                    ] : [],
                    $_POST['rasa'] ? [
                        'key' => 'rasa',
                        'compare' => 'NOT EXISTS'
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
                        'compare' => 'NOT EXISTS'
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
                        'compare' => 'NOT EXISTS'
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
                        'compare' => 'NOT EXISTS'
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
                        'compare' => 'NOT EXISTS'
                    ] : []
                ],
                [
                    'relation' => 'OR',
                    $_POST['datum'] ? [
                        'key' => 'datum',
                        'value' => date('Ymd', strtotime('-' . sanitize_text_field($_POST['datum']) . ' months')),
                        'compare' => '>'
                    ] : [],
                    $_POST['datum'] ? [
                        'key' => 'datum',
                        'compare' => 'NOT EXISTS'
                    ] : []
                ]
            ]
        ];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            ob_start();
            echo '<div class="acf-searcher-results-grid">';
            while ($query->have_posts()) {
                $query->the_post();
                ?>
                <div class="acf-searcher-item">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        <?php endif; ?>
                        <h2 class="post-title"><?php the_title(); ?></h2>
                    </a>
                        <div class="post-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                </div>
                <?php
            }
            echo '</div>';
            echo ob_get_clean();
        } else {
            echo '<h3>Nema rezultata!</h3>';
        }
        wp_die();
    }
}