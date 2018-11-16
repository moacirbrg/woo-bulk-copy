<?php
namespace WooBulkCopy;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BulkCopy {
	
	public static function init() {
		if ( is_admin() ) {
			self::admin_includes();
			add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		}
	}
	
	public static function admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Bulk copy', WOO_BULKCOPY ),
			__( 'Bulk copy', WOO_BULKCOPY ),
			'manage_options',
			WOO_BULKCOPY_BASEURL,
			array( '\WooBulkCopy\AdminPage', 'init' )
		);
	}
	
	public static function admin_includes() {
		include_once dirname( __FILE__ ) . '/utils/options-page-builder.php';
		include_once dirname( __FILE__ ) . '/utils/field.php';
		include_once dirname( __FILE__ ) . '/utils/checkbox-field.php';
		include_once dirname( __FILE__ ) . '/utils/number-field.php';
		include_once dirname( __FILE__ ) . '/utils/select-field.php';
		include_once dirname( __FILE__ ) . '/admin-page.php';
	}
}