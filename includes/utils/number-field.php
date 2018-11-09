<?php
namespace WooBulkCopy\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class NumberField extends Field {
	public function create_field( $form_name ) {
		$html = sprintf(
			'<input id="%s" name="%s" type="number" class="regular-text" />',
			$form_name . '-' . $this->get_id(),
			$form_name . '-' . $this->get_name() );
		
		$html .= $this->create_description();
		return $html;
	}
}