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

        // Make classes available
        require_once ACF_SEARCHER_PATH . 'includes/class-pas-create.php';
        require_once ACF_SEARCHER_PATH . 'includes/class-search-form.php';
        require_once ACF_SEARCHER_PATH . 'includes/class-ajax-request.php';

        PasCreate::instance();

        add_shortcode('acf_searcher', [ SearchForm::class, 'render']);
        add_action('wp_ajax_nopriv_acf_search', [AjaxRequest::class, 'handle']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_acf_search', [$this, 'handle_ajax_request']);
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

}