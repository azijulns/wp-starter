<?php

/**
 * Plugin Name: {{PLUGIN_NAME}}
 * Version:     1.0.0
 * Description: {{PLUGIN_DESCRIPTION}}
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Author:      {{PLUGIN_AUTHOR}}
 * Author URI:  {{PLUGIN_AUTHOR_URL}}
 * Text Domain: {{PLUGIN_TEXT_DOMAIN}}
 * Domain Path: /languages
 */
defined('ABSPATH') || die();

define('WPPS_PLUGIN_VERSION', time());
define('WPPS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPPS_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('WPPS_PLUGIN_ASSETS', trailingslashit(WPPS_PLUGIN_DIR_URL . 'assets'));

if (!class_exists('WPPS_MAIN')) :

    final class WPPS_MAIN {
        private static $instance;

        private function __construct() {
            add_action('plugins_loaded', [$this, 'init_plugin']);
        }

        public function init_plugin() {
            new WPPluginStarter\Includes\AssetsManager();
            new WPPluginStarter\Widgets\Init_Widgets();
            new WPPluginStarter\Includes\Mobile_Menu();
        }

        public static function instance() {
            if (!isset(self::$instance) && !(self::$instance instanceof WPPS_MAIN)) {
                self::$instance = new WPPS_MAIN();
                self::$instance->includes();
            }

            return self::$instance;
        }

        private function includes() {
            require_once WPPS_PLUGIN_DIR . 'includes/assets-manager.php';
            require_once WPPS_PLUGIN_DIR . 'includes/mobile-menu.php';
            require_once WPPS_PLUGIN_DIR . 'includes/hooks.php';
            require_once WPPS_PLUGIN_DIR . 'includes/functions.php';
            require_once WPPS_PLUGIN_DIR . 'widgets/init.php';
        }
    }

endif;

function wpps_init_plugin() {
    return WPPS_MAIN::instance();
}

wpps_init_plugin();
