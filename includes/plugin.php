<?php namespace WSUWP\Plugin\NetworkInfo;

class Plugin {
    public static function init() {
		require_once __DIR__ . '/scripts.php';
		require_once __DIR__ . '/multisite-info.php';
	}

    // Gets wsuwp Gutenberg plugin URL.
    public static function _get_wsuwp_network_admin_plugin_url() {
        static $wsuwp_network_admin_plugin_url;
    
        if (empty($wsuwp_network_admin_plugin_url)) {
            $wsuwp_network_admin_plugin_url = plugins_url(null, __FILE__);
        }
    
        return $wsuwp_network_admin_plugin_url;
    }

    public static function get( $property ) {

		switch ( $property ) {

			case 'version':
				return WSUWPMULTISITEINFOVERSION;

            case 'url':
                return plugin_dir_url( dirname( __FILE__ ) );

            case 'dir':
                return plugin_dir_path( dirname( __FILE__ ) );

			default:
				return '';

		}

	}
}

Plugin::init();