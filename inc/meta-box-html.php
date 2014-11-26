<?php
namespace CFPB\Utils\MetaBox;
use \WP_Error;
class HTML {

	public $elements = array(
		'selects' => array( 'select', 'multiselect', 'taxonomyselect', 'tax_as_meta', 'post_select', 'post_multiselect' ),
		'inputs' => array( 'text_area', 'number', 'text', 'boolean', 'email', 'url', 'date', 'radio', 'link', ),
		'hidden' => array( 'nonce', 'hidden', 'separator', 'fieldset' ),
		);

	public function draw( $field, $slug = null ) {
		if ( empty( $field ) ) {
			$error = new WP_Error( 'field_required', 'You need to pass a field array to this method. You passed a '. gettype( $field ) . ' .');
			return $error;
		}
		?>
		<div<?php if (isset( $field['class'] )) { ?> class="<?php echo esc_attr( $field['class'] ); ?>"<?php } ?>><?php

		?><p><?php

		if ( ! isset($field['params']['is_formset_of_fieldsets']) ):
			if ( isset( $field['title'] ) ) {
				?><h4 id="<?php echo "{$field['meta_key']}"; ?>" ><?php
					echo "{$field['title']}"; 
				?></h4><?php
			}
		endif;
		if ( $field['type'] == 'fieldset' ) {
			if ( ! isset($field['params']['is_formset_of_fieldsets']) ) {
				?><fieldset><?php
					$this->pass_fieldset($field);
				?></fieldset><?php				
			} else {
				$this->pass_fieldset_of_formset($field);
			}
		} elseif ( in_array($field['type'], $this->elements['inputs'] ) ) {
			$this->pass_input($field);
		} elseif ( in_array($field['type'], $this->elements['selects'] ) ) {
			$this->pass_select($field);
		} elseif ( $field['type'] == 'hidden' ) {
			HTML::hidden( $field['meta_key'], $field['value'] );
		} elseif ( $field['type'] == 'nonce' ) {
			wp_nonce_field( plugin_basename( __FILE__ ), $field['meta_key'] );
		}
		if ( array_key_exists('howto', $field) ) {
		?> <p class="howto"><?php echo esc_html( $field['howto'] ) ?></p><?php
		} 
		?></p>
		</div><?php
	}

	private function pass_fieldset( $field, $form_num = NULL ) {
		foreach ($field['fields'] as $f) {
			$required = array_key_exists('required', $f) ? $f['required'] : false;
			if ( $f['type'] == 'boolean' ) {
				HTML::boolean_input($f['meta_key'], $f['label'], $f['value'], $fieldset = true, $required);
			} elseif ( in_array( $f['type'], $this->elements['inputs'] ) ) {
				$placeholder = array_key_exists('placeholder', $f) ? esc_attr( $f['placeholder'] ) : null;
				$title = array_key_exists('title', $f) ? esc_attr( $f['title'] ) : null;
				$label = array_key_exists('label', $f) ? $f['label'] : null;
				$this->draw_input( $f, $form_num );
			} elseif ( in_array($f['type'], array( 'select', 'multiselect', 'taxonomselect') ) ) {
				HTML::select($f['meta_key'], $f['params'], $f['taxonomy'], $f['multiselect'], $f['placeholder'], $required);
			}
		}
	}

