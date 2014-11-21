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
	private $other   = array( 'separator', 'fieldset' );
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
			// check if this post has post meta or a taxonomy term already, if it does, set that as the value
			if ( ! in_array( $field['type'], $this->elements ) ) {
			    return  new WP_Error( 'invalid_type', "Invalid type {$field['type']} given for {$field['slug']} in this model. Acceptable elements: {$this->elements}");
			}
			$ID = get_the_ID();
			if ( isset($field['meta_key'] ) ) {
				$field['value'] = $this->default_value($ID, $field);
			} else {
			    $field['value'] = wp_get_object_terms(
					$ID,
					$taxonomy = $field['taxonomy'],
					array( 'fields' => 'names' )
			    );
			}
			if ( $field['type'] == 'fieldset' ) {
				// if our field is a fieldset, process defaults for each field that
				// in the group.
				if ( isset( $field['params']['is_formset_of_fieldsets'] ) ) {
					$field['init_num_forms'] = $this->formset_count( $field );
					$ready = $this->process_defaults_for_formset_of_fieldsets( $field );
				} else {
					$fields = $field['slug']['fields'];
					$i = 0;
					foreach ($field['fields'] as $f) {
						$field['fields'][$i] = $this->assign_defaults($f);
						$fieldset_slug = $field['meta_key'] . '_' . $f['meta_key'];
					 	$field['fields'][$i]['meta_key'] = $fieldset_slug;
					 	$f['meta_key'] = $field['fields'][$i]['meta_key'];
						$field['fields'][$i]['value'] = $this->default_value($ID, $f);
						$i++;
					}
					$ready[$field['slug']] = $field;
				}
			} else {
				$ready[$field['slug']] = $this->assign_defaults($field);
			}
		}
		return $ready;
	}

	public function process_defaults_for_formset_of_fieldsets( $field ) {
		$ID = get_the_ID();
		$ready = array();
		$key = $field['meta_key'];
		for ( $i = 0; $i < $field['params']['max_num_forms']; $i++ ) {
			$ready[$i] = $field;
			for ( $j = 0; $j < count( $field['fields'] ); $j++ ) {
				$meta_key = $ready[$i]['fields'][$j]['meta_key'];
				$ready[$i]['fields'][$j]['meta_key'] = "{$key}_{$meta_key}_{$i}";
				$ready[$i]['fields'][$j] = $this->assign_defaults( $ready[$i]['fields'][$j] );
				$ready[$i]['fields'][$j]['value'] = $this->default_value($ID, $ready[$i]['fields'][$j]);
			}
		}
		return $ready;
	}

	public function assign_defaults( $field ) {
		
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
		if ( $field['type'] == 'mutliselect' ) {
			$field['multiselect'] = true;
		} else {
			$field['multiselect'] = false;
		}
		
		if ( ! in_array($field['type'], array( 'taxonomyselect', 'tax_as_meta' ) ) ) {
			$field['taxonomy'] = false;
		}

		if ( $field['type'] == 'link' ) {
			// if there's existing post meta to go with, use that for the 
			// initial value
			$field['init_num_forms'] = $this->formset_count( $field );
		}
		return $field;
	}

	public function default_value( $ID, $field, $index = 0 ) {
		if ( $field['type'] == 'link' ) {
			$default = null;
		} else {
			$existing = get_post_meta( $ID, $field['meta_key'], false );
			$value = isset( $field['value'] ) ? $field['value'] : '';
			$default = array_key_exists( $index, $existing ) ? $existing[$index] : $value;
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
		$default = ! isset( $field['params']['rows'] ) ? 2 : intval( $field['params']['rows'] );
		return $default;
	}

	public function default_cols( $field ) {
		$default = ! isset( $field['params']['cols'] ) ? 27 : intval( $field['params']['cols'] );
		return $default;
	}
	public function default_options( $field ) {
	    $default = ! isset( $field['params']['include'] ) ? array() : $field['params']['include'];
		return $default;
	}

	public function formset_count( $field ) { 
		$init_num = array_key_exists( 'init_num_forms', $field['params'] ) ? $field['params']['init_num_forms'] : 1;
		$max_num  = isset($field['params']['max_num_forms']) ? $field['params']['max_num_forms'] : 1;
		$existing = array();
		$key = $field['meta_key'];
		for ($i=0; $i < $max_num; $i++) { 
			global $post;
			$meta = get_post_meta( $post->ID, $key = "{$key}_{$i}", $single = false );
			if ( isset( $meta ) ) {
				array_push($existing, $meta);
			}
		}
		$count = $max_num > count($existing) ? $max_num : count($existing);
		return $count;
	}

	public function ready_and_print_html( $post, $fields ) {
		$fields = $fields['args'];
		$ready  = $this->process_defaults( $fields );
		foreach ( $ready as $field ) {
			$this->HTML->draw( $field, $field['slug'] );
		}
	}
}
