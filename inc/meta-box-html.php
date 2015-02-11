<?php
namespace CFPB\Utils\MetaBox;
use \WP_Error;
class HTML {

	public $elements = array(
		'selects' => array( 'select', 'multiselect', 'taxonomyselect', 'tax_as_meta', 'post_select', 'post_multiselect' ),
		'inputs' => array( 'text_area', 'number', 'text', 'boolean', 'email', 'url', 'date', 'radio', 'link', ),
		'hidden' => array( 'nonce', 'hidden', 'separator', 'fieldset' ),
		);

	public function draw( $field, $form_id = NULL ) {
		if ( empty( $field ) ) {
			return new WP_Error( 'field_required', 'You need to pass a field array to this method. You passed a '. gettype( $field ) . ' .');
		}
		?><div class="cms-toolkit-wrapper<?php if (isset( $field['class'] )) { echo ' ' . esc_attr( $field['class'] ); } ?>"><?php
		if ( $field['type'] !== 'formset' and isset( $field['title'] ) ) {
			?><h4 id="<?php echo "{$field['meta_key']}"; ?>" ><?php
				echo "{$field['title']}"; 
			?></h4><?php
		}
		if ( $field['type'] == 'formset' ) {
			$this->draw_formset( $field );
		} elseif ( $field['type'] == 'fieldset' ) {
			?><fieldset><?php
				$this->pass_fieldset( $field, $form_id );
			?></fieldset><?php				
		} elseif ( in_array( $field['type'], $this->elements['inputs'] ) ) {
			$this->draw_input( $field, $form_id );
		} elseif ( in_array( $field['type'], $this->elements['selects'] ) ) {
			$this->pass_select( $field, $form_id );
		} elseif ( $field['type'] == 'hidden' ) {
			$this->hidden( $field['meta_key'], $field['value'], $form_id );
		} elseif ( $field['type'] == 'nonce' ) {
			wp_nonce_field( plugin_basename( __FILE__ ), $field['meta_key'] );
		}
		if ( isset( $field['howto'] ) ) { 
			?><p class="howto"><?php echo esc_attr( $field['howto'] ) ?></p><?php
		}
		?></div><?php
	}

	public function draw_formset( $field ) {
		global $post;
		$post_id = $post->ID;	
		$post_data = get_post_custom( $post_id );
		$form_id = $this->get_formset_id( $field['meta_key'] );
		$init = isset( $field['init'] ) ? true : false;
		$existing = array();
		$this->get_existing_data( $field, $existing, $post_data );
		?><div id="<?php echo "{$field['meta_key']}_formset"; ?>"<?php
		  if ( empty( $existing ) and ! $init ) { echo ' class="hidden new" disabled'; } ?>><?php
			?><h4 id="<?php echo "{$field['meta_key']}_header"; ?>" class="formset-header<?php
			if ( empty( $existing ) and ! $init ) { echo ' hidden'; } ?>"><?php 
				echo isset( $field['title'] ) ? $field['title'] : "Formset";
				?><a class="toggle_form_manager <?php
					echo "{$field['meta_key']} remove {$form_id}";
					if ( empty( $existing ) and ! $init ) { echo " hidden"; } 
					?>" href="#remove-formset_<?php echo $form_id; ?>"><?php
						if ( isset( $field['title'] ) ) {
							echo "Remove";
						} else {
							echo "Remove formset";
						}
				?></a><?php
			?></h4><?php
			$this->pass_fieldset( $field, $form_id );
		?></div><?php
		?><a class="toggle_form_manager <?php
			echo "{$field['meta_key']} add {$form_id}";
			if ( $existing or $init ) { echo " hidden"; } 
			?>" href="#add-formset_<?php echo $form_id; ?>"><?php
			if ( isset( $field['title'] ) ) {
				echo "Add {$field['title']}";
			} else {
				echo "Add Formset";
			}
		?></a><?php
	}

	public function pass_fieldset( $field, $form_id = NULL ) {
		foreach ($field['fields'] as $f) {
			$this->draw( $f, $form_id );
		}
	}

