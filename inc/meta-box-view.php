<?php
namespace CFPB\Utils\MetaBox;
use \CFPB\Utils\MetaBox\HTML;
use \CFPB\Utils\MetaBox\Models;
use \WP_Error;

class View {
	private $selects = array(
		'select',
		'multiselect',
		'taxonomyselect',
		'tax_as_meta',
		'post_select',
		'post_multiselect',
	);
	private $inputs  = array(
		'text_area',
		'wysiwyg',
		'number',
		'text',
		'boolean',
		'email',
		'url',
		'date',
		'time',
		'datetime',
		'radio',
		'link',
	);
	private $hidden  = array( 'nonce', 'hidden' );
	private $other   = array( 'separator', 'fieldset', 'file' );
	public $elements;
	private $HTML;
	private $error;

	function __construct() {
		$this->HTML     = new HTML();
		$this->elements = array_merge(
			$this->selects,
			$this->inputs,
			$this->hidden,
			$this->other
		);
		$this->error = '\WP_Error';
	}

	public function replace_html( $HTML ) {
		$this->HTML = $HTML;
	}

	public function error_handler( $Class ) {
		$this->error = $Class;
	}

	public function process_defaults( $ID, &$field, $saved ) {
		// check if this post has post meta or a taxonomy term already, if it does, set that as the value
		if ( ! in_array( $field['type'], $this->elements ) ) {
			wp_die("Invalid type {$field['type']} given for {$field['key']} in this model.");
		}
		if ( isset( $field['taxonomy'] ) ) {
			$field['value'] = wp_get_object_terms(
				$ID,
				$taxonomy = $field['taxonomy'],
				array( 'fields' => 'names' )
			);
		}
		if ( isset( $field['params'] ) and isset( $field['params']['repeated'] ) ) {
			$this->process_repeated_fields( $ID, $field, $saved );
		} elseif ( $field['type'] == 'fieldset' ) {
			$this->process_fieldset( $ID, $field, $saved );
		} else {
			$this->assign_defaults( $ID, $field, $saved );
		}
	}

	public function process_repeated_fields( $ID, &$field, $saved ) {
		$this->process_repeated_field_params( $field );
		$repeated = $field['params']['repeated'];
		unset( $field['params']['repeated'] );
		if ( isset( $field['howto'] ) ) {
			$howto = $field['howto'];
			unset( $field['howto'] );
		}
		$processed = array();
		for ( $i = 0; $i < $repeated['max']; $i++ ) {
			$processed[$i] = $field;
			$processed[$i]['init'] = ( ( $i + 1 ) <= $repeated['min'] );
			$processed[$i]['key'] .= '_' . $i;
			// $processed[$i]['title'] .= isset( $processed[$i]['title'] ) ? ' ' . ( $i + 1 ) : "";
			// $processed[$i]['label'] .= isset( $processed[$i]['label'] ) ? ' ' . ( $i + 1 ) : "";
			$saved_field = ( $saved and isset( $saved[$i] ) ) ? $saved[$i] : null;
			$this->process_defaults( $ID, $processed[$i], $saved_field );
		}
		$field['fields'] = $processed;
		$field['params']['repeated'] = $repeated;
		$field['howto'] = ( isset( $howto ) ) ? $howto: "";
	}

	public function process_repeated_field_params( $field ) {
		if ( is_array( $field['params']['repeated'] ) and !empty( $field['params']['repeated'] ) ) {
			foreach ( array( 'min', 'max' ) as $limit ) {
				if ( ! isset( $field['params']['repeated'][$limit] ) or ! is_numeric( $field['params']['repeated'][$limit] ) ) {
					wp_die("{$field['key']} must have repeated param {$limit} set as a number.");
				}
			}
			if ( intval( $field['params']['repeated']['min'] ) > intval( $field['params']['repeated']['max'] ) ) {
				wp_die("{$field['key']} repeated param min must not be more than the repeated max param.");
			}
		} else {
			wp_die("{$field['key']} must have repeated param be array and set min and max params.");
		}
	}

