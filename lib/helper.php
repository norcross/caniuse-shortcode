<?php
/**
 * Can I Use? Shortcode - Helper Module
 *
 * Contains data, query, and template modules
 *
 * @package Can I Use Shortcode
 */

/**
 * Lets start the engine.
 */
class CIU_Shortcode_Helper
{

	/**
	 * Return an array of the items you want to check support on.
	 *
	 * @param  string $key  Which item we're checking for.
	 *
	 * @return array
	 */
	public static function get_support_checks( $key = '' ) {

		// Bail if no key is present.
		if ( empty( $key ) ) {
			return false;
		}

		// Set the desktop checks.
		$desk   = apply_filters( 'ciu_checks_desktop', array( 'chrome', 'firefox', 'ie', 'opera', 'safari' ) );

		// Set the mobile checks.
		$mobile = apply_filters( 'ciu_checks_mobile', array( 'ios_saf', 'android', 'op_mob', 'and_chr', 'and_ff' ) );

		// Return desktop.
		if ( 'desktop' === $key ) {
			return $desk;
		}

		// Return mobile.
		if ( 'mobile' === $key ) {
			return $mobile;
		}

		// Set a combined array and return them.
		return apply_filters( 'ciu_checks_grouped', array( 'desktop' => $desk, 'mobile' => $mobile ) );
	}

	/**
	 * Get the support data for a single feature.
	 *
	 * @param  string $feature  The feature we want to get support data.
	 *
	 * @return array
	 */
	public static function get_support_data( $feature = '' ) {

		// Bail without a feature request.
		if ( empty( $feature ) ) {
			return;
		}

		// Make a clean key from our feature.
		$k  = sanitize_key( 'ciu_support_' . $feature );

		// Check for transient before going forward.
		if ( false === $data = get_transient( $k ) ) {

			// Construct the API URL and if for some reason we messed up the URL, bail.
			if ( false === $url = apply_filters( 'ciu_data_url', CIU_SHORTCODE_URL ) ) {
				return array(
					'error' => true,
					'cause' => 'NO_SOURCE_URL',
				);
			}

			// Set the default HTTP call args with an optional filter.
			$args   = apply_filters( 'ciu_data_args', array( 'timeout' => 30, 'sslverify' => false ) );

			// Make the request.
			$resp   = wp_remote_get( esc_url( $url ), $args );

			// Bail on empty return.
			if ( empty( $resp ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_RESPONSE',
				);
			}

			// Bail on wp_error.
			if ( is_wp_error( $resp ) ) {
				return array(
					'error' => true,
					'cause' => 'API_ERROR',
				);
			}

			// Get our response code.
			$code   = wp_remote_retrieve_response_code( $resp );

			// Bail on 404.
			if ( 200 !== $code ) {
				return array(
					'error' => true,
					'cause' => 'RESPONSE_CODE',
					'code'  => $code,
				);
			}

			// Get the body.
			$body   = wp_remote_retrieve_body( $resp );

			// Bail on empty body.
			if ( empty( $body ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_BODY',
				);
			}

			// Decode the JSON.
			$decode = json_decode( $body, true );

			// Bail on empty JSON.
			if ( empty( $decode ) || empty( $decode['data'] ) ) {
				return array(
					'error' => true,
					'cause' => 'EMPTY_JSON',
				);
			}

			// Bail if data portion not found.
			if ( ! array_key_exists( $feature, $decode['data'] ) ) {
				return array(
					'error' => true,
					'cause' => 'KEY_NOT_FOUND',
				);
			}

			// Set my data element.
			$data   = $decode['data'][ $feature ];

			// Bail if no stats exist.
			if ( ! array_key_exists( 'stats', $data ) ) {
				return array(
					'error' => true,
					'cause' => 'NO_STATS_FOUND',
				);
			}

			// Set the transient.
			set_transient( $k, $data, DAY_IN_SECONDS );
		}

		// Return the data.
		return $data;
	}

