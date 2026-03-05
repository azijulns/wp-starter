<?php

namespace WPPluginStarter\Includes;

defined('ABSPATH') || die();

class Ajax {
public function __construct() {
add_action('wp_ajax_{{plugin_prefix}}_get_posts', [$this, 'get_posts']);
add_action('wp_ajax_nopriv_{{plugin_prefix}}_get_posts', [$this, 'get_posts']);
}

public function get_posts() {
check_ajax_referer('{{plugin-prefix}}-nonce', 'security');

// Handle your AJAX request here and send back a response.
wp_send_json_success([]);

wp_die();
}
}