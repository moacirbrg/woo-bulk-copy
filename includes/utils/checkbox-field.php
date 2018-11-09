<?php
namespace WooBulkCopy\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CheckboxField extends Field {
	/** @var string */
	private $_value;
	
	public function __construct( $id, $name, $value, $label, $description = '', $extra_description = [] ) {
		parent::__construct( $id, $name, $label, $description, $extra_description );
		$this->_value = $value;
	}
	
	/**
	 * Gets checkbox value
	 * 
	 * @return string
	 */
	public function get_value() {
		return $this->_value;
	}
	
	public function create_field( $form_name ) {
		$html = sprintf(
			'<input id="%s" name="%s" value="%s" type="checkbox" />',
			$form_name . '-' . $this->get_id(),
			$form_name . '-' . $this->get_name(),
			$this->get_value() );
		
		$html .= $this->create_description();
		return $html;
	}
}