	private function pass_fieldset_of_formset($field) {
		global $post;
		$post_id = $post->ID;	
    	$existing_terms = array();
		for ( $i = 0; $i < count($field['fields']); $i++ ):
			$meta_key = $field['fields'][$i]['meta_key'];
			$post_data = get_post_custom( $post_id );
			if ( array_key_exists( $meta_key, $post_data ) ){
				$existing = $post_data[$meta_key];
			}
			if ( ! empty($existing) ):
				array_push($existing_terms, $existing);
			endif;
		endfor;
		$count = $i;
		$form_num = substr( $field['fields'][0]['meta_key'], -1 ) ;

		if ( ! empty( $existing_terms ) ):
			if ( isset( $field['title'] ) ) {
				?><h4 id="<?php echo "{$field['meta_key']}_{$form_num}"; ?>" >
					<?php echo "{$field['title']} " . ($form_num + 1) . "";
				?></h4><?php
			}
			?><fieldset id="<?php echo "{$field['meta_key']}_{$form_num}"; ?>"><?php
				$this->pass_fieldset( $field, $form_num );
			?></fieldset><?php
			?>
			<a class='toggle_form_manager <?php echo "{$field['meta_key']}_{$form_num} add hidden"?>' href="#subinitiative_links">
				<?php
				if ( isset( $field['title'] ) ) {
					echo "Add {$field['title']} " . ($form_num + 1);
				} else {
					echo "Add Fieldset " . ($form_num + 1);
				}?>
			</a>
			<p>
				<a class='toggle_form_manager <?php echo "{$field['meta_key']}_{$form_num} remove {$form_num}"?>' href="#subinitiative_links">
					<?php
					if ( isset( $field['title'] ) ) {
						echo "Remove {$field['title']} " . ($form_num + 1);
					} else {
						echo "Remove Fieldset " . ($form_num + 1);
					}?>
				</a>
			</p><?php
		else:
			if ( isset( $field['title'] ) ) {
				?><h4 id="<?php echo "{$field['meta_key']}_{$form_num}"; ?>" class="hidden">
					<?php echo "{$field['title']} " . ($form_num + 1) . "";
				?></h4><?php
			}
			?><fieldset disabled id="<?php echo "{$field['meta_key']}_{$form_num}"; ?>" class="hidden new"><?php
				$this->pass_fieldset( $field, $form_num );
			?></fieldset>
			<a class='toggle_form_manager <?php echo "{$field['meta_key']}_{$form_num} add"?>' href="#subinitiative_links">
				<?php
				if ( isset( $field['title'] ) ) {
					echo "Add {$field['title']} " . ($form_num + 1);
				} else {
					echo "Add Fieldset " . ($form_num + 1);
				}?>
			</a>
			<p>
				<a class='toggle_form_manager <?php echo "{$field['meta_key']}_{$form_num} remove hidden {$form_num}"?>' href="#subinitiative_links">
					<?php
					if ( isset( $field['title'] ) ) {
						echo "Remove {$field['title']} " . ($form_num + 1);
					} else {
						echo "Remove Fieldset " . ($form_num + 1);
					}?>
				</a>
			</p><?php
		endif;
	}

	private function pass_input( $field, $for = null ) {
		if ( array_key_exists('fields', $field) ) {
			foreach ( $field['fields'] as $f ) {
				HTML::draw_input($f);
			}
		} else {
			HTML::draw_input($field);
		}
		
	}

	private function draw_input( $field, $form_num = NULL ) {
		if ( array_key_exists('required', $field ) ) {
			$required = true;
		} else {
			$required = false;
		}
		if ( array_key_exists('title', $field ) ){
			$title = $field['title'];
		} else {
			$title = null;
		}
		if ( $field['type'] == 'text_area' ) {
			HTML::text_area( $field['rows'], $field['cols'], $field['meta_key'], $field['value'], $field['label'], $field['placeholder'], $required, $form_num );
		}

		if ( in_array( $field['type'], array( 'number', 'text', 'email', 'url' ) ) ) {
			HTML::single_input( $field['meta_key'], $field['type'], $field['max_length'], $field['value'], $field['label'], $field['placeholder'], $title, $required, $form_num );
		}

		if ( $field['type'] == 'date' ) {
			HTML::date( $taxonomy = $field['taxonomy'], $tax_nice_name = $field['title'], $multiples = $field['multiple'], $required, $form_num );
		}

		if ( $field['type'] == 'radio' ) {
			HTML::single_input( $field['meta_key'], $field['type'] = 'radio', $max_length = null, $value = 'true', $field['label'], $field['placeholder'], $field['title'], $required, $form_num );
			HTML::single_input( $field['meta_key'], $field['type'] = 'radio', $max_length = null, $value = 'false', $field['label'], $field['placeholder'], $field['title'], $required, $form_num );
		}

		if ( $field['type'] == 'boolean' ) {
			HTML::boolean_input( $field['meta_key'], $field['label'], $field['value'], $required );
		}

		if ( $field['type'] == 'link' ) {
			if ( array_key_exists( 'max_num_forms', $field['params'] ) ):
				$max = $field['params']['max_num_forms'];
			else:
				$max = 1;
			endif;
			if ( array_key_exists( 'init_num_forms', $field['params'] ) ):
				$init = $field['params']['init_num_forms'];
			else:
				$init = 1;
			endif;
			HTML::url_input($field['meta_key'], $init, $max, $field['max_length'], $field['value'], $required, $form_num );
		}
	}

