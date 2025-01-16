<?php

class PasBlock {

	public static $instance = null;

    public static function instance() {
        // Initialize the plugin
        if (null === self::$instance) {
            self::$instance = new self();
        } 
        return self::$instance;
    }

    private function __construct() {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        add_action('init', [$this, 'register_block']);
    }

    public function enqueue_block_editor_assets() {
        wp_register_script(
            'pas-block-editor-script',
            ACF_SEARCHER_URL . '/assets/js/pas-block.js',
            ['wp-blocks', 'wp-element', 'wp-editor'],
            '1.0.0',
            true
        );
    }

    public function register_block() {
        register_block_type('acf-searcher/pas-block', [
            'editor_script' => 'pas-block-editor-script',
            'render_callback' => [$this, 'render_block'],
        ]);
    }

    public function render_block($attributes) {
        return '<div></div>';
    }
}
