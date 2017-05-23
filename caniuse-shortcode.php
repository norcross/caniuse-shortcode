<?php
/*
Plugin Name: Can I Use? Shortcode
Plugin URI: https://github.com/norcross/caniuse-shortcode
Description: Provide the latest "When Can I Use" information to your readers without manual maintanence.
Author: Andrew Norcross
Version: 0.0.5
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

// Define our base.
if ( ! defined( 'CIU_SHORTCODE_BASE' ) ) {
	define( 'CIU_SHORTCODE_BASE', plugin_basename(__FILE__) );
}

// Define our directory.
if ( ! defined( 'CIU_SHORTCODE_DIR' ) ) {
	define( 'CIU_SHORTCODE_DIR', plugin_dir_path( __FILE__ ) );
}

// Define our version.
if ( ! defined( 'CIU_SHORTCODE_VER' ) ) {
	define( 'CIU_SHORTCODE_VER', '0.0.5' );
}

// Define our source data URL.
if ( ! defined( 'CIU_SHORTCODE_URL' ) ) {
	define( 'CIU_SHORTCODE_URL', 'https://raw.github.com/Fyrd/caniuse/master/data.json' );
}

/**
 * Lets start the engine.
 */
class CIU_Shortcode_Core {

	/**
	 * Load our hooks and filters.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'plugins_loaded',                       array( $this, 'textdomain'                  )           );
		add_action( 'plugins_loaded',                       array( $this, 'load_files'                  )           );
	}

	/**
	 * Load our textdomain for localization.
	 *
	 * @return void
	 */
	public function textdomain() {
		load_plugin_textdomain( 'caniuse-shortcode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Load our files.
	 *
	 * @return void
	 */
	public function load_files() {

		// Don't call any of this on admin.
		if ( is_admin() ) {
			return;
		}

		// Load my files.
		require_once( CIU_SHORTCODE_DIR . 'lib/front.php' );
		require_once( CIU_SHORTCODE_DIR . 'lib/helper.php' );
		require_once( CIU_SHORTCODE_DIR . 'lib/display.php' );
	}

	// End class.
}

// Instantiate our class.
$CIU_Shortcode_Core = new CIU_Shortcode_Core();
$CIU_Shortcode_Core->init();