	private function pass_select( $field ) {
		if ( array_key_exists('required', $field ) ) {
			$required = true;
		} else {
			$required = false;
		}
		if ( in_array( $field['type'], array('multiselect', 'select', 'taxonomyselect' ) ) ) {
			HTML::select( 
				$field['meta_key'], 
				$field['params'], 
				$field['taxonomy'], 
				$field['multiple'], 
				$field['placeholder'], 
				$required 
			);
		}

		if ( $field['type'] == 'tax_as_meta' ) {
			HTML::taxonomy_as_meta(
				$slug = $field['slug'],
				$params = $field['include'],
				$taxonomy = $field['taxonomy'],
				$key = $field['meta_key'],
				$placeholder = $field['placeholder'],
				$value = $field['value'],
				$required
			);
		}

		if ( $field['type'] == 'post_select' || $field['type'] == 'post_multiselect' ) {
			$args = $field['params'];
			if ( $field['type'] == 'post_multiselect') {
				$multi = 'multiple';
			} else {
				$multi = null;
			}
			global $post;
			$value = get_post_meta( $post->ID, $field['meta_key'], $single = false );
			$posts = get_posts($args);
			HTML::post_select(
				$slug = $field['meta_key'],
				$posts = $posts,
				$value,
				$multi,
				$placeholder = $field['placeholder'],
				$required 
			);
		}
	}

/**
	 * Generate a <textarea> field
	 *
	 * Generates a textarea HTML field using defined parameters A protected
	 * function, this method may only be called from within this class.
	 *
	 * All parameters are required
	 * @param array $field unused, eliminate
	 * @param int $rows value for the rows attribute
	 * @param int $cols value for the cols attribute
	 * @param str $slug value for the 'id' and 'name' attributes
	 * @param str $value a default value for the <textarea>
	 *
	**/
	protected function text_area( $rows, $cols, $slug, $value, $label, $placeholder, $required = false, $form_num = NULL ) { ?>
		<p>
			<div>
				<label><?php echo $label; if ( $required ): echo ' (required)'; endif; ?></label>
			</div>
			<div>
				<textarea id="<?php echo esc_attr( $slug ) ?>" class="cms-toolkit-textarea form-input_<?php echo $form_num; ?>" name="<?php echo esc_attr( $slug ) ?>" rows="<?php echo esc_attr( $rows ) ?>" cols="<?php echo esc_attr( $cols ) ?>" value="<?php echo esc_attr( $value ) ?>" placeholder="<?php echo esc_attr( $placeholder ) ?>" <?php if ( $required ): echo 'required'; endif; ?>><?php echo esc_html( $value ) ?></textarea>
			</div>
		</p>
	<?php
	}

	/**
	 * Generates a single input field
	 *
	 * A single <input> is generated based on defined parameters.
	 *
	 * Slug and type parameters are required, all others default to null. A
	 * protected function, this method may only be called from within this
	 * class.
	 *
	 * @param str $slug the meta_key for this field, used as 'name' and 'id'
	 * @param str $type the type of input field, use any valid HTML input type
	 * @param int $max_length the maxlength attribute for number or text inputs
	 * @param str $value a default value
	 * @param str $placeholder a default placeholder value
	 *
	 * @since 1.0
	 *
	**/
	protected function single_input( $slug, $type, $max_length = NULL, $value = NULL, $label = NULL, $placeholder = NULL, $title = NULL, $required = false, $form_num = NULL ) {
			$value       = 'value="'. $value . '"';
			$max_length  = 'maxlength="'. $max_length . '"';
			$placeholder = 'placeholder="' . $placeholder . '"';
			?><div>
				<label for="<?php echo esc_attr( $slug ) ?>"><?php echo $label; if ( $required ): echo ' (required)'; endif; ?></label>
			</div>
			<div>
				<input id="<?php echo esc_attr( $slug ) ?>" class="cms-toolkit-input form-input_<?php echo $form_num; ?>" name="<?php echo esc_attr( $slug ) ?>" type="<?php echo esc_attr( $type ) ?>" <?php echo " $max_length $value $placeholder" ?>  <?php if ( $required ): echo 'required'; endif; ?>/>
			</div>
			<?php if ( $title != NULL ): ?>
				<p class="howto"><?php echo $title ?></p><?php
			endif;
	}

