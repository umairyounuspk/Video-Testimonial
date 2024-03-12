<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://qtechstudios.com
 * @since      1.0.0
 *
 * @package    Qts_Video_Testimonials
 * @subpackage Qts_Video_Testimonials/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Qts_Video_Testimonials
 * @subpackage Qts_Video_Testimonials/includes
 * @author     QTech Studios <info@qtechstudios.com>
 */
class Qts_Video_Testimonials_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'qts-video-testimonials',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