	/**
	 * Get the visual label from a key for status.
	 *
	 * @param  string $key  Which label key we want.
	 *
	 * @return string
	 */
	public static function get_spec_status_label( $key = '' ) {

		// Bail with no key.
		if ( empty( $key ) ) {
			return false;
		}

		// Set the labels in an array.
		$labels = array(
			'rec'   => __( 'W3C Recommendation', 'caniuse-shortcode' ),
			'pr'    => __( 'W3C Proposed Recommendation', 'caniuse-shortcode' ),
			'cr'    => __( 'W3C Candidate Recommendation', 'caniuse-shortcode' ),
			'wd'    => __( 'W3C Working Draft', 'caniuse-shortcode' ),
			'other' => __( 'Non-W3C, but Reputable', 'caniuse-shortcode' ),
			'unoff' => __( 'Unofficial or W3C "Note"', 'caniuse-shortcode' ),
		);

		// Return the key.
		return array_key_exists( $key, $labels ) ? $labels[ $key ] : false;
	}

	/**
	 * Get the visual label from a key for support.
	 *
	 * @param  string $key  Which label key we want.
	 *
	 * @return string
	 */
	public static function get_spec_support_label( $key = '' ) {

		// Bail with no key.
		if ( empty( $key ) ) {
			return false;
		}

		// Set the labels in an array.
		$labels = array(
			'y' => __( 'Yes', 'caniuse-shortcode' ),
			'x' => __( 'With Prefix', 'caniuse-shortcode' ),
			'n' => __( 'No', 'caniuse-shortcode' ),
			'a' => __( 'Partial Support', 'caniuse-shortcode' ),
			'p' => __( 'Polyfill', 'caniuse-shortcode' ),
			'u' => __( 'Unknown', 'caniuse-shortcode' ),
		);

		// Return the key.
		return array_key_exists( $key, $labels ) ? $labels[ $key ] : false;
	}

	/**
	 * Get the proper label for a browser key.
	 *
	 * @param  string $key  Which label key we want.
	 *
	 * @return string
	 */
	public static function get_browser_label( $key = '' ) {

		// Bail with no key.
		if ( empty( $key ) ) {
			return false;
		}

		// Set the labels in an array.
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

		// Return the key.
		return array_key_exists( $key, $labels ) ? $labels[ $key ] : false;
	}

	/**
	 * Check each item against the chosen browser and return the proper results.
	 *
	 * @param  array $dataset  Our entire dataset that we're parsing.
	 *
	 * @return array
	 */
	public static function get_support_result( $dataset = array() ) {

		// Bail if no dataset was provided.
		if ( empty( $dataset ) || ! is_array( $dataset ) ) {
			return false;
		}

		// Loop my dataset.
		foreach ( $dataset as $version => $result ) {

			// Do our flag substring check.
			$flag   = self::trim_string( $result );

			// Check for the flag in our support array.
			if ( empty( $flag ) || ! in_array( $flag, array( 'y', 'a', 'p' ) ) ) {
				continue;
			}

			// Return our array.
			return array(
				'flag'  => $flag,
				'vers'  => self::trim_string( $version, 3, true ),
				'pfix'  => self::check_support_prefix( $result ),
			);
		}

		// Set my fallback support results.
		return array( 'flag' => 'n', 'vers' => '', 'pfix' => false );
	}

	/**
	 * Trim a string we have.
	 *
	 * @param  string  $string  The string to trim.
	 * @param  integer $count   How many characters to trim by.
	 * @param  boolean $period  Whether to also check for a period and trim that.
	 *
	 * @return string
	 */
	public static function trim_string( $string = '', $count = 1, $period = false ) {

		// Do the string cleanup.
		$string = substr( trim( $string ), 0, absint( $count ) );

		// Return it with our withour the period check.
		return ! empty( $period ) ? rtrim( $string, '.' ) : $string;
	}

	/**
	 * Check if a particular support item requires a prefix or not.
	 *
	 * @param  string $result  The result of the check.
	 *
	 * @return bool
	 */
	public static function check_support_prefix( $result = '' ) {
		return false !== stripos( $result, 'x' ) ? true : false;
	}

	// End class.
}

// Instantiate our class.
new CIU_Shortcode_Helper();
