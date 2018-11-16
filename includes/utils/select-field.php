<?php
namespace WooBulkCopy\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SelectField extends Field {
	/** @var string[][] */
	private $_options;
	
	public function __construct( $id, $name, $options, $label, $description = '', $extra_description = [] ) {
		parent::__construct( $id, $name, $label, $description, $extra_description );
		$this->_options = $options;
	}
	
	public function create_field( $form_name ) {
		$html = sprintf(
			'<select id="%s" name="%s" class="regular-text">',
			$form_name . '-' . $this->get_id(),
			$form_name . '-' . $this->get_name() );
		
		foreach ( $this->_options as $option ) {
			$html .= sprintf(
				'<option value="%s">%s</option>',
				$option['value'],
				$option['name']);
		}
		
		$html .= '</select>';
		
		$html .= $this->create_description();
		return $html;
	}
}