	protected function boolean_input( $slug, $label, $value, $required = false ) {
			$name = 'name="' . esc_attr($slug) . '"';
			$id = 'id="'. esc_attr($slug) . '"';
	?>
		<p>
			<input <?php echo $id . ' ' . $name ?> type="checkbox" <?php if ($value == 'on') { echo ' checked'; } ?>  <?php if ( $required ): echo 'required'; endif; ?>/>
			<label for="<?php echo esc_attr( $slug ) ?>"><?php echo $label;  if ( $required ): echo ' (required)'; endif; ?></label>
		</p>
	<?php
	}

	protected function url_input( $slug, $init_num_forms, $max_num_forms, $max_length = NULL, $value = NULL, $required = false, $form_num = NULL ) {
		global $post;
		$post_id = $post->ID;
		$value = "value='{$value}'";
		$max_length = "max_length='{$max_length}'";
		$existing_terms = array();
		for ( $i = 0; $i <= $max_num_forms; $i++ ):
			$existing = get_post_meta( $post_id, "{$slug}_{$i}", $single = false );
			if ( ! empty($existing) ) {
				array_push($existing_terms, $existing);
			}
		endfor;
		$count = count($existing_terms) > $init_num_forms ? count($existing_terms) : $init_num_forms;
		?>
		<div class='link_manager <?php echo "{$slug} max_{$max_num_forms}" ?>'>
		<?php
			for ( $i = 0; $i < $count; $i++ ):
				$existing = get_post_meta( $post_id, $slug . "_{$i}", false);
				if ( ! isset( $existing[0] ) || ! isset( $existing[1] ) ): ?>
						<fieldset>
							<label for='<?php echo esc_attr( $slug ) . '_text_' . $i ?>'>Link text <?php  if ( $required ): echo ' (required)'; endif; ?></label>
							<input id='<?php echo esc_attr( $slug ) . '_text_' . $i ?>' class='<?php echo $i; ?> cms-toolkit-input form-input_<?php echo $form_num; ?>' name="<?php echo esc_attr( $slug ) . '_text_' . $i ?>" type="text" <?php echo " $max_length $value" ?>  <?php if ( $required ): echo 'required'; endif; ?>/>
							<label for='<?php echo esc_attr( $slug ) . '_url_' . $i ?>'>Link URL <?php  if ( $required ): echo ' (required)'; endif; ?></label>
							<input class='<?php $i ?> cms-toolkit-input form-input_<?php echo $form_num; ?>' id='<?php echo esc_attr( $slug ) . '_url_' . $i?>' name='<?php echo esc_attr( $slug ) ?>_url_<?php echo $i ?>' type="url" <?php echo " $max_length $value" ?>  <?php if ( $required ): echo 'required'; endif; ?>/>
						</fieldset>
				<?php else:?>
					<p><span class="<?php echo $i ?>">Link text: <?php echo $existing[1] ?><br />Link URL: <?php echo $existing[0] ?>.<br /><a href="#related_links" title='<?php esc_attr($slug) ?>' class="toggle_link_manager <?php echo "{$slug} edit {$i}"  ?>" >Edit</a></span></p>
						<fieldset id='<?php echo "{$slug}_{$i}" ?>' class='hidden'>
							<label class='<?php echo $i ?>' for='<?php echo esc_attr( $slug ) . '_text_' . $i ?>'>Link text <?php  if ( $required ): echo ' (required)'; endif; ?></label>
							<input class='<?php echo $i ?> form-input_<?php echo $form_num; ?>' id='<?php echo esc_attr( $slug ) . '_text_' . $i ?>' class="cms-toolkit-input" name="<?php echo esc_attr( $slug ) . '_text_' . $i ?>" type="text" <?php echo " $max_length value='{$existing[1]}'" ?>  <?php if ( $required ): echo 'required'; endif; ?>/>
							<label class='<?php echo $i ?>' for='<?php echo esc_attr( $slug ) . '_url_' . $i ?>'>Link URL <?php  if ( $required ): echo ' (required)'; endif; ?></label>
							<input class='<?php echo $i ?> form-input_<?php echo $form_num; ?>' id='<?php echo esc_attr( $slug ) . '_url_' . $i ?>' class="cms-toolkit-input" name='<?php echo esc_attr( $slug ) ?>_url_<?php echo $i ?>' type="url" <?php echo " $max_length value='{$existing[0]}'" ?>  <?php if ( $required ): echo 'required'; endif; ?>/>
							<a href="#related_links" title='<?php esc_attr($slug) ?>' class="toggle_link_manager <?php echo "{$slug} edit {$i}"  ?>" >Undo</a>
							<span class="howto">Save the post to update this field, click undo to keep what you had (above).</span>
						</fieldset>
				<?php endif;?>
		<?php endfor;
		for ( $i = $count; $i <= $max_num_forms; $i++ ): ?>
				<fieldset disabled id="<?php echo "{$slug}_{$i}" ?>" class="hidden new">
					<label class='<?php echo $i ?>' for='<?php echo esc_attr( $slug ) . '_text_' . $i ?>'>Link text <?php  if ( $required ): echo ' (required)'; endif; ?></label>
					<input class='<?php echo $i ?> form-input_<?php echo $form_num; ?>' id='<?php echo esc_attr( $slug ) . '_text_' . $i ?>' class="cms-toolkit-input" name="<?php echo esc_attr( $slug ) . '_text_' . $i ?>" type="text" <?php echo " $max_length value=''" ?> />
					<label class='<?php echo $i ?>' for='<?php echo esc_attr( $slug ) . '_url_' . $i ?>'>Link URL <?php  if ( $required ): echo ' (required)'; endif; ?></label>
					<input class='<?php echo $i ?> form-input_<?php echo $form_num; ?>' id='<?php echo esc_attr( $slug ) . '_url_' . $i ?>' class="cms-toolkit-input" name='<?php echo esc_attr( $slug ) ?>_url_<?php echo $i ?>' type="url" <?php echo " $max_length value=''" ?> />
					<a href="#related_links" title='<?php esc_attr($slug) ?>' class="toggle_link_manager <?php echo "{$slug} remove {$i}"  ?>" >Remove</a>
				</fieldset>
		<?php endfor; 
		if ( $count < $max_num_forms ): ?>
			<a class='toggle_link_manager <?php echo "{$slug} add"?>' href="#related_links" >Add a link</a>
		<?php endif; ?>
	</div>
	<?php
	}

