<?php
/*
Plugin Name: Can I Use? Shortcode
Plugin URI: http://reaktivstudios.com/
Description: Provide the latest "When Can I Use" information to your readers without manual maintanence.
Author: Andrew Norcross
Version: 0.0.1
Requires at least: 4.0
Author URI: http://reaktivstudios.com/
*/
/*  Copyright 2015 Andrew Norcross

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License (GPL v2) only.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if( ! defined( 'CIU_SHORTCODE_BASE ' ) ) {
	define( 'CIU_SHORTCODE_BASE', plugin_basename(__FILE__) );
}

if( ! defined( 'CIU_SHORTCODE_DIR' ) ) {
	define( 'CIU_SHORTCODE_DIR', plugin_dir_path( __FILE__ ) );
}

if( ! defined( 'CIU_SHORTCODE_VER' ) ) {
	define( 'CIU_SHORTCODE_VER', '0.0.1' );
}

// lets start the engine
class CIU_Shortcode_Core {

	/**
	 * Static property to hold our singleton instance
	 * @var $instance
	 */
	static $instance = false;

	/**
	 * this is our constructor.
	 * there are many like it, but this one is mine
	 */
	private function __construct() {
		add_action( 'plugins_loaded',                       array( $this, 'textdomain'                  )           );
		add_action( 'plugins_loaded',                       array( $this, 'load_files'                  )           );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return $instance
	 */
	public static function getInstance() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * load our textdomain for localization
	 *
	 * @return void
	 */
	public function textdomain() {
		load_plugin_textdomain( 'caniuse-shortcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * load our files
	 *
	 * @return [type] [description]
	 */
	public function load_files() {

		// bail on admin
		if ( is_admin() ) {
			return;
		}

		// load my files
		require_once( 'lib/front.php'   );
		require_once( 'lib/helper.php'  );
		require_once( 'lib/display.php' );
	}

/// end class
}

// Instantiate our class
$CIU_Shortcode_Core = CIU_Shortcode_Core::getInstance();