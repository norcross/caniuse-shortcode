<?php
/**
 * Can I Use? Shortcode - Helper Module
 *
 * Contains data, query, and template modules
 *
 * @package Can I Use Shortcode
 */
/*  Copyright 2015 Reaktiv Studios

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

if ( ! class_exists( 'CIU_Shortcode_Helper' ) ) {

// Start up the engine
class CIU_Shortcode_Helper
{

	/**
	 * return an array of the items you want to check support on
	 *
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function get_support_checks( $key = '' ) {

		// set the desktop
		$desk   = apply_filters( 'ciu_checks_desktop', array( 'chrome', 'firefox', 'ie', 'opera', 'safari' ) );

		// set the mobile
		$mobile = apply_filters( 'ciu_checks_mobile', array( 'ios_saf', 'android', 'op_mob', 'and_chr', 'and_ff' ) );

		// return desktop
		if ( ! empty( $key ) && $key == 'desktop' ) {
			return $desk;
		}

		// return mobile
		if ( ! empty( $key ) && $key == 'mobile' ) {
			return $mobile;
		}

		// set a combined array
		$checks = array( 'desktop' => $desk, 'mobile' => $mobile );

		// return them
		return apply_filters( 'ciu_checks_grouped', $checks );
	}

	/**
	 * get the support data for a single feature
	 * @param  string $feature [description]
	 * @return [type]          [description]
	 */
	public static function get_support_data( $feature = '' ) {

		// bail without a feature request
		if ( empty( $feature ) ) {
			return;
		}

		// make a clean key from our feature
		$key    = sanitize_title_with_dashes( $feature, '', 'save' );

		// check for transient before going forward
		if( false === $data = get_transient( 'ciu_support_' . $key ) ) {

			// set the default HTTP call args
			$args	= array(
				'timeout'	=> 30,
				'sslverify'	=> false
			);

			// construct the API URL
			$url    = apply_filters( 'ciu_data_url', 'https://raw.github.com/Fyrd/caniuse/master/data.json' );

			// if for some reason we messed up the URL, bail
			if ( empty( $url ) ) {
				return array(
					'error' => true,
					'cause' => 'NO_SOURCE_URL'
				);
			}

			// make the request
			$resp   = wp_remote_get( esc_url( $url ), $args );

			// bail on empty return
			if ( empty( $resp ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_RESPONSE'
				);
			}

			// bail on wp_error
			if ( is_wp_error( $resp ) ) {
				return array(
					'error' => true,
					'cause' => 'API_ERROR'
				);
			}

			// get our response code
			$code   = wp_remote_retrieve_response_code( $resp );

			// bail on 404
			if ( $code !== 200 ) {
				return array(
					'error' => true,
					'cause' => 'RESPONSE_CODE',
					'code'  => $code
				);
			}

			// get the body
			$body   = wp_remote_retrieve_body( $resp );

			// bail on empty body
			if ( empty( $body ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_BODY'
				);
			}

			// decode the JSON
			$decode = json_decode( $body, true );

			// bail on empty JSON
			if ( empty( $decode ) || empty( $decode['data'] ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_JSON'
				);
			}

			// bail if data portion not found
			if ( ! array_key_exists( $key, $decode['data'] ) ) {
				return array(
					'error' => true,
					'cause' => 'KEY_NOT_FOUND'
				);
			}

			// set my data element
			$data   = $decode['data'][$key];

			// bail if no stats exist
			if ( ! array_key_exists( 'stats', $data ) ) {
				return array(
					'error' => true,
					'cause' => 'NO_STATS_FOUND'
				);
			}

			// set the transient
			set_transient( 'ciu_support_' . $key, $data, DAY_IN_SECONDS );
		}

		// return the data
		return $data;
	}

	/**
	 * get the visual label from a key
	 *
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function get_spec_status_label( $key = '' ) {

		// bail with no key
		if ( empty( $key ) ) {
			return false;
		}

		// set the labels in an array
		$labels = array(
			'rec'   => __( 'W3C Recommendation', 'caniuse-shortcode' ),
			'pr'    => __( 'W3C Proposed Recommendation', 'caniuse-shortcode' ),
			'cr'    => __( 'W3C Candidate Recommendation', 'caniuse-shortcode' ),
			'wd'    => __( 'W3C Working Draft', 'caniuse-shortcode' ),
			'other' => __( 'Non-W3C, but Reputable', 'caniuse-shortcode' ),
			'unoff' => __( 'Unofficial or W3C "Note"', 'caniuse-shortcode' ),
		);

		// return the key
		return array_key_exists( $key, $labels ) ? $labels[$key] : false;
	}

	/**
	 * get the visual label from a key for support
	 *
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function get_spec_support_label( $key = '' ) {

		// bail with no key
		if ( empty( $key ) ) {
			return false;
		}

		// set the labels in an array
		$labels = array(
			'y' => __( 'Yes', 'caniuse-shortcode' ),
			'x' => __( 'With Prefix', 'caniuse-shortcode' ),
			'n' => __( 'No', 'caniuse-shortcode' ),
			'a' => __( 'Partial Support', 'caniuse-shortcode' ),
			'p' => __( 'Polyfill', 'caniuse-shortcode' ),
			'u' => __( 'Unknown', 'caniuse-shortcode' ),
		);

		// return the key
		return array_key_exists( $key, $labels ) ? $labels[$key] : false;
	}

	/**
	 * get the proper label for a browser key
	 *
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function get_browser_label( $key = '' ) {

		// bail with no key
		if ( empty( $key ) ) {
			return false;
		}

		// set the labels in an array
		$labels = array(
			'android'   => __( 'Android', 'caniuse-shortcode' ),
			'and_ff'    => __( 'Android Firefox', 'caniuse-shortcode' ),
			'and_chr'   => __( 'Android Chrome', 'caniuse-shortcode' ),
			'bb'        => __( 'Blackberry', 'caniuse-shortcode' ),
			'chrome'    => __( 'Google Chrome', 'caniuse-shortcode' ),
			'firefox'   => __( 'Mozilla Firefox', 'caniuse-shortcode' ),
			'ie'        => __( 'Internet Explorer', 'caniuse-shortcode' ),
			'ios_saf'   => __( 'iOS Safari', 'caniuse-shortcode' ),
			'opera'     => __( 'Opera', 'caniuse-shortcode' ),
			'op_mini'   => __( 'Opera Mini', 'caniuse-shortcode' ),
			'op_mob'    => __( 'Opera Mobile', 'caniuse-shortcode' ),
			'safari'    => __( 'Apple Safari', 'caniuse-shortcode' ),
		);

		// return the key
		return array_key_exists( $key, $labels ) ? $labels[$key] : false;
	}

	/**
	 * check each item against the chosen browser and return
	 * the proper results
	 *
	 * @param  array  $dataset [description]
	 * @return [type]          [description]
	 */
	public static function get_support_result( $dataset = array() ) {

		// do a quick polyfill check
		$partcheck = array_search( 'a', $dataset );

		// loop my dataset
		foreach ( $dataset as $vers => $result ) {

			// strip my version down to handle the weird stuff
			$vers   = substr( trim( $vers ), 0, 3 );

			// if we have a "y" in the thing, do it
			if ( substr( trim( $result ), 0, 1 ) == 'y' ) {

				// check for prefix setup
				$pfix   = false !== stripos( $result, 'x' ) ? true : false;

				// and return it
				return array( 'flag' => 'y', 'vers' => $vers, 'pfix' => $pfix );
			}

			// no direct "yes" so check for partial
			if ( substr( trim( $result ), 0, 1 ) == 'a' ) {

				// check for prefix setup
				$pfix   = false !== stripos( $result, 'x' ) ? true : false;

				// and return it
				return array( 'flag' => 'a', 'vers' => $vers, 'pfix' => $pfix );
			}

			// no direct "yes" or "partial" so check for polyfill
			if ( false === $partcheck && substr( trim( $result ), 0, 1 ) == 'p' ) {

				// check for prefix setup
				$pfix   = false !== stripos( $result, 'x' ) ? true : false;

				// and return it
				return array( 'flag' => 'p', 'vers' => $vers, 'pfix' => $pfix );
			}

			// no direct "yes", "partial", or "polyfill" so check for unknown
			if ( substr( trim( $result ), 0, 1 ) == 'u' ) {

				// check for prefix setup
				$pfix   = false !== stripos( $result, 'x' ) ? true : false;

				// and return it
				return array( 'flag' => 'u', 'vers' => $vers, 'pfix' => $pfix );
			}
		}

		// set my fallback support results
		return array( 'flag' => 'n', 'vers' => '', 'pfix' => false );
	}

// end class
}

// end exists check
}

// Instantiate our class
new CIU_Shortcode_Helper();
