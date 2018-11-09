<?php
namespace WooBulkCopy\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionsPageBuilder {
	
	/**
	 * Creates HTML of tabs for options page
	 * 
	 * @param Tab[] $tabs
	 * @return string HTML
	 */
	public static function create_tabs($tabs) {
		$html = '<nav class="nav-tab-wrapper">';
		
		foreach ( $tabs as $tab ) {
			$class = 'nav-tab' .  ( $tab->active ? ' nav-tab-active' : '' );
			$fragment = '<a class="%s" href="%s">%s</a>';
			$html .= sprintf( $fragment, $class, $tab->url, $tab->title );
		}
		
		$html .= '</nav>';
		return $html;
	}
	
	public static function show_subtitle( $title ) {
		?>
		<h2><?php echo $title; ?></h2>
		<?php
	}
	
	public static function show_title_description( $description ) {
		?>
		<em><?php echo $description; ?></em>
		<?php
	}
	
	public static function is_post_data( $form_name ) {
		return isset( $_POST[ $form_name ] );
	}
	
	public static function show_form_begin( $form_name ) {
		?>
		<form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="<?php echo $form_name; ?>" value="" />
		<?php
	}
	
	public static function show_form_end() {
		?>
		</form>
		<?php
	}
	
	public static function show_form_submit_button( $button_text ) {
		?>
		<p>
			<input type="submit" class="button button-primary" value="<?php echo $button_text; ?>" />
		</p>
		<?php
	}
	
	public static function show_page_header( $page_title ) {
		?>
		<div class="wrap">
		<h1><?php echo $page_title; ?></h1>
		<?php
	}
	
	public static function show_page_footer() {
		?>
		</div>
		<?php
	}
	
	/**
	 * Creates HTML of the fields
	 * 
	 * @param Field[] $fields
	 * @return string HTML
	 */
	public static function show_form_fields( $form_name, $fields = [] ) {
		?>
		<table class="form-table">
			<tbody>
				<?php foreach( $fields as $field ): ?>
				<tr>
					<th class="row"><?php echo $field->create_label( $form_name ); ?></th>
					<td><?php echo $field->create_field( $form_name ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
	
	public static function is_page( $page ) {
		$current_page = self::get_page();
		return $page === $current_page;
	}
	
	public static function get_form_data( $form_name ) {
		$form_data = array();
		$prefix = $form_name . '-';
		
		foreach ( $_POST as $key => $value ) {
			if ( substr( $key, 0, strlen( $prefix ) ) === $prefix ) {
				$form_data[ substr( $key, strlen( $prefix ) ) ] = $value;
			}
		}
		
		foreach ( $_FILES as $key => $value ) {
			if ( substr( $key, 0, strlen( $prefix ) ) === $prefix ) {
				$form_data[ substr( $key, strlen( $prefix ) ) ] = array(
					'name' => $value['name'],
					'type' => $value['type'],
					'content' => file_get_contents( $value['tmp_name'] )
				);
			}
		}
		
		return $form_data;
	}
	
	public static function show_admin_notice_updated( $message ) {
		?>
    	<div class="notice updated is-dismissible">
    		<p><?php echo $message; ?></p>
    	</div>
    	<?php
    }
    
    public static function show_admin_notice_error( $message ) {
    	?>
    	<div class="notice error is-dismissible">
    		<p><?php echo $message; ?></p>
    	</div>
    	<?php
    }
}