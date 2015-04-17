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
	//	if( false === $data = get_transient( 'ciu_support_' . $key ) ) {

			// set the default HTTP call args
			$args	= array(
				'timeout'	=> 30,
				'sslverify'	=> false
			);

			// construct the API URL
			$url    = 'https://raw.github.com/Fyrd/caniuse/master/data.json';

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

			// bail if key not found
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
	//		set_transient( 'ciu_support_' . $key, $data, DAY_IN_SECONDS );
	//	}

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
	 * get the header items for the display
	 *
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public static function get_display_header( $data = array() ) {

		// set an empty build
		$build  = '';

		// the header
		$build .= '<div class="caniuse-header">';

			// show the title
			if ( ! empty( $data['title'] ) ) {
				$build .= '<h3>' . esc_attr( $data['title'] ) . '</h3>';
			}

			// show the description
			if ( ! empty( $data['description'] ) ) {
				$build .= wpautop( esc_attr( $data['description'] ) );
			}

			// show the status
			if ( ! empty( $data['status'] ) && false !== $label = self::get_spec_status_label( $data['status'] ) ) {
				$build .= '<p class="status">' . esc_attr( $label ) . '</p>';
			}

			// and the supported intro
			$build .= '<p>' . __( 'Supported from the following versions:', 'caniuse-shortcode' ) . '</p>';

		// close the header
		$build .= '</div>';

		// return it
		return $build;
	}

	/**
	 * get the items for the display
	 *
	 * @param  array  $data  [description]
	 * @param  string $check [description]
	 * @param  string $title [description]
	 * @return [type]        [description]
	 */
	public static function get_display_row( $data = array(), $check = '', $title = '' ) {

		// bail with no check key
		if ( empty( $check ) || false === $checks = self::get_support_checks( $check ) ) {
			return;
		}

		// set an empty build
		$build  = '';

		// the header
		$build .= '<div class="caniuse-section">';

			// show the title
			if ( ! empty( $title ) ) {
				$build .= '<h4>' . esc_attr( $title ) . '</h4>';
			}

			// now the output of lists
			$build .= '<ul class="agents">';

			// loop them
			foreach ( $checks as $key => $browser ) {

				// check to make sure we have it
				if ( empty( $data[ $browser ] ) ) {
					continue;
				}

				$build .= '<li class="icon-' . esc_attr( $browser ). ' y" title="Chrome - Yes"><span class="version">21*</span></li>';

			}

			// close the list output
			$build .= '</ul>';

		// close the header
		$build .= '</div>';

		// return it
		return $build;
	}


	/**
	 * get the legend for the display
	 *
	 * @return [type]        [description]
	 */
	public static function get_display_legend( $data = array(), $check = '', $title = '' ) {

		// set an empty build
		$build  = '';

		// the header
		$build .= '<div class="caniuse-section caniuse-section-legend">';

			// now the output of lists
			$build .= '<ul class="legend">';

				// list each thing
				$build .= '<li>' . __( 'Supported:', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="y">' . __( 'Yes', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="n">' . __( 'No', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="a">' . __( 'Partially', 'caniuse-shortcode' ) . '</li>';
				$build .= '<li class="p">' . __( 'Polyfill', 'caniuse-shortcode' ) . '</li>';

			// close the list output
			$build .= '</ul>';

			// show the source
			$build .= '<p class="stats">' . sprintf( __( 'Stats from <a target="_blank" href="%s">caniuse.com</a>', 'caniuse-shortcode' ), esc_url( 'http://caniuse.com/#feat=stream' ) ) . '</p>';

		// close the header
		$build .= '</div>';

		// return it
		return $build;
	}

// end class
}

// end exists check
}

// Instantiate our class
new CIU_Shortcode_Helper();
