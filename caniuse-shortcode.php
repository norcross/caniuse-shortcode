<?php
/*
Plugin Name: Can I Use? Shortcode
Plugin URI: https://github.com/norcross/caniuse-shortcode
Description: Provide the latest "When Can I Use" information to your readers without manual maintanence.
Author: Andrew Norcross
Version: 0.0.1
Requires at least: 4.0
Author URI: http://reaktivstudios.com/
GitHub Plugin URI: https://github.com/norcross/caniuse-shortcode
*/
/*
The MIT License (MIT)

Copyright (c) 2015 Andrew Norcross

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
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