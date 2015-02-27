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
	private $other   = array( 'separator', 'fieldset', 'formset' );
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
			if ( $field['type'] == 'formset' ) {
				$this->process_formset_defaults( $field, $ready );
			} elseif ( $field['type'] == 'fieldset' ) {
				for ($i = 0; $i < count( $field['fields'] ); $i++ ) {
					$field['fields'][$i]['meta_key'] = "{$field['meta_key']}_{$field['fields'][$i]['meta_key']}";
					$field['fields'][$i] = $this->assign_defaults( $field['fields'][$i] );
					$field['fields'][$i]['value'] = $this->default_value( $ID, $field['fields'][$i] );
				}
				$ready[$field['meta_key']] = $field;
			} else {
				if ( isset( $field['meta_key'] ) ) {
					$ready[$field['meta_key']] = $this->assign_defaults($field);
				} elseif ( isset( $field['slug'] ) ) {                  
					$ready[$field['slug']] = $this->assign_defaults($field);
				}
			}
		}
		return $ready;
	}

	public function process_formset_defaults( $field, &$ready ) {
		$ID = get_the_ID();
		$processed = array();
		$key = $field['meta_key'];
		for ( $i = 0; $i < $field['params']['max_num_forms']; $i++ ) {
			$processed[$i] = $field;
			if ( ( $i + 1 ) <= $field['params']['init_num_forms'] ) {
				$processed[$i]['init'] = true;
			}
			$processed[$i]['meta_key'] .= '_' . $i;
			$processed[$i]['slug'] .= '_' . $i;
			$processed[$i]['title'] .= isset( $processed[$i]['title'] ) ? ' ' . ( $i + 1 ) : "";
			for ( $j = 0; $j < count( $field['fields'] ); $j++ ) {
				$meta_key = $processed[$i]['fields'][$j]['meta_key'];
				$processed[$i]['fields'][$j]['meta_key'] = "{$processed[$i]['meta_key']}_{$meta_key}";
			}
			$processed[$i]['fields'] = $this->process_defaults( $processed[$i]['fields'] );
		}
		foreach ( $processed as $f ) {
			$ready[$f['meta_key']] = $f;
		}
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
		if ( $field['type'] == 'multiselect' ) {
			$field['multiselect'] = true;
		} else {
			$field['multiselect'] = false;
		}
		if ( ! in_array($field['type'], array( 'taxonomyselect', 'tax_as_meta', 'date' ) ) ) {
			$field['taxonomy'] = false;
		}
		if ( $field['type'] == 'wysiwyg' ) {
			if ( ! isset( $field['params'] ) or empty( $field['params'] ) ) {
				$field['params'] = array( 'textarea_rows' => 5, 'editor_class' => "cms-toolkit-wysiwyg" );
			} else {
				$field['params']['editor_class'] .= " cms-toolkit-wysiwyg";
			}
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

	public function ready_and_print_html( $post, $fields ) {
		$fields = $fields['args'];
		$ready  = $this->process_defaults( $fields );
		foreach ( $ready as $field ) {
			$this->HTML->draw( $field );
		}
	}
}
