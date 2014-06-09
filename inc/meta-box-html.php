<?php
namespace CFPB\Utils\MetaBox;
use \WP_Error;
class HTML {

	public $elements = array(
		'selects' => array( 'select', 'multiselect', 'taxonomyselect', 'tax_as_meta', 'post_select' ),
		'inputs' => array( 'text_area', 'number', 'text', 'boolean', 'email', 'url', 'date', 'radio', 'link', ),
		'hidden' => array( 'nonce', 'hidden', 'separator' ),
		);

	public function draw( $field ) {
		if ( empty( $field ) ) {
			$error = new WP_Error( 'field_required', 'You need to pass a field array to this method. You passed a '. gettype( $field ) . ' .');
			return $error;
		}
		?><div<?php if (isset( $field['class'] )) { ?> class="<?php echo esc_attr( $field['class'] ); ?>"<?php } ?>><?php

		if ( isset( $field['title'] ) ) {?>
			<h4><?php echo esc_attr( $field['title'] ); ?></h4><?php
		}

		if ( in_array($field['type'], $this->elements['inputs'] ) ) {
			$this->pass_input($field);
		} elseif ( in_array($field['type'], $this->elements['selects'] ) ) {
			$this->pass_select($field);
		} elseif ( $field['type'] == 'hidden' ) {
			HTML::hidden( $field['slug'], $field['value'] );
		} elseif ( $field['type'] == 'nonce' ) {
			wp_nonce_field( plugin_basename( __FILE__ ), $field['slug'] );
		}

		if ( ! in_array($field['type'], $this->elements['hidden'] ) ) { ?>
			<p class="howto"><?php echo esc_html( $field['howto'] ) ?></p>
		<?php
		}

		?></div><?php
	} 

	private function pass_input( $field ) {
		if ( $field['type'] == 'text_area' ) {
			HTML::text_area( $field['rows'], $field['cols'], $field['slug'], $field['value'], $field['placeholder'] );
		}

		if ( in_array( $field['type'], array( 'number', 'text', 'boolean', 'email', 'url' ) ) ) {
			HTML::single_input( $field['slug'], $field['type'], $field['max_length'], $field['value'], $field['placeholder'] );
		}

		if ( $field['type'] == 'date' ) {
			HTML::date( $taxonomy = $field['taxonomy'], $tax_nice_name = $field['title'], $multiples = $field['multiple'] );
		}

		if ( $field['type'] == 'radio' ) {
			HTML::single_input( $field['slug'], $type = 'radio', $max_length = null, $value = 'true' );
			HTML::single_input( $field['slug'], $type = 'radio', $max_length = null, $value = 'false' );
		}
		if ( $field['type'] == 'link' ) {
			if ( array_key_exists( 'count', $field['params'] ) ):
				$count = $field['params']['count'];
			else:
				$count = 1;
			endif;
			HTML::url_input($field['slug'], $count, $field['max_length'], $field['value']);
		}
	}

