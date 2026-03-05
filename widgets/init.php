<?php

namespace WPPluginStarter\Widgets;

defined('ABSPATH') || die();

class Init_Widgets {
	public function __construct() {
		add_action('elementor/widgets/register', [$this, 'init_widgets']);
	}

	public function init_widgets($widgets_manager) {
		require_once WPPS_PLUGIN_DIR . '/widgets/menu-widget.php';
		require_once WPPS_PLUGIN_DIR . '/widgets/menu.php';

		$widgets_manager->register(new \WPPS_Menu_Widget());
		$widgets_manager->register(new \WPPS_Menu());
	}
}
