<?php
namespace CFPB\Utils\MetaBox;
use \DateTime;
use \DateTimeZone;
use \WP_Error;
class HTML {

	private $elements = array(
		'selects' => array( 'select', 'multiselect', 'taxonomyselect', 'tax_as_meta', 'post_select', 'post_multiselect' ),
		'inputs' => array( 'text_area', 'number', 'text', 'boolean', 'email', 'url', 'date', 'time', 'datetime', 'radio', 'link', 'wysiwyg', 'file' ),
		'hidden' => array( 'nonce', 'hidden', 'separator', 'fieldset' ),
		);

	public function draw( $field, $set_id = NULL ) {
		if ( empty( $field ) ) {
			wp_die( 'field_required', 'You need to pass a field array to this method. You passed a '. gettype( $field ) . ' .');
		}
		?><div class="cms-toolkit-wrapper<?php if (isset( $field['class'] ) ) echo ' ' . esc_attr( $field['class'] ); ?>"><?php
		if ( !isset( $field['params']['repeated'] ) and isset( $field['title'] ) ) {
			?><h4 id="<?php echo "{$field['key']}"; ?>" ><?php
				echo "{$field['title']}"; 
			?></h4><?php
		}
		if ( isset( $field['params']['repeated'] ) ) {
			$this->draw_repeated_fields( $field );
		} else {
			if ( $field['type'] == 'fieldset' ) {
			?><fieldset><?php
				foreach ($field['fields'] as $f) {
					$this->draw( $f, $set_id );
				}
			?></fieldset><?php				
			} elseif ( in_array( $field['type'], $this->elements['inputs'] ) ) {
			$this->draw_input( $field, $set_id );
			} elseif ( in_array( $field['type'], $this->elements['selects'] ) ) {
				$this->pass_select( $field, $set_id );
			} elseif ( $field['type'] == 'hidden' ) {
				$this->hidden( $field['key'], $field['value'], $set_id );
			} elseif ( $field['type'] == 'nonce' ) {
				wp_nonce_field( plugin_basename( __FILE__ ), $field['key'] );
			}
			if ( isset( $field['howto'] ) and $field['type'] != 'fieldset' ) {
				?><p class="howto"><?php echo esc_attr( $field['howto'] );?></p><?php
			}
		}
		?></div><?php
	}

	public function draw_repeated_fields( $field ) {
		$post_id = get_the_ID();
		?><div id="<?php echo "{$field['key']}"; ?>" class="form"><?php
			?><h2 id="<?php echo "{$field['key']}-header"; ?>" class="form-header"><?php
				echo isset( $field['plural'] ) ? $field['plural'] :
						( isset( $field['title'] ) ? $field['title'] :
							( isset( $field['label'] ) ? $field['label'] :
								"Create a plural/title/label." ) );
			?></h2><?php
			if ( isset( $field['howto'] ) ) {
				?><p class="howto"><?php echo esc_attr( $field['howto'] ); ?></p><?php
			}
			foreach ( $field['fields'] as $f ) {
			?><div class="cms-toolkit-wrapper set <?php if ( isset( $field['class'] ) ) echo  ' ' . esc_attr( $field['class'] ); ?>"><?php
				$set_id = $this->get_set_id( $f['key'] );
				$existing = $this->get_existing_data( $f );
				if ( isset( $f['title'] ) and !empty( $f['title'] ) ) {
					?><h3 id="<?php echo "{$f['key']}-header"; ?>" class="set-header"><?php
						echo esc_attr( $f['title'] );
				} elseif ( isset( $f['label'] ) and !empty( $f['label'] ) ) {
					?><label id="<?php echo "{$f['key']}-header"; ?>" class="cms-toolkit-file block-label set-header ?>"><?php
						echo esc_attr( $f['label'] );
				}
					?><a class="toggle-repeated-field<?php if ( $existing or $f['init'] ) echo " hidden"; ?>"
						 data-term="<?php echo $f['key']; ?>" data-action-term="add" data-term-id="<?php echo $set_id; ?>">
						&nbsp;<span class="dashicons dashicons-plus-alt"></span><?php
					?></a><?php
					?><a class="toggle-repeated-field<?php if ( ! $existing and ! $f['init'] ) echo " hidden"; ?>"
						 data-term="<?php echo $f['key']; ?>" data-action-term="remove" data-term-id="<?php echo $set_id; ?>">
						&nbsp;<span class="dashicons dashicons-dismiss"></span><?php
					?></a><?php
				if ( isset( $f['title'] ) and !empty( $f['title'] ) ) {
					?></h3><?php
					unset( $f['title'] );
				} elseif ( isset( $f['label'] ) and !empty( $f['label'] ) ) {
					?></label><?php
					unset( $f['label'] );
				}
				?><div id="<?php echo "{$f['key']}-set"; ?>" class=<?php
				  echo ( ! $existing and ! $f['init'] ) ? '"hidden new" disabled' : '"expanded"';?>><?php
					$this->draw( $f, $set_id );
				?></div><?php
			?></div><?php
			}
		?></div><?php
	}