	public function get_formset_id( $form_meta_key ) {
		$id = "";
		$key_parts = explode( '_', $form_meta_key );
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

	public function get_existing_data( $field, &$existing, $data ) {
		foreach ( $field['fields'] as $f ) {
			if ( $f['type'] == 'fieldset' ) {
				$this->get_existing_data( $f, $existing, $data );
			} else {
				if ( array_key_exists( $f['meta_key'], $data ) ){
					if ( ! empty( $data[$f['meta_key']] ) ) {
						array_push( $existing, $data[$f['meta_key']] );
					}
				}
			}
		}
	}

	public function pass_select( $field, $form_id = NULL ) {
		$required = isset( $field['required'] ) ? $field['required'] : false;
		$key = isset( $field['meta_key'] ) ? $field['meta_key'] : $field['slug'];
		$label = isset( $field['label'] ) ? $field['label'] : null;
		$title = isset( $field['title'] ) ? $field['title'] : null;
		$multi = isset( $field['multiple'] ) ? $field['multiple'] : false;
		if ( in_array( $field['type'], array('multiselect', 'select', 'taxonomyselect' ) ) ) {
			$this->select( 
				$key, 
				$field['params'], 
				$field['taxonomy'], 
				$multi,
				$field['value'],
				$field['placeholder'],
				$title,
				$label,
				$required,
				$form_id
			);
		} elseif ( $field['type'] == 'tax_as_meta' ) {
			$this->taxonomy_as_meta(
				$slug = $field['slug'],
				$params = $field['include'],
				$taxonomy = $field['taxonomy'],
				$value = $field['value'],
				$multi,
				$placeholder = $field['placeholder'],
				$required,
				// $title,
				// $label,
				$form_id
			);
		} elseif ( $field['type'] == 'post_select' || $field['type'] == 'post_multiselect' ) {
			global $post;
			$args = $field['params'];
			$posts = get_posts($args);
			$value = get_post_meta( $post->ID, $field['meta_key'], $single = false );
			$multi = $field['type'] == 'post_multiselect' ? 'multiple' : null;
			$this->post_select(
				$key,
				$posts,
				$value,
				$multi,
				$title,
				$label,
				$required,
				$placeholder = $field['placeholder'],
				$form_id
			);
		}
	}

	public function draw_input( $field, $form_id = NULL ) {
		$required = isset( $field['required'] ) ? $field['required'] : false;
		$value = isset( $field['value'] ) ? $field['value'] : null;
		$label = isset( $field['label'] ) ? $field['label'] : null;
		$title = isset( $field['title'] ) ? $field['title'] : null;
		if ( $field['type'] == 'text_area' ) {
			$this->text_area( $field['rows'], $field['cols'], $field['meta_key'], $value, $title, $label, $field['placeholder'], $required, $form_id );
		}

		if ( in_array( $field['type'], array( 'number', 'text', 'email', 'url' ) ) ) {
			$this->single_input( $field['meta_key'], $field['type'], $field['max_length'], $value, $title, $label, $field['placeholder'], $required, $form_id );
		}

		if ( $field['type'] == 'date' ) {
			$this->date( $taxonomy = $field['taxonomy'], $multiples = $field['multiple'], $required, $form_id );
		}

		if ( $field['type'] == 'radio' ) {
			$this->single_input( $field['meta_key'], $field['type'] = 'radio', $max_length = null, $value = 'true', $title, $label, $field['placeholder'], $required, $form_id );
			$this->single_input( $field['meta_key'], $field['type'] = 'radio', $max_length = null, $value = 'false', $title, $label, $field['placeholder'], $required, $form_id );
		}

		if ( $field['type'] == 'boolean' ) {
			$this->boolean_input( $field['meta_key'], $title, $label, $value, $required, $form_id );
		}

		if ( $field['type'] == 'link' ) {
			$this->url_input($field['meta_key'], $value, $title, $label, $required, $form_id );
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
	 * @param str $meta_key value for the 'id' and 'name' attributes
	 * @param str $value a default value for the <textarea>
	 *
	**/
	public function text_area( $rows, $cols, $meta_key, $value, $title, $label, $placeholder, $required, $form_id = NULL ) {
		?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $meta_key ) ?>"><?php 
			echo $title ? esc_attr( $title ) : esc_attr( $label ); if ( $required ): echo ' (required)'; endif; 
		?></label><?php
		// wp_editor( $value, $meta_key );
		?><textarea id="<?php echo esc_attr( $meta_key ) 
				  ?>" class="cms-toolkit-textarea <?php echo "form-input_{$form_id}"; 
				  ?>" name="<?php echo esc_attr( $meta_key ) 
				  ?>" rows="<?php echo esc_attr( $rows ) 
				  ?>" cols="<?php echo esc_attr( $cols ) 
				  ?>" value="<?php echo esc_attr( $value ) 
				  ?>" placeholder="<?php echo esc_attr( $placeholder ) ?>"<?php
				   if ( $required ): echo ' required'; endif; ?>><?php echo esc_attr( $value ) 
		?></textarea><?php
	}

	/**
	 * Generates a single input field
	 *
	 * A single <input> is generated based on defined parameters.
	 *
	 * Slug and type parameters are required, all others default to null. A
	 * public function, this method may only be called from within this
	 * class.
	 *
	 * @param str $meta_key the meta_key for this field, used as 'name' and 'id'
	 * @param str $type the type of input field, use any valid HTML input type
	 * @param int $max_length the maxlength attribute for number or text inputs
	 * @param str $value a default value
	 * @param str $placeholder a default placeholder value
	 *
	 * @since 1.0
	 *
	**/
	public function single_input( $meta_key, $type, $max_length = NULL, $value = NULL, $title, $label = NULL, $placeholder = NULL, $required, $form_id = NULL ) {
		$value       = 'value="' . $value . '"';
		$max_length  = 'maxlength="' . $max_length . '"';
		$placeholder = 'placeholder="' . $placeholder . '"';
		?><label class="cms-toolkit-label block-label" for="<?php echo esc_attr( $meta_key ) ?>"><?php 
			echo $title ? esc_attr( $title ) : esc_attr( $label ); if ( $required ): echo ' (required)'; endif; 
		?></label>
		<input id="<?php echo esc_attr( $meta_key ) 
			   ?>" class="cms-toolkit-input <?php echo "form-input_{$form_id}"; 
			   ?>" name="<?php echo esc_attr( $meta_key ) 
			   ?>" type="<?php echo esc_attr( $type ) ?>"<?php 
			   echo " $max_length $value $placeholder";
			   if ( $required ): echo ' required '; endif; ?>/><?php
	}

	public function boolean_input( $meta_key, $title, $label, $value, $required, $form_id = NULL ) {
		?><input id="<?php echo esc_attr( $meta_key ) 
			   ?>" class="cms-toolkit-checkbox <?php echo "form-input_{$form_id}"; 
			   ?>" name="<?php echo esc_attr( $meta_key ) 
			   ?>" type="checkbox" <?php
			   if ( $value == 'on' ) { echo 'checked '; }
			   if ( $required ) { echo 'required '; } ?>/>
		<label class="cms-toolkit-label" for="<?php echo esc_attr( $meta_key ) ?>"><?php
			echo $title ? esc_attr( $title ) : esc_attr( $label ); if ( $required ): echo ' (required)'; endif; 
		?></label><?php
	}

	public function url_input( $meta_key, $value = NULL, $title, $label, $required, $form_id = NULL ) {
		global $post;
		$post_id = $post->ID;
		?><div class="link-field <?php echo "{$meta_key}" ?>"><?php
		$existing = get_post_meta( $post_id, $meta_key, false);
		if ( ! isset( $existing[0] ) || ! isset( $existing[1] ) ) { 
				$this->single_input( $meta_key . "_text", 'text', NULL, $value, NULL, 'Text', NULL, $required, $form_id );
				$this->single_input( $meta_key . "_url", 'url', NULL, $value, NULL, 'URL', NULL, $required, $form_id );
		} else { 
				$this->single_input( $meta_key . "_text", 'text', NULL, $existing[1], NULL, 'Text', NULL, $required, $form_id );
				$this->single_input( $meta_key . "_url", 'url', NULL, $existing[0], NULL, 'URL', NULL, $required, $form_id );
		}
		?></div><?php
	}

	/**
	 *  Generates a hidden field
	**/
	public function hidden( $meta_key, $value, $form_id ) {
		?><input class="cms-toolkit-input <?php echo "form-input_{$form_id}";
			   ?>" id="<?php echo esc_attr( $meta_key ) 
			   ?>" name="<?php echo esc_attr( $meta_key ) 
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
	 * @param array $field currently unused
	 * @param str   $meta_key the meta-key
	 * @param array $param an array of values for the <option> elements,
	 *              default empty, required for non-taxonomy selections
	 * @param str/bool $taxonomy, pass a string with a valid taxonomy name to
	 *                 generate a taxonomy dropdown. Default: false
	 * @param bool  $multi if true, generates a multi-select. Default: false
	 * @param str 	$value if not null, sets this value to selected.
	 *				Default: null
	 * @param str   $placeholder A string that will be the first value, default
	 *              if no value selected. Default: '--'
	 *
	**/
	public function select( $meta_key, $params = array(), $taxonomy = false, $multi = false, $value = null, $placeholder = '--', $title = NULL, $label = NULL, $required = false, $form_id = NULL ) {
		if ( $taxonomy != false ) { // if a taxonomy is set, use wp_dropdown category to generate the select box
			$IDs = wp_get_object_terms( get_the_ID(), $taxonomy, array( 'fields' => 'ids' ) );
			wp_dropdown_categories( 'taxonomy=' . $taxonomy . '&hide_empty=0&orderby=name&name=' . $taxonomy . '&show_option_none=Select ' . $taxonomy . '&selected='. array_pop($IDs) );
		} else {	// otherwise use all the values set in $param to generate the option
			$multiple = isset($multi) ? 'multiple' : null;
			?><label for="<?php echo esc_attr($meta_key) ?>"><?php echo $title ? esc_attr( $title ) : esc_attr( $label ); ?></label><?php
			?><select id="<?php echo esc_attr( $meta_key ) ?>" name="<?php echo esc_attr( $meta_key ) ?>[]" class="<?php echo "form-input_{$form_id}"; ?>" <?php echo $multiple ?> <?php if ( $required ): echo 'required'; endif; ?>><?php
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

	public function post_select( $meta_key, $posts, $value, $multi, $title, $label, $required, $placeholder = '--', $form_id = NULL ) { 
		global $post;
		$selected = null;
		?><label for="<?php echo esc_attr( $meta_key ) ?>"><?php echo $title ? esc_attr( $title ) : esc_attr( $label ); ?></label><?php
		?><select class="<?php echo $multi ? esc_attr( $multi ) . " ": null; echo "form-input_{$form_id}"; ?>" id="<?php echo esc_attr( $meta_key ) ?>" name="<?php echo esc_attr( $meta_key ) ?>[]"<?php echo " " . $multi; if ( $required ): echo ' required '; endif; ?>><?php
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

	public function taxonomy_as_meta( $slug, $params, $taxonomy, $value, $multi = NULL, $placeholder = '--', $required = false, $form_id= NULL ) { // keep as slug
		?><select class="<?php echo esc_attr($multi) . " "; echo "form-input_{$form_id}"; ?>" name="<?php echo esc_attr( $slug )?>[]" <?php echo esc_attr( $multi ) . " "; if ( $required ): echo 'required'; endif; ?>><?php
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
	 * @uses get_grandchildren() to spit out list items for term items
	 *
	 * @param str  $taxonomy      the slug of the target taxonomy for this metabox (i.e., cfpb_input_date)
	 * @param bool $multiples     whether the term shoud append (true) or replace (false) existing terms
	 **/
	public function date( $taxonomy, $multiple = false, $required=false, $form_id = NULL ) {
		$tax_name = stripslashes( $taxonomy );
		global $post, $wp_locale;
		$month = NULL;
		$day   = NULL;
		$year  = NULL;

		?><select id="<?php echo esc_attr( $tax_name ) ?>_month" name="<?php echo esc_attr( $tax_name ) ?>_month" class="<?php echo "form-input_{$form_id}"; ?>"><option selected="selected" value="<?php echo esc_attr( $month ) ?>" <?php if ( $required ): echo 'required'; endif; ?>>Month</option><?php
		for ( $i = 1; $i < 13; $i++ ) {
			?><option value="<?php echo esc_attr( $wp_locale->get_month( $i ) ) ?>"><?php echo sanitize_text_field( $wp_locale->get_month( $i ) )  ?></option><?php 
		} 
		?></select><?php
		?><input id="<?php echo esc_attr( $tax_name ) ?>_day" type="text" name="<?php echo esc_attr( $tax_name ) ?>_day" class="<?php echo "form-input_{$form_id}"; ?>" value="<?php echo esc_attr( $day ) ?>" size="2" maxlength="2" placeholder="DD"/><?php
		?><input id="<?php echo esc_attr( $tax_name ) ?>_year" type="text" name="<?php echo esc_attr( $tax_name ) ?>_year" class="<?php echo "form-input_{$form_id}"; ?>" value="<?php echo esc_attr( $year ) ?>" size="4" maxlength="4" placeholder="YYYY"/><?php
		if ( $multiple ) {
			?><p class="howto">Select a month, day and year to add another.</p><?php 
		} else {
			?><p class="howto">If one is set already, selecting a new month, day and year will override it.</p><?php
		} 
		?><div class="tagchecklist"><?php
			if ( has_term( '', $taxonomy, $post->id ) ) {
				$terms = get_the_terms( $post->id, $tax_name );
				$i     = 0;
				foreach ( $terms as $term ) {
					// Checks if the current set term is wholly numeric (in this case a timestamp)
					if ( is_numeric( $term->name ) ) {
						$natdate = date( 'j F, Y', intval( $term->name ) );
						?><span><a id="<?php echo sanitize_text_field( $taxonomy ) ?>-check-num-<?php echo sanitize_text_field( $i ) ?>" class="datedelbutton <?php echo sanitize_text_field( $term->name ) ?>"><?php echo sanitize_text_field( $term->name ) ?></a>&nbsp;<?php echo $natdate ?></span><?php
					} else {
						?><span><a id="<?php echo sanitize_text_field( $taxonomy ) ?>-check-num-<?php echo sanitize_text_field( $i ) ?>" class="datedelbutton <?php echo sanitize_text_field( $term->name ) ?>"><?php echo sanitize_text_field( $term->name ) ?></a>&nbsp;<?php echo sanitize_text_field( $term->name ) ?></span><?php
					}
					HTML::hidden( 'rm_' . $tax_name . '_' . $i, null, null );
					$i++;
				}
			}
		?></div><?php
	}
}
