<?php
namespace CFPB\Utils\MetaBox;
use \CFPB\Utils\MetaBox\HTML;
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
	    'number',
	    'text',
	    'boolean',
	    'email',
	    'url',
	    'date',
	    'radio',
		'link',
	);
	private $hidden  = array( 'nonce', 'hidden' );
	private $other   = array( 'separator' );
	public $elements;
	private $HTML;

	function __construct() {
	    $this->HTML     = new HTML();
	    $this->elements = array_merge(
			$this->selects,
	        $this->inputs,
	        $this->hidden,
	        $this->other
	    );
	}

	public function replace_html( $HTML ) {
		$this->HTML = $HTML;
	}

	public function process_defaults( $fields ) {
		$ready = array();
		foreach ( $fields as $field ) {
			if ( ! in_array( $field['type'], $this->elements ) ) {
			    return  new WP_Error( 'invalid_type', "Invalid type {$field['type']} given for {$field['slug']} in this model. Acceptable elements: {$this->elements}");
			} else {
				// check if this post has post meta or a taxonomy term already, if it does, set that as the value
				if ( isset($field['meta_key'] ) ) {
					$ID = get_the_ID();
					$field['value'] = $this->default_value($ID, $field);
				} else {
				    $ID = get_the_ID();
				    $field['value'] = wp_get_object_terms(
						$ID,
						$taxonomy = $field['taxonomy'],
						array( 'fields' => 'names' )
				    );
				}
				$field['label'] = $this->default_label($field);
				if ( ! in_array( $field['type'], $this->hidden ) ) {
					$field['max_length'] = $this->default_max_length($field);
					$field['placeholder'] = $this->default_placeholder($field);
				}
				if ( $field['type'] == 'text_area') {
					$field['rows'] = $this->default_rows($field);
					$field['cols'] = $this->default_cols($field);
				}
				if ( $field['type'] == 'tax_as_meta') {
					$field['include'] = $this->default_options( $field );
					unset($field['params']);
				}
			}
			$ready[$field['slug']] = $field;
		}
		return $ready;
	}

	public function default_value( $ID, $field ) {
		if ( $field['type'] == 'link' ) {
			$default = null;
		} else {
			$existing = get_post_meta( $ID, $field['meta_key'], true );
			$value = isset( $field['value']) ? $field['value'] : '';
			$default = $existing ? $existing : $value;
		}
		return $default;
	}

	public function default_label( $field ) {
		$default = empty( $field['label'] ) ? '' : $field['label'];
		return $default;
	}

	public function default_max_length( $field ) {
		$default  = empty( $field['max_length'] ) ? 255 : $field['max_length'];
		return $default;
	}

	public function default_placeholder( $field ) {
		$default = empty( $field['placeholder'] ) ? '' : $field['placeholder'];
		return $default;
	}

	public function default_rows( $field ) {
		extract($field);
		$default = ! isset( $params['rows'] ) ? 2 : intval( $params['rows'] );
		return $default;
	}

	public function default_cols( $field ) {
		extract($field);
		$default = ! isset( $params['cols'] ) ? 27 : intval( $params['cols'] );
		return $default;
	}
	public function default_options( $field ) {
	    extract($field);
	    $default = ! isset( $params['include'] ) ? array() : $params['include'];
		return $default;
	}

	public function ready_and_print_html( $post, $fields ) {
		$fields = $fields['args'];
		$ready  = $this->process_defaults( $fields );
		foreach ( $ready as $field ) {
			$this->HTML->draw( $field );
		}
	}
}