	public function get_set_id( $set_key ) {
		$id = "";
		$key_parts = explode( '_', $set_key );
		foreach ( $key_parts as $part ) {
			if ( ctype_digit( $part ) ) {
				$id .= $part . "-";
			}
		}
		if ( ! empty( $id ) ) {
			$id = substr( $id, 0, -1 );
		}
		return $id;
	}

	public function get_existing_data( $field ) {
		if ( isset( $field['params']['repeated'] ) ) {
			foreach ( $field['fields'] as $f ) {
				if ( $this->get_existing_data( $f ) ) {
					return true;
				}
			}
		} elseif ( $field['type'] == 'fieldset' ) {
			foreach ( $field['fields'] as $f ) {
				if ( $this->get_existing_data( $f ) ) {
					return true;
				}
			}
		} else {
			if ( isset( $field['value'] ) and !empty( $field['value'] ) ) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function pass_select( $field, $set_id = NULL ) {
		$value    = $field['value'];
		$taxonomy = isset( $field['taxonomy'] ) ? $field['taxonomy'] : false;
		$required = isset( $field['required'] ) ? $field['required'] : false;
		$multiple = isset( $field['multiple'] ) ? $field['multiple'] : false;
		$label    = isset( $field['label'] )    ? $field['label']    : null;
		$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : null;
		if ( in_array( $field['type'], array('multiselect', 'select', 'taxonomyselect' ) ) ) {
			$this->select( 
				$field['key'], 
				$field['params'], 
				$taxonomy, 
				$multiple,
				$value,
				$required,
				$placeholder,
				$label,
				$set_id
			);
		} elseif ( $field['type'] == 'tax_as_meta' ) {
			$this->taxonomy_as_meta(
				$slug = $field['key'],
				$params = $field['include'],
				$taxonomy = $taxonomy,
				$value,
				$multiple,
				$required,
				$label,
				$placeholder,
				$set_id
			);
		} elseif ( $field['type'] == 'post_select' or $field['type'] == 'post_multiselect' ) {
			$post_id = get_the_ID();
			$settings = $field['params'];
			$posts = get_posts($settings);
			$multiple = $field['type'] == 'post_multiselect' ? 'multiple' : null;
			$this->post_select(
				$field['key'],
				$posts,
				$value,
				$multiple,
				$required,
				$label,
				$placeholder,
				$set_id
			);
		}
	}

	public function draw_input( $field, $set_id = NULL ) {
		$required   = isset( $field['required'] )   ? $field['required']   : false;
		$value      = isset( $field['value'] )      ? $field['value']      : null;
		$label      = isset( $field['label'] )      ? $field['label']      : null;
		$max_length = isset( $field['max_length'] ) ? $field['max_length'] : null;
		$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : null;
		if ( $field['type'] == 'text_area' ) {
			$this->text_area( $field['key'], $value, $required, $field['rows'], $field['cols'], $label, $placeholder, $set_id );
		}

		if ( $field['type'] == 'wysiwyg' ) {
			$this->wysiwyg( $value, $field['key'], $field['settings'], $label, $set_id );
		}

		if ( in_array( $field['type'], array( 'number', 'text', 'email', 'url' ) ) ) {
			$this->single_input( $field['key'], $value, $field['type'], $required, $max_length, $label, $placeholder, $set_id );
		}

		if ( $field['type'] == 'date' ) {
			$value = ( $field['key'] == $field['taxonomy'] ) ? null : $value;
			$this->date( $field['key'], $value, $required, $label, $set_id );
			$this->displayTags( $field );
		} elseif ( $field['type'] == 'time' ) {
			$value = ( $field['key'] == $field['taxonomy'] ) ? null : $value;
			$this->time( $field['key'], $value, $required, $label, $set_id );
			$this->displayTags( $field );
		} elseif ( $field['type'] == 'datetime' ) {
			$value = ( $field['key'] == $field['taxonomy'] ) ? null : $value;
			$this->datetime( $field['key'], $value, $required, $label, $set_id );
			$this->displayTags( $field );
		}

		if ( $field['type'] == 'radio' ) {
			$this->single_input( $field['key'], $value = 'true', $field['type'] = 'radio', $required, $max_length = null, $label, $placeholder, $set_id );
			$this->single_input( $field['key'], $value = 'false', $field['type'] = 'radio', $required, $max_length = null,  $label, $placeholder, $set_id );
		}

		if ( $field['type'] == 'boolean' ) {
			$this->boolean_input( $field['key'], $value, $required, $label, $set_id );
		}

		if ( $field['type'] == 'link' ) {
			$this->link_input($field['key'], $value, $required, $label, $set_id );
		}

		if ( $field['type'] == 'file' ) {
			$this->file_input( $field['key'], $value, $label, $required, $set_id );
		}
	}

/**
	 * Generate a <textarea> field
	 *
	 * Generates a textarea HTML field using defined parameters A public
	 * function, this method may only be called from within this class.
	 *
	 * All parameters are required
	 * @param array $field unused, eliminate
	 * @param int $rows value for the rows attribute
	 * @param int $cols value for the cols attribute
	 * @param str $key value for the 'id' and 'name' attributes
	 * @param str $value a default value for the <textarea>
	 *
	**/
	public function text_area( $key, $value, $required, $rows, $cols, $label, $placeholder, $set_id = NULL ) {
		if ( $label ) {
			?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $key ) ?>"><?php 
				echo esc_attr( $label ); if ( $required ): echo ' (required)'; endif; 
			?></label><?php
		}
		?><textarea id="<?php echo esc_attr( $key ) 
				  ?>" class="cms-toolkit-textarea <?php echo "set-input_{$set_id}"; 
				  ?>" name="<?php echo esc_attr( $key ) 
				  ?>" rows="<?php echo esc_attr( $rows ) 
				  ?>" cols="<?php echo esc_attr( $cols ) 
				  ?>" value="<?php echo esc_attr( $value ) 
				  ?>" placeholder="<?php echo esc_attr( $placeholder ) ?>"<?php
				   if ( $required ): echo ' required'; endif; ?>><?php echo esc_attr( $value ) 
		?></textarea><?php
	}

	/**
	 * Generate a wysiwyg field
	 *
	 * Uses the built in Wordpress function wp_editor to generate the field.
	 *
	 * @param str $value is the text within the editor that has been saved
	 * @param str $key the id associated with the HTML tag and data
	 * @param str $params are the settings for the wp_editor function
	 * @param str $set_id the numeric id for a formset that the field could be in
	 *
	**/
	public function wysiwyg( $value, $key, $settings, $label, $set_id = NULL ) {
		if ( isset( $set_id ) ) {
			$settings['editor_class'] .= " set-input_{$set_id}";
		}
		if ( $label ) {
			?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $key ) ?>"><?php echo esc_attr( $label ) ?></label><?php
		}
		wp_editor( $value, $key, $settings );
	}