	public function process_fieldset( $ID, &$field, $saved ) {
		if ( ! isset( $field['fields'] ) or ! is_array( $field['fields'] ) ) {
			wp_die("{$field['old_key']} must have a fields array.");
		}
		foreach ( array_keys( $field['fields'] ) as $key ) {
			$field['fields'][$key]['old_key'] = Models::validate_keys( $field['fields'][$key] );
			$field['fields'][$key]['key'] = "{$field['key']}_{$field['fields'][$key]['old_key']}";
			$saved_field = ( $saved and isset( $saved[$field['fields'][$key]['old_key']] ) ) ? $saved[$field['fields'][$key]['old_key']] : null;
			$this->process_defaults( $ID, $field['fields'][$key], $saved_field );
		}
	}

	public function assign_defaults( $ID, &$field, $saved ) {
		$field['label'] = isset( $field['label'] ) ? $field['label'] : null;
		if ( $field['type'] == 'text' or $field['type'] == 'text_area' ) {
			$field['max_length'] = isset( $field['max_length'] ) ? $field['max_length'] : 255;
			$field['placeholder'] = isset( $field['placeholder'] ) ? $field['placeholder'] : "";
		}
		if ( $field['type'] == 'text_area') {
			$field['rows'] = isset( $field['params']['rows'] ) ? intval( $field['params']['rows'] ) : 2;
			$field['cols'] = isset( $field['params']['cols'] ) ? intval( $field['params']['cols'] ) : 27;
		}
		if ( $field['type'] == 'tax_as_meta') {
			$field['include'] = isset( $field['params']['include'] ) ? $field['params']['include'] : array();
			unset($field['params']);
		}
		if ( in_array($field['type'], $this->selects ) ) {
			$field['multiselect'] = ( $field['type'] == 'multiselect' ) ? true : false;
		}
		if ( ! in_array($field['type'], array( 'taxonomyselect', 'tax_as_meta', 'date', 'time', 'datetime' ) ) ) {
			$field['taxonomy'] = false;
		}
		if ( $field['type'] == 'wysiwyg' ) {
			if ( ! isset( $field['settings'] ) or empty( $field['settings'] ) ) {
				$field['settings'] = array(
					'textarea_rows' => 8,
					'editor_class' => "cms-toolkit-wysiwyg",
					'wpautop' => false
				);
			} else {
				if ( ! isset( $field['settings']['textarea_rows'] ) ) {
					$field['settings']['textarea_rows'] = 8;
				}
				if ( ! isset( $field['settings']['editor_class'] ) ) {
					$field['settings']['editor_class'] = "cms-toolkit-wysiwyg";
				} else {
					$field['settings']['editor_class'] .= " cms-toolkit-wysiwyg";
				}
				if ( ! isset( $field['settings']['wpautop'] ) ){
					$field['settings']['wpautop'] = false;
				}
			}
		}
		// the else is added for backwards compatibility and is to be phased out
		if ( $saved or $saved === "" ) {
			$field['value'] = $saved;
		} else {
			$existing = get_post_meta( $ID, $field['key'], false );
			$field['value'] = isset( $existing[0] ) ? $existing[0] : null;
		}
	}

	public function ready_and_print_html( $post, $fields ) {
		$ID = get_the_ID();
		$fields = $fields['args'];
		$saved = array();
		foreach ( $fields as $field ) {
			$field['old_key'] = Models::validate_keys( $field );
			$field['key'] = $field['old_key'];
			$saved[$field['old_key']] = get_post_meta( $ID, $field['old_key'], $single = true );
			if ( in_array( $field['type'], array( 'link', 'multiselect', 'post_multiselect' ) ) ) {
				$existing = get_post_meta( $ID, $field['key'], false );
				if ( $existing and ! is_array( $existing[0] ) ) {
					$saved[$field['old_key']] = $existing;
				}
			}
			$this->process_defaults( $ID, $field, $saved[$field['old_key']] );
			$this->HTML->draw( $field );
		}
	}
}
