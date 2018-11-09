<?php
namespace WooBulkCopy\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Field {
	/** @var string */
	private $_id;
	
	/** @var string */
	private $_name;
	
	/** @var string */
	private $_label;
	
	/** @var string */
	private $_description;
	
	/** @var string[] */
	private $_extra_description;
	
	/**
	 * @param string $id
	 * @param string $name
	 * @param string $form_name
	 */
	public function __construct( $id, $name, $label, $description = '', $extra_description = [] ) {
		$this->_id = $id;
		$this->_name = $name;
		$this->_label = $label;
		$this->_description = $description;
		$this->_extra_description = $extra_description;
	}
	
	/**
	 * Field ID
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->_id;
	}
	
	/**
	 * Field name
	 * 
	 * @return string
	 */
	public function get_name() {
		return $this->_name;
	}
	
	/**
	 * User friendly name of the field
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->_label;
	}
	
	/**
	 * Field description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->_description;
	}
	
	/**
	 * Extra field description to be displayed after main description
	 *
	 * @return string[]
	 */
	public function get_extra_description() {
		return $this->_extra_description;
	}
	
	public function create_description() {
		if ( empty( $this->get_description() ) ) {
			return '';
		}
		
		$description = sprintf( '<p class="description">%s</p>', $this->get_description() );
		
		foreach( $this->_extra_description as $extra_description ) {
			$description .= "<p>{$extra_description}</p>";
		}
		
		return $description;
	}
	
	public function create_label( $form_name ) {
		return sprintf( '<label for="%s">%s</label>', $form_name . '-' . $this->get_id(), $this->get_label() );
	}
	
	/**
	 * @param string $id
	 * @return string HTML of the field
	 */
	abstract function create_field( $id );
}