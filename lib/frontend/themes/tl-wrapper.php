<?php
/*
Plugin Name: TL wrapper
Plugin URI:
Description: This plugin get header and footer from Trueloaded
Author: Holbi
Version: 1.0.0
Author URI: https://www.holbi.co.uk/
*/


class TlWrapper {

    private static $initiated = false;
    private static $headerContent = '';
    private static $footerContent = '';
    private static $tlw_url = 'tlw_url';

    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    /**
     * Initializes WordPress hooks
     */
    private static function init_hooks() {
        self::$initiated = true;

        self::getDataFromTl();

        add_action( 'wp_body_open', array( 'TlWrapper', 'tl_header' ));
        add_action( 'wp_footer', array( 'TlWrapper', 'tl_footer' ));

        add_action('admin_menu', array( 'TlWrapper', 'add_option_field_to_general_admin_page' ));
    }



    public static function getDataFromTl() {
        $file = file_get_contents(esc_attr( get_option(self::$tlw_url) ));
        $arr = explode('<!-- body-content -->', $file);
        self::$headerContent = $arr[0];
        self::$footerContent = $arr[1];
    }

    public static function tl_header() {
        echo self::$headerContent;
    }

    public static function tl_footer() {
        echo self::$footerContent;
    }


    public static function add_option_field_to_general_admin_page(){

        register_setting( 'general', self::$tlw_url );

        add_settings_field(
            'tlw_url-id',
            'Trueloaded wrapper url',
            array( 'TlWrapper', 'tlw_url_callback_function' ),
            'general',
            'default',
            array(
                'id' => 'tlw_url-id',
                'option_name' => 'tlw_url'
            )
        );
    }

    public static function tlw_url_callback_function( $val ){
        $id = $val['id'];
        $option_name = $val['option_name'];
        ?>
        <input
            type="text"
            name="<? echo $option_name ?>"
            id="<? echo $id ?>"
            value="<? echo esc_attr( get_option($option_name) ) ?>"
            class="regular-text"
        />
        <?
    }

}

add_action( 'init', array( 'TlWrapper', 'init' ) );