	/**
	 *  Generates a hidden field
	**/
	protected function hidden( $slug, $value ) { ?>
			<input id="<?php echo esc_attr( $slug ) ?>" class="cms-toolkit-input" name="<?php echo esc_attr( $slug ) ?>" type="hidden" value="<?php echo esc_attr( $value ) ?>" />
	<?php
	}

	/**
	 * Generate select form fields based on specified parameters
	 *
	 * Select can generate three kinds of form elements: taxonomy dropdowns,
	 * single select fields, and mutli-select fields. For a taxonomy dropdown,
	 * pass a taxonomy name as the fourth parameter and select uses
	 * wp_dropdown_categories to do all the hard work for you. If the post you
	 * use it on has a term from that taxonomy it will be autoselected. Select
	 * and multi-select fields use an array in the $param parameter to generate
	 * options. To make a select a mutli-select, just pass 'true' to the fifth
	 * parameter. A protected function, this method may only be called from
	 * within this class.
	 *
	 * @since v1.0
	 *
	 * @uses wp_dropdown_categories
	 *
	 * @param array $field currently unused
	 * @param str   $slug the meta-key
	 * @param array $param an array of values for the <option> elements,
	 *              default empty, required for non-taxonomy selections
	 * @param str/bool $taxonomy, pass a string with a valid taxonomy name to
	 *                 generate a taxonomy dropdown. Default: false
	 * @param bool  $multi if true, generates a mutli-select. Default: false
	 * @param str 	$value if not null, sets this value to selected.
	 *				Default: null
	 * @param str   $placeholder A string that will be the first value, default
	 *              if no value selected. Default: '--'
	 *
	**/
	protected function select( $slug, $params = array(), $taxonomy = false, $multi = null, $value = null, $placeholder = '--', $required = false ) {
		if ( $taxonomy != false ): // if a taxonomy is set, use wp_dropdown category to generate the select box
			$IDs = wp_get_object_terms( get_the_ID(), $taxonomy, array( 'fields' => 'ids' ) );
			wp_dropdown_categories( 'taxonomy=' . $taxonomy . '&hide_empty=0&orderby=name&name=' . $taxonomy . '&show_option_none=Select ' . $taxonomy . '&selected='. array_pop($IDs) );
		else :	// otherwise use all the values set in $param to generate the option
				$multiple = isset($multi) ? 'multiple' : null;
				?> 
				<label for="<?php echo esc_attr($slug) ?>"><select id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>[]" <?php echo $multiple ?> <?php if ( $required ): echo 'required'; endif; ?>></label>
				<?php
				if ( empty( $value ) ): ?>
					<option selected value=""><?php echo esc_html( $placeholder ) ?></option>
				<?php
				else : ?>
					<option value=""><?php echo esc_html( $placeholder ) ?></option>
					<option selected="selected" value="<?php echo esc_attr( $value ) ?>" ><?php echo esc_html( $value ) ?></option><?php
				endif;

			foreach ( $params as $option ): ?>
				<option value="<?php echo esc_attr( $option ) ?>"><?php echo esc_html( $option ) ?></option>
			<?php
			endforeach;
		?>	</select> <?php
		endif;
	}

