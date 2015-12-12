<?php
/**
 * ECP1Mapstraction Controller class.
 *
 * Provides methods for determining how maps are handled by the site.
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Define the class
class ECP1Mapstraction
{
	
	// Constants so we don't need to pass strings around
	const ADMIN = 100;
	const CLIENT = 101;
	
	// Default values for the Mapstraction provider settings
	static $DEFAULTS = array(
		'enabled' => false, 'mxnid' => null, 'name' => null, 'geocoder' => false,
		'apiurl' => null, 'has_ssl' => false
	);
	// Reference:
	//  enabled  - true or false: is this provider enabled
	//  mxnid    - mapstraction id used for this provider
	//  name     - text name for labelling this provider
	//  geocoder - true or false: does provider have a geocoder
	//  apiurl   - the API url (without http:// or https://) 
	//  has_ssl  - true or false: provides maps over SSL
	//
	// Which Mapstraction providers are supported
	static $PROVIDERS = array(
		// TODO
		'cartociudad' => array( 'enabled' => false ),

		// TODO
		'cloudmade' => array( 'enabled' => false ),

		// TODO
		'geocommons' => array( 'enabled' => false ),

		// TODO
		'google-old' => array( 'enabled' => false, 'mxnid' => 'google' ),

		// Google V3
		// Documentation:
		// 
		'google' => array(
			'enabled' => true, 'mxnid' => 'googlev3', 'name' => 'Google (v3)', 'geocoder' => true,
			'apiurl' => 'maps.googleapis.com/maps/api/js?sensor=false', 'has_ssl' => true,
		),

		// TODO
		'mapquest' => array( 'enabled' => false ),

		// TODO
		'microsoft' => array( 'enabled' => false ),

		// TODO
		'microsoftv7' => array( 'enabled' => false ),

		// TODO
		'multimap' => array( 'enabled' => false ),

		// OpenLayers
		// Documentation:
		//
		'openlayers' => array(
			'enabled' => true, 'mxnid' => 'openlayers', 'name' => 'OpenLayers', 'geocoder' => false, // doesn't work
			'apiurl' => 'openlayers.org/api/OpenLayers.js', 'has_ssl' => false,
		),

		// TODO
		'openspace' => array( 'enabled' => false ),

		// TODO
		'ovi' => array( 'enabled' => false ),

		// TODO
		'yahoo' => array( 'enabled' => false ),

		// TODO
		'yandex' => array( 'enabled' => false )
	);

	// Is this a valid provider
	public static function ValidProvider( $provider )
	{
		return array_key_exists( $provider, self::$PROVIDERS ) && self::ProviderData( $provider, 'enabled' );
	}

	// Is this a valid geocoder
	public static function ValidGeocoder( $provider )
	{
		return self::ValidProvider( $provider ) && self::ProviderData( $provider, 'geocoder' );
	}

	// Returns the value for the given provider / key
	public static function ProviderData( $provider, $key )
	{
		if ( ! array_key_exists( $provider, self::$PROVIDERS ) || ! array_key_exists( $key, self::$DEFAULTS ) )
			return null; // no such provider or no such key
		if ( ! array_key_exists( $key, self::$PROVIDERS[$provider] ) )
			return self::$DEFAULTS[$key]; // return the default value
		return self::$PROVIDERS[$provider][$key];
	}

	// Is this operating in secure HTTPS mode?
	private static function MapsSecure( $provider )
	{
		$has_ssl = self::ProviderData( $provider, 'has_ssl' );
		if ( $has_ssl && is_ssl() )
			return true; // in https and provider has ssl
		return false;
	}

	// Get the API URL for the given provider
	private static function ProviderAPI( $provider )
	{
		$url = self::ProviderData( $provider, 'apiurl' );
		if ( is_null( $url ) ) return null;
		if ( self::MapsSecure( $provider ) )
			return 'https://' . $url;
		return 'http://' . $url;
	}

	// Are maps enabled on this site
	public static function MapsEnabled()
	{
		if ( _ecp1_get_option( 'use_maps' ) )
			return true;
		return false;
	}

	// Which map provider is active
	public static function GetProviderKey()
	{
		$provider = _ecp1_get_option( 'map_provider' );
		if ( ! array_key_exists( $provider, self::$PROVIDERS ) )
			return null; // no provider chosen
		return $provider;
	}

	// Which geocoder is active
	public static function GetGeocoderKey()
	{
		$geocoder = _ecp1_get_option( 'map_geocoder' );
		if ( ! array_key_exists( $geocoder, self::$PROVIDERS ) || ! self::$PROVIDERS[$geocoder]['geocoder'] ) {
			$provider = self::GetProviderKey(); // get the maps provider if no known geocoder
			if ( ! is_null( $provider ) && self::$PROVIDERS[$provider]['geocoder'] )
				return $provider; // use maps provider if unknown geocoder and they are one
			return null; // no geocoder available
		}
		// A geocoder was given so return it
		return $geocoder;
	}

	// Enqueue the Mapstraction scripts and styles
	public static function EnqueueResources( $site )
	{
		// Are maps enabled and do we have a valid provider
		if ( ! self::MapsEnabled() ) return false;
		$provider = self::GetProviderKey();
		if ( is_null( $provider ) || ! self::ProviderData( $provider, 'enabled' ) )
			return false;

		// First enqueue the map provider API
		$apiurl = self::ProviderAPI( $provider );
		if ( ! is_null( $apiurl ) ) {
			wp_register_script( 'ecp1_map_provider_api', $apiurl, array(), null, false );
			wp_enqueue_script( 'ecp1_map_provider_api' );
		}

		// Second enqueue the mapstraction library
		$mxnid = self::ProviderData( $provider, 'mxnid' );
		if ( ! is_null( $mxnid ) ) {
			$geocoder = self::GetGeocoderKey();
			$apiurl = self::ProviderAPI( $geocoder );
			if ( ECP1Mapstraction::CLIENT == $site || is_null( $geocoder ) || ! self::ProviderData( $geocoder, 'geocoder' ) || is_null( $apiurl ) ) {
				// We're in client mode so don't need to geocoder OR
				// There is no geocoder so just load the maps provider
				wp_register_script( 'ecp1_mapstraction', plugins_url( "/mxn/mxn.js?($mxnid)", dirname( dirname( __FILE__ ) ) ), array(), null, false );
			} else if ( ECP1Mapstraction::ADMIN == $site ) {
				// In admin AND there is a geocoder so load it and it's provider
				// WordPress esc_url removes [] from around the module list in the URL
				// so we include a patched version of Mapstraction that uses : prefix
				if ( $geocoder == $provider ) {
					// Rely on the provider API load above
					wp_register_script( 'ecp1_mapstraction', plugins_url( "/mxn/mxn.js?($mxnid:geocoder)", dirname( dirname( __FILE__ ) ) ), array(), null, false );
				} else {
					// Load the geocoder API first
					wp_register_script( 'ecp1_geocoder_api', $apiurl, array(), null, false );
					wp_enqueue_script( 'ecp1_geocoder_api' );
					// Now load the Mapstraction layer for the geocoder
					$gmxnid = self::ProviderData( $geocoder, 'mxnid' );
					if ( is_null( $gmxnid ) ) {
						wp_register_script( 'ecp1_mapstraction', plugins_url( "/mxn/mxn.js?($mxnid)", dirname( dirname( __FILE__ ) ) ), array(), null, false );
					} else {
						wp_register_script( 'ecp1_geocoder', plugins_url( "/mxn/mxn.js?($mxnid,$gmxnid:geocoder)", dirname( dirname( __FILE__ ) ) ), array(), null, false );
					}
				}
			}
		}

		// Finally enqueue the mapstraction library
		wp_enqueue_script( 'ecp1_mapstraction' );
		if ( $geocoder != $provider && ! is_null( $mxnid ) )
			wp_enqueue_script( 'ecp1_geocoder' );
	}

	// Returns a string of <option></option> tags for the providers
	public static function ToOptionTags( $geocoders=false )
	{
		$current = self::GetProviderKey();
		if ( $geocoders )
			$current = self::GetGeocoderKey();
		$outstr  = '';
		foreach ( self::$PROVIDERS as $key=>$data ) {
			if ( $geocoders && ! self::ProviderData( $key, 'geocoder' ) )
				continue; // skip this entry not a geocoder
			if ( ! self::ProviderData( $key, 'enabled' ) && $current != $key )
				continue; // skip those not enabled and not active

			// Contruct a name for the provider
			$name = __( self::ProviderData( $key, 'name' ) );
			if ( ! self::ProviderData( $key, 'enabled' ) ) { 
				// just in case code disables an active provider
				$name = sprintf( '%s: %s', __( 'Disabled' ), $name );
			}

			// Append the option tag
			$outstr .= sprintf( '<option value="%s"%s>%s</option>', $key,
				$key == $current ? ' selected="selected"' : '', $name );
		}
		return $outstr;
	}

}

// Don't close the php interpreter
/*?>*/