	private function pass_select( $field ) {
		if ( $field['type'] == 'multiselect' ){
			HTML::select( $field['slug'], $field['params'], $taxonomy = false, $multi = true, $placeholder = $field['placeholder'] );
		}

		if ( $field['type'] == 'select' ) {
			HTML::select( $field['slug'], $field['params'], $taxonomy = false, $multi = false, $field['value'], $placeholder = $field['placeholder'] );
		}

		if ( $field['type'] == 'taxonomyselect' ) {
			HTML::select( $field['slug'], $field['params'], $taxonomy = $field['taxonomy'], $multi = $field['multiple'], $value = $field['value'], $placeholder = $field['placeholder'] );
		}

		if ( $field['type'] == 'tax_as_meta' ) {
			HTML::taxonomy_as_meta(
				$slug = $field['slug'],
				$params = $field['include'],
				$taxonomy = $field['taxonomy'],
				$key = $field['meta_key'],
				$placeholder = $field['placeholder'],
				$value = $field['value']
			);
		}

		if ( $field['type'] == 'post_select' ) {
			$args = $field['params'];
			$posts = get_posts($args);
			HTML::post_select(
				$slug = $field['slug'],
				$posts = $posts,
				$value = $field['value'],
				$placeholder = $field['placeholder'] );
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
	protected function text_area( $rows, $cols, $slug, $value, $placeholder ) { ?>
		<p>
			<textarea id="<?php echo esc_attr( $slug ) ?>" class="cms-toolkit-textarea" name="<?php echo esc_attr( $slug ) ?>" rows="<?php echo esc_attr( $rows ) ?>" cols="<?php echo esc_attr( $cols ) ?>" value="<?php echo esc_attr( $value ) ?>" placeholder="<?php echo esc_attr( $placeholder ) ?>"><?php echo esc_html( $value ) ?></textarea>
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
	protected function single_input( $slug, $type, $max_length = NULL, $value = NULL, $placeholder = NULL ) {
			$value       = 'value="'. $value . '"';
			$max_length  = 'maxlength="'. $max_length . '"';
			$placeholder = 'placeholder="' . $placeholder . '"';?>
		<p>
			<input id="<?php echo esc_attr( $slug ) ?>" class="cms-toolkit-input" name="<?php echo esc_attr( $slug ) ?>" type="<?php echo esc_attr( $type ) ?>" <?php echo " $max_length $value $placeholder" ?> />
		</p>
	<?php
	}

	protected function url_input( $slug, $count = 1, $max_length = NULL, $value = NULL ) {
		global $post;
		$post_id = $post->ID;
		$value = "value='{$value}'";
		$max_length = "max_length='{$max_length}'";?>
		<ol id="link_manager">
		<?php
			for ( $i = 0; $i < $count; $i++ ):
			$existing = get_post_meta( $post_id, $slug . "_{$i}", false); ?>
				<?php if ( ! isset( $existing[0] ) || ! isset( $existing[1] ) ): ?>
					<li>
						<label for='<?php echo esc_attr( $slug ) . '_text_' . $i ?>'>Link text</label>
						<input id='<?php echo esc_attr( $slug ) . '_text_' . $i ?>' class="cms-toolkit-input" name="<?php echo esc_attr( $slug ) . '_text_' . $i ?>" type="text" <?php echo " $max_length $value" ?> />
						<label for='<?php echo esc_attr( $slug ) . '_url_' . $i ?>'>Link URL</label>
						<input id='<?php echo esc_attr( $slug ) . '_url_' . $i ?>' class="cms-toolkit-input" name='<?php echo esc_attr( $slug ) ?>_url_<?php echo $i ?>' type="url" <?php echo " $max_length $value" ?> />
					</li>
				<?php else:?>
					<li><span class="<?php echo $i ?>">Link text: <?php echo $existing[1] ?><br />Link URL: <?php echo $existing[0] ?>.<br /><a href="#related_links" title='<?php esc_attr($slug) ?>' onclick="clear_link_manager(<?php print_r( $i ) ?>, <?php print_r( $post_id ) ?>, <?php print_r( $slug ) ?>)" >Click here to replace it</a></span>
						<label class='hidden <?php echo $i ?>' for='<?php echo esc_attr( $slug ) . '_text_' . $i ?>'>Link text</label>
						<input class='hidden <?php echo $i ?>' id='<?php echo esc_attr( $slug ) . '_text_' . $i ?>' class="cms-toolkit-input" name="<?php echo esc_attr( $slug ) . '_text_' . $i ?>" type="text" <?php echo " $max_length value=''" ?> />
						<label class='hidden <?php echo $i ?>' for='<?php echo esc_attr( $slug ) . '_url_' . $i ?>'>Link URL</label>
						<input class='hidden <?php echo $i ?>' id='<?php echo esc_attr( $slug ) . '_url_' . $i ?>' class="cms-toolkit-input" name='<?php echo esc_attr( $slug ) ?>_url_<?php echo $i ?>' type="url" <?php echo " $max_length value=''" ?> />
					</li>
				<?php endif;?>
		<?php endfor; ?>
	</ol><?php
	}

	/**
	 *  Generates a hidden field
	**/
	protected function hidden( $slug, $value ) { ?>
		<p>
			<input id="<?php echo esc_attr( $slug ) ?>" class="cms-toolkit-input" name="<?php echo esc_attr( $slug ) ?>" type="hidden" value="<?php echo esc_attr( $value ) ?>" />
		</p>
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
	protected function select( $slug, $params = array(), $taxonomy = false, $multi = false, $value = null, $placeholder = '--' ) { ?>
		<p><?php
		if ( $taxonomy != false ): // if a taxonomy is set, use wp_dropdown category to generate the select box
			$IDs = wp_get_object_terms( get_the_ID(), $taxonomy, array( 'fields' => 'ids' ) );
			wp_dropdown_categories( 'taxonomy=' . $taxonomy . '&hide_empty=0&orderby=name&name=' . $taxonomy . '&show_option_none=Select ' . $taxonomy . '&selected='. array_pop($IDs) );
		else :	// otherwise use all the values set in $param to generate the option
			if ( $multi == true ):
				?> <select id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>" multiple>
			<?php
			else : ?>
				<select id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>">
				<?php
				if ( empty( $value ) ): ?>
					<option selected value=""><?php echo esc_html( $placeholder ) ?></option>
				<?php
				else : ?>
					<option value=""><?php echo esc_html( $placeholder ) ?></option>
					<option selected="selected" value="<?php echo esc_attr( $value ) ?>" ><?php echo esc_html( $value ) ?></option><?php
				endif;
			endif;

			foreach ( $params as $option ): ?>
				<option value="<?php echo esc_attr( $option ) ?>"><?php echo esc_html( $option ) ?></option>
			<?php
			endforeach;
		?>	</select> <?php
		endif;
		?>
	</p>
	<?php
	}

	protected function post_select( $slug, $posts, $value, $placeholder = '--' ) { ?>
		<p>
			<select id="<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>" >
				<?php if ( ! empty( $value ) ):
					if ( is_numeric( $value) ):
						$set_value = get_post($value)->post_title;
					elseif ( filter_var($value, FILTER_VALIDATE_URL) ):
						$id = url_to_postid( $value );
						$set_value = get_post($id)->post_title;
					else:
						$set_value = $value;
					endif;
					?>
					<option selected value="<?php echo esc_attr($value); ?>" ><?php echo esc_attr( $set_value ); ?></option>
					<option value=""><?php echo $placeholder; ?></option>
					<?php foreach ( $posts as $p ):
							if ( $p->ID != $value && ! empty( $p->post_name ) ): ?>
							<option value="<?php echo esc_attr( $p->ID ); ?>" ><?php echo esc_attr( $p->post_title ); ?></option>
					<?php endif; ?>
					<?php endforeach; ?>
				<?php else: ?>
					<option selected value=""><?php echo $placeholder; ?></option>
				<?php foreach ( $posts as $p ):
					if ( $p->ID != $value && ! empty( $p->post_name ) ): ?>
					<option value="<?php echo esc_attr( $p->ID ); ?>" ><?php echo esc_attr( $p->post_title ); ?></option>
				<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			</select>
		</p>
		<?php
	}

	protected function taxonomy_as_meta( $slug, $params, $taxonomy, $key, $placeholder = '--', $value ) {?>
		<select name='<?php echo esc_attr( $slug )?>'><?php
			if ( isset( $value ) ):?>
				<option selected value="<?php echo esc_attr( $value ) ?>" id="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $value ) ?></option><?php
			endif;?>
			<option value="" id="no_<?php echo esc_attr( $slug ) ?>" name="<?php echo esc_attr( $slug ) ?>"><?php echo esc_html( $placeholder ) ?></option><?php
			foreach ( $params as $term_id ):
				$term = get_term_by( $field = 'id', $value = strval( $term_id ), $taxonomy, $output = OBJECT, $filter = 'raw' ); ?>
				<option value="<?php echo esc_attr( $term->name ) ?>" name="<?php echo esc_attr( $slug ) ?>" class="<?php echo esc_attr( $term_id ) ?>"><?php echo esc_html( $term->name )  ?> (<?php echo esc_html( $term->count ) ?>)</option><?php
			endforeach;?>
		</select>
		</p><?php
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
	protected function date( $taxonomy, $tax_nice_name, $mutliples = false ) {?>
    <p>
	    <?php
			$tax_name = stripslashes( $taxonomy );
			global $post, $wp_locale;

			$month = NULL;
			$day   = NULL;
			$year  = NULL;

			?><select id="<?php echo esc_attr( $tax_name ) ?>_month" name="<?php echo esc_attr( $tax_name ) ?>_month"><option selected="selected" value='<?php echo esc_attr( $month ) ?>'>Month</option>
	    <?php
			for ( $i = 1; $i < 13; $i++ ) {
				?><option value="<?php echo esc_attr( $wp_locale->get_month( $i ) ) ?>"><?php echo sanitize_text_field( $wp_locale->get_month( $i ) )  ?></option>
	    <?php } ?>
	    </select>
	    <input id="<?php echo esc_attr( $tax_name ) ?>_day" type="text" name="<?php echo esc_attr( $tax_name ) ?>_day" value="<?php echo esc_attr( $day ) ?>" size="2" maxlength="2" placeholder="DD"/>
	    <input id="<?php echo esc_attr( $tax_name ) ?>_year" type="text" name="<?php echo esc_attr( $tax_name ) ?>_year" value="<?php echo esc_attr( $year ) ?>" size="4" maxlength="4" placeholder="YYYY"/>
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
	</p>
	<?php
	}
}