	protected function post_select( $slug, $posts, $value, $multi, $placeholder = '--', $required = false ) { 
		global $post;
		$selected = null;?>
			<label for="<?php echo esc_attr( $slug ) ?>"><select class="<?php echo esc_attr($multi)?>" id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>[]" <?php echo $multi; ?>  <?php if ( $required ): echo 'required'; endif; ?>></label>
				<?php if ( $multi == null ):
						if ( empty( $value )  ): ?>
							<option value='' selected>-- Nothing selected --</option>
						<?php else: ?>
					<option value=''>-- Nothing selected --</option>
					<?php endif; 
				endif; ?>
				<?php foreach ( $posts as $p ):
					if ( in_array($p->post_name, (array)$value) ):
						$selected = "selected";
					else:
						$selected = null;
					endif;?>
					<option <?php echo $selected ?> value="<?php echo esc_attr( $p->post_name )?>"><?php echo $p->post_title; ?></option>
				<?php endforeach; ?>
			</select>			
		<?php
	}

	protected function taxonomy_as_meta( $slug, $params, $taxonomy, $key, $placeholder = '--', $value, $multi=null, $required = false ) {?>
		<select class="<?php echo esc_attr($multi) ?>" name='<?php echo esc_attr( $slug )?>[]' <?php echo esc_attr( $multi )?>  <?php if ( $required ): echo 'required'; endif; ?>><?php
			if ( isset( $value ) ):?>
				<option selected value="<?php echo esc_attr( $value ) ?>" id="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $value ) ?></option><?php
			else:?>
			<option value="" id="no_<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>"><?php echo esc_html( $placeholder ) ?></option><?php
			endif;
				foreach ( $params as $term_id ):
					$term = get_term_by( $field = 'id', $value = strval( $term_id ), $taxonomy, $output = OBJECT, $filter = 'raw' ); 
					if ($term):?>
					<option value="<?php echo esc_attr( $term->slug ) ?>"><?php echo esc_html( $term->name )  ?> (<?php echo esc_html( $term->count ) ?>)</option><?php
					endif;
				endforeach;?>
		</select>
		<?php
	}

