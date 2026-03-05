<?php

namespace WPPluginStarter\Includes;

defined('ABSPATH') || die();

class Mobile_Menu {
	public function __construct() {
		add_action('wp_body_open', [$this, 'mobile_menu_insert_html']);
	}

	public function mobile_menu_insert_html() {
?>
		<!-- Offcanvas -->
		<div class="offcanvas">
			<div class="flex-wrap">
				<?php
				if (has_custom_logo()) {
					the_custom_logo();
				}
				?>
				<div class="offcanvas__close">
					<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
						<circle cx="20" cy="20" r="19.5" stroke="#2F4153" />
						<path d="M26 14L14 26M14 14L26 26" stroke="#EBF1FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</div>
			</div>
			<div class="offcanvas__nav">
				<?php wp_nav_menu(['theme_location' => 'menu-1']); ?>
			</div>
		</div>

<?php
	}
}
