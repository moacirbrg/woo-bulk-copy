<?php
namespace WooBulkCopy;

use WooBulkCopy\Utils\OptionsPageBuilder as Builder;
use WooBulkCopy\Utils\CheckboxField;
use WooBulkCopy\Utils\NumberField;
use WooBulkCopy\Utils\SelectField;
use WooBulkCopy\Utils\PluginsCompatibility as Plugins;

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
				'copy-woo-shipping-discount',
				'template_data[]',
				'woo-shipping-discount',
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
				'copy-regular-price',
				'template_data[]',
				'regular-price',
				__( 'Regular price', WOO_BULKCOPY ) ),
			new CheckboxField(
				'copy-sale-price',
				'template_data[]',
				'sale-price',
				__( 'Sale price', WOO_BULKCOPY ) )
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
			new CheckboxField(
				'all-products',
				'all-products',
				'true',
				__( 'All products', WOO_BULKCOPY ) )
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
    	
    	$data_to_copy = array(
    		'categories'            => false,
    		'weight'                => false,
    		'dimensions'            => false,
    		'woo-shipping-discount' => false,
    		'attributes'            => false,
    		'variations'            => false,
    		'regular-price'         => false,
    		'sale-price'            => false
    	);
    	
    	if ( isset( $form['template_data'] ) ) {
    		foreach ( $form['template_data'] as $item ) {
    			switch ( $item ) {
    				case 'categories': $data_to_copy['categories'] = true;
    				break;
    				
    				case 'weight': $data_to_copy['weight'] = true;
    				break;
    				
    				case 'dimensions': $data_to_copy['dimensions'] = true;
    				break;
    				
    				case 'woo-shipping-discount': $data_to_copy['woo-shipping-discount'] = true;
    				break;
    				
    				case 'attributes': $data_to_copy['attributes'] = true;
    				break;
    				
    				case 'variations': $data_to_copy['variations'] = true;
    				break;
    				
    				case 'regular-price': $data_to_copy['regular-price'] = true;
    				break;
    				
    				case 'sale-price': $data_to_copy['sale-price'] = true;
    				break;
    			}
    		}
    	}
    	
    	return [
    		'product_id' => intval( $form['template_id'] ),
    		'data_to_copy' => $data_to_copy
    	];
    }
	
    /**
     * @param int $product_id
     * @throws \Exception
     * @return \WC_Product
     */
    public static function get_product( $product_id ) {
    	$product = wc_get_product( $product_id );
    	
    	if ( ! $product instanceof \WC_Product ) {
    		throw new \Exception(
    			__( sprintf( 'Product %d not found.', $product_id ), WOO_BULKCOPY ) );
    	}
    	
    	return $product;
    }
    
	public static function get_product_data( $product_id ) {
		$product = self::get_product( $product_id );
		
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
		
		$yoast_primary_cat = Plugins::yoast_is_enabled() ? Plugins::yoast_get_primary_cat( $product_id ) : null;
		$woo_shipping_discount = Plugins::woo_shipping_discount_is_enabled() ? Plugins::woo_shipping_discount_get_discount( $product_id ) : null;
		
		return [
			'categories' => wc_get_product_cat_ids( $product_id ),
			'yoast-primary-cat' => $yoast_primary_cat, 
			'weight' => $product->get_weight(),
			'dimensions' => $product->get_dimensions( false ),
			'woo-shipping-discount' => $woo_shipping_discount,
			'regular_price' => $product->get_regular_price(),
			'sale_price' => $product->get_sale_price(),
			'variations' => $variations,
			'attributes' => $product->get_attributes(),
		];
	}
	
	public static function process_page( $form ) {
		$template = self::get_template_form_data( $form );
		$product_id = $template['product_id'];
		$data_to_copy = $template['data_to_copy'];
		
		$product_data = self::get_product_data( $product_id );
		
		if ( is_numeric( $form['update_product_id'] ) ) {
			self::update_product( intval( $form['update_product_id'] ), $product_data, $data_to_copy );
		}
		elseif ( is_numeric( $form['update_category_id'] ) ) {
			self::update_products_from_category( intval( $form['update_category_id'] ), $product_data, $data_to_copy );
		}
		elseif ( $form['all-products'] === 'true' ) {
			self::update_all_products( $product_data, $data_to_copy );
		}
		else {
			throw new \Exception( __( 'You must set a product or category to be updated.', WOO_BULKCOPY ) );
		}
	}
	
	public static function update_all_products( $template_data, $data_to_copy ) {
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => 100000
		);
		
		$query = new \WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			self::update_product( $query->post->ID, $template_data, $data_to_copy );
		}
	}
	
	public static function update_products_from_category( $category_id, $template_data, $data_to_copy ) {
		$args = array(
			'post_type' => 'product',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $category_id
				),
			),
			'posts_per_page' => 100000
		);
		
		$query = new \WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			self::update_product( $query->post->ID, $template_data, $data_to_copy );
		}
	}
	
	public static function update_product( $product_id, $template_data, $data_to_copy ) {
		/** @var \WC_Product */
		$product = self::get_product( $product_id );
		
		if ( $data_to_copy['categories'] === true ) {
			wp_set_object_terms( $product_id , $template_data['categories'], 'product_cat' );
			
			if ( Plugins::yoast_is_enabled() ) {
				Plugins::yoast_set_primary_cat( $product_id, $template_data['yoast-primary-cat'] );
			}
		}
		
		if ( $data_to_copy['weight'] === true ) {
			$product->set_weight( $template_data['weight'] );
		}
		
		if ( $data_to_copy['dimensions'] === true ) {
			$product->set_width( $template_data['dimensions']['width'] );
			$product->set_height( $template_data['dimensions']['height'] );
			$product->set_length( $template_data['dimensions']['length'] );
		}
		
		if ( $data_to_copy['woo-shipping-discount'] === true ) {
			Plugins::woo_shipping_discount_set_discount( $product_id, $template_data['woo-shipping-discount'] );
		}
		
		if ( $data_to_copy['attributes'] === true ) {
			$product->set_attributes( $template_data['attributes'] );
		}
		
		if ( $data_to_copy['variations'] === true ) {
			/** @var \WP_Post[] */
			$variations_to_delete = get_posts( array(
				'post_type' => 'product_variation',
				'post_parent' => $product_id
			) );
			
			foreach ( $variations_to_delete as $variation_to_delete ) {
				wp_delete_post( $variation_to_delete->ID );
			}
			
			foreach ( $template_data['variations'] as $template_variation ) {
				$variation_id = wp_insert_post( array(
					'post_title'   => 'Product #' . $product_id . ' Variation',
					'post_content' => '',
					'post_status'  => 'publish',
					'post_parent'  => $product_id,
					'post_type'    => 'product_variation'
				) );
				
				$variation = new \WC_Product_Variation( $variation_id );
				$variation->set_regular_price( $template_variation['regular_price'] );
				$variation->set_weight( $template_variation['weight'] );
				$variation->set_width( $template_variation['dimensions']['width'] );
				$variation->set_height( $template_variation['dimensions']['height'] );
				$variation->set_length( $template_variation['dimensions']['length'] );
				$variation->save();
			}
		}
		
		if ( $data_to_copy['regular-price'] === true ) {
			$product->set_regular_price( $template_data['regular_price'] );
		}
		
		if ( $data_to_copy['sale-price'] === true ) {
			$product->set_sale_price( $template_data['sale_price'] );
		}
		
		$product->save();
	}
}