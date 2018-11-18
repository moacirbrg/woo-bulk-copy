<?php
namespace WooBulkCopy\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PluginsCompatibility {
	
	/**
	 * @return boolean
	 */
	public static function yoast_is_enabled() {
		return function_exists( 'wpseo_init' );
	}
	
	/**
	 * @param int $product_id
	 * @return NULL|number
	 */
	public static function yoast_get_primary_cat( $product_id ) {
		$cat_id = get_post_meta( $product_id, '_yoast_wpseo_primary_product_cat', true );
		return is_numeric( $cat_id ) ? intval( $cat_id ) : null;
	}
	
	public static function yoast_set_primary_cat( $product_id, $cat_id ) {
		update_post_meta( $product_id, '_yoast_wpseo_primary_product_cat', $cat_id );
	}
	
	/**
	 * @return boolean
	 */
	public static function woo_shipping_discount_is_enabled() {
		return class_exists( 'Woo_ShippingDiscount' );
	}
	
	/**
	 * @param int $product_id
	 * @return NULL|string
	 */
	public static function woo_shipping_discount_get_discount( $product_id ) {
		$discount = get_post_meta( $product_id, 'shipping_discount', true );
		return $discount ? $discount : null;
	}
	
	public static function woo_shipping_discount_set_discount( $product_id, $discount ) {
		update_post_meta( $product_id, 'shipping_discount', $discount );
	}
}