	/**
	 * Generates a single input field
	 *
	 * A single <input> is generated based on defined parameters.
	 *
	 * @param str     $key         the key for this field, used as 'name' and 'id'
	 * @param str     $value   	   value of the field
	 * @param str     $type        the type of input field, use any valid HTML input type
	 * @param boolean $required    boolean to apply required browser validation
	 * @param int 	  $max_length  the maxlength attribute for number or text inputs
	 * @param str 	  $placeholder a default placeholder value
	 * @param int 	  $set_id      id of the set if it is or is in a repeated field
	 *
	 * @since 1.0
	 *
	**/
	public function single_input( $key, $value, $type, $required, $max_length, $label, $placeholder, $set_id = NULL ) {
		$value       = 'value="' . $value . '"';
		$max_length  = 'maxlength="' . $max_length . '"';
		$placeholder = 'placeholder="' . $placeholder . '"';
		if ( $label ) {
			?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $key ) ?>"><?php 
				echo esc_attr( $label ); if ( $required ): echo ' (required)'; endif; 
			?></label><?php
		}
		?><input id="<?php echo esc_attr( $key ) 
			   ?>" class="cms-toolkit-input <?php echo "set-input_{$set_id}"; 
			   ?>" name="<?php echo esc_attr( $key ) 
			   ?>" type="<?php echo esc_attr( $type ) ?>"<?php 
			   echo " $max_length $value $placeholder";
			   if ( $required ): echo ' required '; endif; ?>/><?php
	}

	public function boolean_input( $key, $value, $required, $label, $set_id = NULL ) {
		?><input id="<?php echo esc_attr( $key ) 
			   ?>" class="cms-toolkit-checkbox <?php echo "set-input_{$set_id}"; 
			   ?>" name="<?php echo esc_attr( $key ) 
			   ?>" type="checkbox" <?php
			   if ( $value == 'on' ) { echo 'checked '; }
			   if ( $required ) { echo 'required '; } ?>/><?php
		if ( $label ) {
			?><label class="cms-toolkit-label" for="<?php echo esc_attr( $key ) ?>"><?php
			 echo esc_attr( $label ); if ( $required ): echo ' (required)'; endif; 
			?></label><?php
		}
	}

	public function link_input( $key, $value, $required, $label, $set_id = NULL ) {
		// $existing is set for backwards compatibility and is to be phased out
		$existing = get_post_meta( get_the_ID(), $key, false);
		?><div class="link-field <?php echo "{$key}" ?>"><?php
		if ( isset( $existing[0] ) and isset( $existing[1] ) ) {
			$this->single_input( $key . "_label", $existing[1], 'text', $required, NULL, 'Link Label', 'Link label here', $set_id );
			$this->single_input( $key . "_url", $existing[0], 'text', $required, NULL, 'Link URL', 'URL here', $set_id );
		} elseif ( isset( $value['label'] ) and isset( $value['url'] ) ) {
			$this->single_input( $key . "_label", $value['label'], 'text', $required, NULL, 'Link Label', NULL, $set_id );
			$this->single_input( $key . "_url", $value['url'], 'text', $required, NULL, 'Link URL', NULL, $set_id );
		} else {
			$this->single_input( $key . "_label", NULL, 'text', $required, NULL, 'Link Label', NULL, $set_id );
			$this->single_input( $key . "_url", NULL, 'text', $required, NULL, 'Link URL', NULL, $set_id );
		}
		?></div><?php
	}

	public function file_input( $key, $value = NULL, $label = NULL, $required = NULL, $set_id = NULL ) {
		if ( $label ) {
			?><label class="cms-toolkit-file block-label set-input_<?php echo esc_attr( $set_id ) ?>"><?php
				echo esc_attr( $label );
			?></label><?php
		}
		$hidden_value = null;
		if ( $value ) {
			if ( ! get_post( $value['id'] ) ) {
				$hidden_value = $value['url'];
			}
			?><div class="tagchecklist"><?php
			if ( ! isset( $hidden_value ) ) {
				?><span>
				  <a id="<?php echo esc_attr( $key ) ?>" class="filedelbutton <?php
				   echo esc_attr( $key ); ?>"><?php
					echo esc_attr( $value['name'] );
				  ?></a>&nbsp;<?php
				  echo esc_attr( $value['name'] );
				?></span><?php
			}
				$this->hidden( 'rm_' . $key, $hidden_value, null );
			?></div><?php
		}
		?><input id="<?php echo esc_attr( $key ) 
			   ?>" name="<?php echo esc_attr( $key ) 
			   ?>" class="cms-toolkit-input <?php echo "set-input_{$set_id}"; 
			   ?>" type="file" value="<?php if ( $value and isset( $value['url'] ) ) echo esc_attr( $value['url'] ); ?>"<?php
			   if ( $required ) echo ' required '; ?>/><?php
	}

	/**
	 *  Generates a hidden field
	**/
	public function hidden( $key, $value, $set_id ) {
		?><input class="cms-toolkit-input <?php echo "set-input_{$set_id}";
			   ?>" id="<?php echo esc_attr( $key ) 
			   ?>" name="<?php echo esc_attr( $key ) 
			   ?>" type="hidden" value="<?php echo esc_attr( $value ) ?>" /><?php
	}

	/**
	 * Generate select form fields based on specified parameters
	 *
	 * Select can generate three kinds of form elements: taxonomy dropdowns,
	 * single select fields, and multi-select fields. For a taxonomy dropdown,
	 * pass a taxonomy name as the fourth parameter and select uses
	 * wp_dropdown_categories to do all the hard work for you. If the post you
	 * use it on has a term from that taxonomy it will be autoselected. Select
	 * and multi-select fields use an array in the $param parameter to generate
	 * options. To make a select a multi-select, just pass 'true' to the fifth
	 * parameter. A public function, this method may only be called from
	 * within this class.
	 *
	 * @since v1.0
	 *
	 * @uses wp_dropdown_categories
	 *
	 * @param str     $key         the field's key
	 * @param array   $param       an array of values for the <option> elements,
	 *                                 default empty, required for non-taxonomy selections
	 * @param mixed   $taxonomy    pass a string with a valid taxonomy name to
	 *                                 generate a taxonomy dropdown. Default: false
	 * @param bool    $multi       if true, generates a multi-select. Default: false
	 * @param str 	  $value       if not null, sets this value to selected.
	 * @param boolean $required    boolean to apply required browser validation
	 * @param str     $placeholder A string that will be the first value, default
	 *              			       if no value selected. Default: '--'
	 * @param str     $label       A string that will be the label
	 * @param int 	  $set_id      id of the set if it is or is in a repeated field
	 *
	**/
	public function select( $key, $params, $taxonomy, $multi, $value, $required, $placeholder, $label, $set_id = NULL ) {
		if ( $label ) {
			?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $key ) ?>"><?php echo esc_attr( $label ); ?></label><?php
		}		
		if ( $taxonomy ) { // if a taxonomy is set, use wp_dropdown category to generate the select box
			$IDs = wp_get_object_terms( get_the_ID(), $taxonomy, array( 'fields' => 'ids' ) );
			wp_dropdown_categories( 'taxonomy=' . $taxonomy . '&hide_empty=0&orderby=name&name=' . $taxonomy . '&show_option_none=Select ' . $taxonomy . '&selected='. array_pop($IDs) );
		} else {	// otherwise use all the values set in $param to generate the option
			$multiple = isset($multi) ? 'multiple' : null;
			?><select id="<?php echo esc_attr( $key ) ?>" name="<?php echo esc_attr( $key ) ?>[]" class="<?php echo "set-input_{$set_id}"; ?>" <?php echo $multiple ?> <?php if ( $required ): echo 'required'; endif; ?>><?php
			if ( empty( $value ) ) {
				?><option selected value=""><?php echo esc_attr( $placeholder ) ?></option><?php
			} else {
				?><option value=""><?php echo esc_attr( $placeholder ) ?></option><?php
				?><option selected="selected" value="<?php echo esc_attr( $value ) ?>"><?php echo esc_attr( $value ) ?></option><?php
			}
			foreach ( $params as $option ) {
				if ( $option != $value ) {
					?><option value="<?php echo esc_attr( $option ) ?>"><?php echo esc_attr( $option ) ?></option><?php
				}
			}
			?></select><?php
		}
	}

	public function post_select( $key, $posts, $value, $multi, $required, $label, $placeholder, $set_id = NULL ) { 
		global $post;
		$selected = null;
		if ( $label ) {
			?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $key ) ?>"><?php echo esc_attr( $label ); ?></label><?php
		}
		?><select class="<?php echo "set-input_{$set_id}"; ?>" id="<?php echo esc_attr( $key ) ?>" name="<?php echo esc_attr( $key ) ?>[]"<?php echo " " . $multi; if ( $required ): echo ' required '; endif; ?>><?php
			if ( $multi == null ) {
				if ( empty( $value ) ) {
					?><option selected value=""><?php echo esc_attr( $placeholder ); ?></option><?php
				} else {
					?><option value=""><?php echo esc_attr( $placeholder ); ?></option><?php
				}
			}
			foreach ( $posts as $p ) {
				if ( in_array($p->post_name, (array)$value) ) {
					$selected = "selected";
				} else {
					$selected = null;
				}
				?><option <?php echo $selected ?> value="<?php echo esc_attr( $p->post_name )?>"><?php echo $p->post_title; ?></option><?php
			}
		?></select><?php
	}

	public function taxonomy_as_meta( $slug, $params, $taxonomy, $value, $multi, $required, $label, $placeholder, $set_id= NULL ) { // keep as slug
		if ( $label ) {
			?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $slug ) ?>"><?php echo esc_attr( $label ); ?></label><?php
		}
		?><select class="<?php echo esc_attr($multi) . " "; echo "set-input_{$set_id}"; ?>" name="<?php echo esc_attr( $slug )?>[]" <?php echo esc_attr( $multi ) . " "; if ( $required ): echo 'required'; endif; ?>><?php
			if ( isset( $value ) ) {
				?><option selected value="<?php echo esc_attr( $value ) ?>" id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>"><?php echo esc_attr( $value ) ?></option><?php
			} else {
				?><option selected value="" id="no_<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>"><?php echo esc_attr( $placeholder ) ?></option><?php
			}
			foreach ( $params as $term_id ) {
				$term = get_term_by( $field = 'id', $value = strval( $term_id ), $taxonomy, $output = OBJECT, $filter = 'raw' ); 
				if ($term) {
					?><option value="<?php echo esc_attr( $term->slug ) ?>"><?php echo esc_attr( $term->name ) ?> (<?php echo esc_attr( $term->count ) ?>)</option><?php
				}
			}
		?></select><?php
	}

	/**
	 * Create a meta box for taxonomies that should always be dates.
	 *
	 * This function restricts the kinds of input that can be accepted for a
	 * custom taxonomy term. If the default metabox is replaced with a
	 * date_meta_box() the standard checklist for hierarchical categories will
	 * be replaced with a month dropdown and text input fields for day and
	 * year. The metabox will still need to be added with add_metabox and
	 * attached to a callback (like date_callback()). A public function,
	 * this method may only be called from within this class.
	 *
	 * @since 0.5.5
	 *
	 * @uses wp_locale to spit out the correct month names based on your WP install
	 *
	 * @param str  $taxonomy      the slug of the target taxonomy for this metabox (i.e., cfpb_input_date)
	 * @param bool $multiple      whether the term shoud append (true) or replace (false) existing terms
	 * @param bool $required      boolean to apply required browser validation
	 * @param bool $label         the field's label
	 * @param int  $set_id        id of the set if it is or is in a repeated field
	 **/
	public function date( $key, $value, $required, $label, $set_id = NULL ) {
		global $wp_locale;
		?><div id="<?php echo esc_attr( $key ) ?>" name="<?php echo esc_attr( $key ) ?>" class="cms-toolkit-date" ><?php
			if ( $label ) {
				?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $key ) ?>"><?php echo esc_attr( $label ); ?></label><?php
			}
			?><select id="<?php echo esc_attr( $key ) ?>_month" name="<?php echo esc_attr( $key ) ?>_month" class="<?php echo "set-input_{$set_id}"; ?>"><option selected="selected" value="" <?php if ( $required ): echo 'required'; endif; ?>>Month</option><?php
			$date = ( $value ) ? DateTime::createFromFormat(Datetime::ISO8601, $value['date']) : null;
			for ( $i = 1; $i < 13; $i++ ) {
				$month = $wp_locale->get_month( $i );
				if ( $date and $month == $date->format( 'F' ) ) {
					?><option value="<?php echo esc_attr( $month ) ?>" selected="selected"><?php echo esc_attr( $month ); ?></option><?php
				} else {
					?><option value="<?php echo esc_attr( $month ) ?>"><?php echo esc_attr( $month )  ?></option><?php
				}
			} 
			?></select><?php
			$day  = ( $date ) ? $date->format( 'd' ) : '';
			$year = ( $date ) ? $date->format( 'o' ) : '';
			?><input id="<?php echo esc_attr( $key ) ?>_day" type="text" name="<?php echo esc_attr( $key ) ?>_day"
				class="<?php echo "set-input_{$set_id}"; ?>" value="<?php echo esc_attr($day); ?>" size="2" maxlength="2" placeholder="DD"/><?php
			?><input id="<?php echo esc_attr( $key ) ?>_year" type="text" name="<?php echo esc_attr( $key ) ?>_year"
				class="<?php echo "set-input_{$set_id}"; ?>" value="<?php echo esc_attr($year); ?>" size="4" maxlength="4" placeholder="YYYY"/><?php
		?></div><?php
	}

	public function time( $key, $value, $required, $label = NULL, $set_id = NULL ) {
		$date =     ( $value ) ? DateTime::createFromFormat(Datetime::ISO8601, $value['date']) : null;
		$hour =     ( $date )  ? $date->format( 'h' ) : '';
		$min  =     ( $date )  ? $date->format( 'i' ) : '';
		$ampm =     ( $date )  ? $date->format( 'a' ) : '';
		$zone =     ( $value and isset($value['timezone']) ) ? @timezone_open($value['timezone']) : null;
		$timezone = ( isset( $zone ) )  ? $zone->getName() : '';
		foreach ( DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, 'US' ) as $country_key => $country ) {
			$countries[] = $country;
		}
		$hours = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );
		$minutes = array( '00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50' , '55' );
		?><div id="<?php echo esc_attr( $key ) ?>" name="<?php echo esc_attr( $key ) ?>" class="cms-toolkit-time" ><?php
			if ( $label ) { 
				?><label for="<?php echo esc_attr( $key ) ?>" class="cms-toolkit-label block-label"><?php 
					echo esc_attr( $label ); 
				?></label><?php
			}
			$this->select( $key . "_hour", $hours, null, null, $hour, $required, "Hour", null, $set_id );
			?> : <?php
			$this->select( $key . "_minute", $minutes, null, null, $min, $required, "Minute", null, $set_id );
			$this->select( $key . "_ampm", array( "am", "pm" ), null, null, $ampm, $required, "am/pm", null, $set_id );
			$this->select( $key . "_timezone", $countries, null, null, $timezone, $required, "Timezone", null, $set_id );
		?></div><?php
	}

	public function datetime( $key, $value, $required, $label = NULL, $set_id = NULL ) {
		?><div id="<?php echo esc_attr( $key ) ?>" name="<?php echo esc_attr( $key ) ?>" class="cms-toolkit-datetime" ><?php
			if ( $label ) { 
				?><label for="<?php echo esc_attr( $key ) ?>" class="cms-toolkit-label block-label"><?php 
					echo esc_attr( $label ); 
				?></label><?php
			}
			$this->date( $key, $value, $required, null, $set_id );
			?> @ <?php
			$this->time( $key, $value, $required, null, $set_id );
		?></div><?php
	}

	public function displayTags( $field ) {
		$post_id = get_the_ID();
		?><div class='tagchecklist'><?php
		if ( $field['key'] == $field['taxonomy'] ) {
			if ( has_term( '', $field['taxonomy'], $post_id ) ) {
				$terms = get_the_terms( $post_id, $field['taxonomy'] );
				$i = 0;
				foreach ( $terms as $term ) {
					// Checks if the current set term is wholly numeric (in this case a timestamp)
					if ( is_numeric( $term->name ) ) {
						if ( $field['type'] == 'date' ) {
							$natdate = date( 'j F Y', intval( $term->name ) );
						} elseif ( $field['type'] == 'time' ) {
							$natdate = date( 'h:ia T', intval( $term->name ) );
						} else {
							$natdate = date( 'F j Y h:ia T', intval( $term->name ) );
						}
						?><span><a id="<?php echo esc_attr( $field['taxonomy'] ) ?>" data-term-tag-num="<?php echo esc_attr( $i ) ?>"
								  class="tagdelbutton" data-term="<?php echo esc_attr( $term->name ) ?>"><?php
									echo esc_attr( $term->name );
								?></a><?php
							?>&nbsp;<?php echo esc_attr( $natdate );
						?></span><?php
					} else {
						$date = Datetime::createFromFormat(Datetime::ISO8601, $term->name );
						$data_term = $date ? $date->format('c') : $term->name;
						$display = $date ? $date->format('F j Y h:ia T') : $term->name;
						?><span><a id="<?php echo esc_attr( $field['taxonomy'] ) ?>" data-term-tag-num="<?php echo esc_attr( $i ) ?>"
								  class="tagdelbutton" data-term="<?php echo esc_attr( $data_term ) ?>"><?php
									echo esc_attr( $data_term );
								?></a><?php
							?>&nbsp;<?php echo esc_attr( $display );
						?></span><?php
					}
					$this->hidden( 'rm_' . $field['key'] . '_' . $i, null, null );
					$i++;
				}
			}
		}
		?></div><?php
	}
}
