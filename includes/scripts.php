<?php namespace WSUWP\Plugin\NetworkInfo;

class Scripts {


	public static function init(){
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'wsuwp_network_admin_styles' ), 5 );
    }

    public static function wsuwp_network_admin_styles(){

        wp_enqueue_script( 'jquery' );

        //These files create the filtering functionality
        wp_enqueue_script( 'dataTables', Plugin::get( 'url' ) . '/assets/js/jquery.dataTables.min.js', array( 'jquery' ), WSUWPMULTISITEINFOVERSION , true );
        wp_enqueue_style( 'dataTables', Plugin::get( 'url' ) . '/assets/css/jquery.dataTables.min.css', array(), WSUWPMULTISITEINFOVERSION );
        wp_enqueue_script( 'wsuwp-multisite-info-scripts', Plugin::get( 'url' ) . '/assets/js/scripts.js', array( 'jquery' ), WSUWPMULTISITEINFOVERSION , true );

        wp_enqueue_style( 'wsuwp-network-admin-styles', Plugin::get( 'url' ) . '/assets/css/styles.min.css' , array(), Plugin::get('version'));
    }


}

Scripts::init();
