<?php

if (!defined('ABSPATH')) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class WPPS_Menu extends Widget_Base {

	public function get_name() {
		return '{{plugin_prefix}}_menu';
	}

	public function get_title() {
		return __('{{PLUGIN_NAME}} Menu', '{{PLUGIN_TEXT_DOMAIN}}');
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_categories() {
		return ['basic'];
	}

	protected function _register_controls() {
		$menus = wp_get_nav_menus();
		$menu_options = [];

		if ($menus) {
			foreach ($menus as $menu) {
				$menu_options[$menu->term_id] = $menu->name;
			}
		}

		$this->start_controls_section(
			'menu_section',
			[
				'label' => __('Menu Selection', '{{PLUGIN_TEXT_DOMAIN}}'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'selected_menu',
			[
				'label'   => __('Select Menu', '{{PLUGIN_TEXT_DOMAIN}}'),
				'type'    => Controls_Manager::SELECT,
				'options' => $menu_options,
				'default' => '',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$menu_id  = $settings['selected_menu'];

		if ($menu_id) {
			wp_nav_menu([
				'menu'        => $menu_id,
				'container'   => false,
				'menu_class'  => '{{plugin-prefix}}-menu',
				'fallback_cb' => false,
				'echo'        => true,
			]);
		} else {
			echo '<p>' . esc_html__('No menu selected', '{{PLUGIN_TEXT_DOMAIN}}') . '</p>';
		}
	}
}
