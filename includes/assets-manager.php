<?php

namespace WPPluginStarter\Includes;

defined('ABSPATH') || die();

class AssetsManager {
    public $prefix = '{{plugin-prefix}}-';

    public function __construct() {
        add_action('wp_enqueue_scripts',    [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_admin_scripts() {
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            '{{plugin-prefix}}-public',
            WPPS_PLUGIN_ASSETS . 'css/public.css',
            null,
            WPPS_PLUGIN_VERSION,
            'all'
        );

        wp_register_style(
            $this->prefix . 'slick',
            WPPS_PLUGIN_ASSETS . 'css/slick.css',
            null,
            WPPS_PLUGIN_VERSION,
            'all'
        );

        wp_register_style(
            $this->prefix . 'slick-theme',
            WPPS_PLUGIN_ASSETS . 'css/slick-theme.css',
            null,
            WPPS_PLUGIN_VERSION,
            'all'
        );

        // JS
        wp_enqueue_script(
            '{{plugin-prefix}}-public',
            WPPS_PLUGIN_ASSETS . 'js/frontend.js',
            ['jquery'],
            WPPS_PLUGIN_VERSION,
            true
        );

        wp_register_script(
            $this->prefix . 'slick',
            WPPS_PLUGIN_ASSETS . 'js/slick.min.js',
            ['jquery'],
            WPPS_PLUGIN_VERSION,
            true
        );

        wp_localize_script(
            '{{plugin-prefix}}-public',
            '{{plugin_prefix}}_ajax',
            [
                'security' => wp_create_nonce('{{plugin-prefix}}-nonce'),
                'ajaxurl'  => admin_url('admin-ajax.php'),
            ]
        );
    }
}