	/**
	 * Create a meta box for taxonomies that should always be dates.
	 *
	 * This function restricts the kinds of input that can be accepted for a
	 * custom taxonomy term. If the default metabox is replaced with a
	 * date_meta_box() the standard checklist for hierarchical categories will
	 * be replaced with a month dropdown and text input fields for day and
	 * year. The metabox will still need to be added with add_metabox and
	 * attached to a callback (like date_callback()). A protected function,
	 * this method may only be called from within this class.
	 *
	 * @since 0.5.5
	 *
	 * @uses wp_locale to spit out the correct month names based on your WP install
	 * @uses get_grandchildren() to spit out list items for term items
	 *
	 * @param str  $taxonomy      the slug of the target taxonomy for this metabox (i.e., cfpb_input_date)
	 * @param str  $tax_nice_name the name of the target taxonomy (i.e. Input Date)
	 * @param bool $multiples     whether the term shoud append (true) or replace (false) existing terms
	 **/
	protected function date( $taxonomy, $tax_nice_name, $mutliples = false, $required = false, $form_num = NULL ) {?>
	    <?php
			$tax_name = stripslashes( $taxonomy );
			global $post, $wp_locale;

			$month = NULL;
			$day   = NULL;
			$year  = NULL;

			?><select id="<?php echo esc_attr( $tax_name ) ?>_month" name="<?php echo esc_attr( $tax_name ) ?>_month" class="form-input_<?php echo $form_num; ?>"><option selected="selected" value='<?php echo esc_attr( $month ) ?>' <?php if ( $required ): echo 'required'; endif; ?>>Month</option>
	    <?php
			for ( $i = 1; $i < 13; $i++ ) {
				?><option value="<?php echo esc_attr( $wp_locale->get_month( $i ) ) ?>"><?php echo sanitize_text_field( $wp_locale->get_month( $i ) )  ?></option>
	    <?php } ?>
	    </select>
	    <input id="<?php echo esc_attr( $tax_name ) ?>_day" type="text" name="<?php echo esc_attr( $tax_name ) ?>_day" class="form-input_<?php echo $form_num; ?>" value="<?php echo esc_attr( $day ) ?>" size="2" maxlength="2" placeholder="DD"/>
	    <input id="<?php echo esc_attr( $tax_name ) ?>_year" type="text" name="<?php echo esc_attr( $tax_name ) ?>_year" class="form-input_<?php echo $form_num; ?>" value="<?php echo esc_attr( $year ) ?>" size="4" maxlength="4" placeholder="YYYY"/>
	    <?php
			if ( $multiples = false ) { ?>
	      <p class="howto">If one is set already, selecting a new month, day and year will override it.</p>
	    <?php } else { ?>
	      <p class='howto'>Select a month, day and year to add another <?php echo sanitize_text_field( $tax_nice_name ) ?></p>
	    <?php } ?>

	    <div class='tagchecklist'>
	    <?php
			if ( has_term( '', $taxonomy, $post->id ) ) {
				$terms = get_the_terms( $post->id, $tax_name );
				$i     = 0;
				foreach ( $terms as $term ) {
					// Checks if the current set term is wholly numeric (in this case a timestamp)
					if ( is_numeric( $term->name ) ) {
						$natdate = date( 'j F, Y', intval( $term->name ) );
	?>
	          <span><a id='<?php echo sanitize_text_field( $taxonomy ) ?>-check-num-<?php echo sanitize_text_field( $i ) ?>' class='ntdelbutton <?php echo sanitize_text_field( $term->name ) ?>'><?php echo sanitize_text_field( $term->name ) ?></a><?php echo $natdate ?></span>
	        <?php
					} else {
						$date = strtotime( $term->name );                                     // If it isn't, convert it to a timestamp -- why? ?>
	        <?php
					}
				$i++;
				}
			}
			?>
		</div>
	<?php
	}
}
