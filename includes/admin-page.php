<?php
namespace WooBulkCopy;

use WooBulkCopy\Utils\OptionsPageBuilder as Builder;
use WooBulkCopy\Utils\CheckboxField;
use WooBulkCopy\Utils\NumberField;
use WooBulkCopy\Utils\SelectField;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AdminPage {

	private static $_url = 'admin.php?page=' . WOO_SKNIFE_BASEURL . '-products-managament';

	public static function init() {
		// Feedback message
		try {
			if ( Builder::is_post_data( WOO_BULKCOPY ) ) {
				self::process_page( Builder::get_form_data( WOO_BULKCOPY ) );
				Builder::show_admin_notice_updated( __( 'Products have been added successfully', WOO_BULKCOPY ) );
			}
		}
		catch( \Exception $e ) {
			Builder::show_admin_notice_error( $e->getMessage() );
		}
		
		// Header
		Builder::show_page_header( __( 'Product management', WOO_BULKCOPY ) );
		Builder::show_form_begin( WOO_BULKCOPY );
		
		// Page
		Builder::show_subtitle( __( 'Template', WOO_BULKCOPY ) );
		Builder::show_title_description( __( 'Select the fields you would like to copy from template', WOO_BULKCOPY ) );
		Builder::show_form_fields( WOO_BULKCOPY, self::create_template_fields() );
		
		Builder::show_subtitle( __( 'Products to update', WOO_BULKCOPY ) );
		Builder::show_title_description( __( 'Select a group of product to be updated', WOO_BULKCOPY ) );
		Builder::show_form_fields( WOO_BULKCOPY, self::create_update_fields() );
		
		Builder::show_form_submit_button( __( 'Update products', WOO_BULKCOPY ) );
		
		// Footer
		Builder::show_form_end();
		Builder::show_page_footer();
	}
	
	public static function create_template_fields() {
		return [
			new NumberField(
				'template_id',
				'template_id',
				__( 'Product', WOO_BULKCOPY ),
				__( 'ID of the product to be copied.', WOO_BULKCOPY ) ),
			new CheckboxField(
				'copy-categories',
				'template_data[]',
				'categories',
				__( 'Categories', WOO_BULKCOPY ) ),
			new CheckboxField(
				'copy-weight',
				'template_data[]',
				'weight',
				__( 'Weight', WOO_BULKCOPY ) ),
			new CheckboxField(
				'copy-dimensions',
				'template_data[]',
				'dimensions',
				__( 'Dimensions', WOO_BULKCOPY ) ),
			new CheckboxField(
				'copy-shipping-discount',
				'template_data[]',
				'shipping-discount',
				__( 'Shipping discount', WOO_BULKCOPY ) ),
			new CheckboxField(
				'copy-attributes',
				'template_data[]',
				'attributes',
				__( 'Attributes', WOO_BULKCOPY ) ),
			new CheckboxField(
				'copy-variations',
				'template_data[]',
				'variations',
				__( 'Variations', WOO_BULKCOPY ) ),
			new CheckboxField(
				'copy-price',
				'template_data[]',
				'price',
				__( 'Price', WOO_BULKCOPY ) )
		];
	}
	
	public static function create_update_fields() {
		return [
			new NumberField(
				'update_product_id',
				'update_product_id',
				__( 'Product', WOO_BULKCOPY ),
				__( 'ID of the product to be updated.', WOO_BULKCOPY ) ),
			new SelectField(
				'update_category_id',
				'update_category_id',
				self::get_product_categories(),
				__( 'Category', WOO_BULKCOPY ),
				__( 'Products from this category will be updated.', WOO_BULKCOPY ) ),
		];
	}
	
	public static function get_product_categories() {
		$wp_categories = get_terms( array(
			'taxonomy' => 'product_cat'
		) );
		
		$func = function( $item ) {
			return [ 'value' => $item->term_id, 'name' => $item->name ];
		};
		
		$categories = array_map ( $func, $wp_categories );
		array_unshift( $categories, [ 'value' => '', 'name' => __( 'Select a category', WOO_BULKCOPY ) ] );
		
		return $categories;
	}
	
    public static function get_template_form_data( $form ) {
    	if ( ! is_numeric( $form['template_id'] ) ) {
    		throw new \Exception( __( 'You must set a product to be the copied.', WOO_BULKCOPY ) );
    	}
    	
    	$fields_to_copy = array(
    		'categories'        => false,
    		'weight'            => false,
    		'dimensions'        => false,
    		'shipping-discount' => false,
    		'attributes'        => false,
    		'variations'        => false,
    		'price'             => false
    	);
    	
    	if ( isset( $form['template_data'] ) ) {
    		foreach ( $form['template_data'] as $item ) {
    			switch ( $item ) {
    				case 'categories': $fields_to_copy['categories'] = true;
    				break;
    				
    				case 'weight': $fields_to_copy['weight'] = true;
    				break;
    				
    				case 'dimensions': $fields_to_copy['dimensions'] = true;
    				break;
    				
    				case 'shipping-discount': $fields_to_copy['shipping-discount'] = true;
    				break;
    				
    				case 'attributes': $fields_to_copy['attributes'] = true;
    				break;
    				
    				case 'variations': $fields_to_copy['variations'] = true;
    				break;
    				
    				case 'price': $fields_to_copy['price'] = true;
    				break;
    			}
    		}
    	}
    	
    	return [
    		'product_id' => intval( $form['template_id'] ),
    		'product_data' => $fields_to_copy
    	];
    }
	
	public static function get_product_data( $product_id, $field_to_copy ) {
		/** @var WC_Product */
		$product = wc_get_product( $product_id );
		
		if ( ! $product instanceof \WC_Product ) {
			throw new \Exception(
				__( sprintf( 'Product %d not found.', $product_id ), WOO_BULKCOPY ) );
		}
		
		$temp_meta = get_post_meta( $product_id, 'shipping_discount' );
		$shipping_discount = count( $temp_meta ) > 0 ? $temp_meta[0] : null;
		
		/** @var \WP_Post[] */
		$variations_post = get_posts( array(
			'post_type' => 'product_variation',
			'post_parent' => $product_id
		) );
		
		$variations = array();
		foreach ( $variations_post as $variation_post ) {
			$variation_id = $variation_post->ID;
			$product_variation = new \WC_Product_Variation( $variation_id );
			$variations[ $variation_id ] = array(
				'regular_price' => $product_variation->get_regular_price(),
				'weight' => $product_variation->get_weight(),
				'dimensions' => $product_variation->get_dimensions( false )
			);
		}
		
		return [
			'categories' => wc_get_product_cat_ids( $product_id ),
			'weight' => $product->get_weight(),
			'dimensions' => $product->get_dimensions( false ),
			'shipping-discount' => $shipping_discount,
			'regular_price' => $product->get_regular_price(),
			'variations' => $variations,
			'attributes' => $product->get_attributes(),
		];
	}
	
	public static function process_page( $form ) {
		$template = self::get_template_form_data( $form );
		$product_id = $template['product_id'];
		
		$product_data = self::get_product_data( $product_id, $template['product_data'] );
		
		if ( is_numeric( $form['update_product_id'] ) ) {
			self::update_product( intval( $form['update_product_id'] ), $product_data );
		}
		elseif ( is_numeric( $form['update_category_id'] ) ) {
			self::update_products_from_category( intval( $form['update_category_id'] ), $product_data );
		}
		else {
			throw new \Exception( __( 'You must set a product or category to be updated.', WOO_BULKCOPY ) );
		}
	}
	
	public static function update_products_from_category( $category_id, $template_data ) {
		die('category');
	}
	
	public static function update_product( $product_id, $template_data ) {
		die('product');
	}
}