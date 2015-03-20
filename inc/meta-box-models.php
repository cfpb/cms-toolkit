<?php
/**
* Metaboxes.php creates a custom metabox for the regulations taxonomy. Needs more
* modularity.
* 
**/
// On 'add_meta_boxes', rip out the old metaboxes and replace them with regulation_meta_box() (below).
namespace CFPB\Utils\MetaBox;
use \CFPB\Utils\Taxonomy as TaxUtils;
use \CFPB\Utils\MetaBox\View;
use \CFPB\Utils\MetaBox\Callbacks;
use \WP_Error as WP_Error;
use \DateTime;

class Models {
	public $title;
	public $slug;
	public $post_type;
	public $context;
	public $fields;
	public $priority;
	public $Callbacks; // obj A class containing other validation methods
	public $View; // obj A class containing template patterns
	public $error;
	private $selects = array(
		'select',
		'multiselect',
		'taxonomyselect',
		'tax_as_meta',
		'post_select',
		'post_multiselect'
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
		'wysiwyg',
	);
	private $other   = array( 'nonce', 'hidden', 'separator', 'fieldset', 'formset' );
	protected $supported_types = array(
		'application/pdf',
		'image/png',
		'image/gif',
		'image/jpeg',
		'video/jpeg',
		'text/csv',
		'application/zip',
		'application/msword',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/vnd.ms-excel',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/vnd.ms-powerpoint',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'application/json',
		'application/xml',
		'video/mpeg',
		'audio/mpeg',
		'text/html',
		'text/plain',
		'video/vnd.sealedmedia.softseal-mov',
		'text/tab-separated-values',
	);

	public function __construct() {
		$this->Callbacks = new Callbacks();
		$this->View      = new View();
		$this->priority  = 'default';
		if ( ! is_array($this->post_type) ) {
			$this->post_type = array($this->post_type);
		}
		$this->error = '\WP_Error';
	}

	/**
	 * The following three methods are dependency injection methods. They can be used
	 * to replace Utils\MetaBox\Callbacks and Utils\MetaBox\View with your own callback
	 * or view class. This is useful for unit testing purposes and for when you decide
	 * you don't like the methods we've given you and need to replace them for some 
	 * reason. Maybe you need different templates for something but don't think they
	 * should be contributed back, use these methods to replace our templates with your
	 * own.
	 *
	 * @param obj $Class The class you want to inject into this plugin
	 */
	public function set_callbacks( $Class ) {
		$this->Callbacks = $Class;
	}

	public function set_view( $Class ) {
		$this->View = $Class;
	}

	public function error_handler( $Class ) {
		$this->error = $Class;
	}

	public function check_post_type( $post_type ) {
		if ( post_type_exists( $post_type ) ) {
			$post_type = sanitize_key( $post_type );
		} else {
			$post_type = false;
		}
		return $post_type;
	}

	/**
	*
	* generate: Create a meta box based on a few parameters.
	*
	* This function allows developers with this plugin installed to easily
	* instantiate meta boxes into different edit screens of their WordPress
	* install. Inspired by Django forms. The generate() method parses parameters
	* into an objeect, the last property of which is passed to the build()
	* callback. Generating metaboxes is as simple as hooking this method into
	* the `add_meta_boxes` action hook.
	*
	* @since 1.0
	*
	* @uses wp_parse_args To determine the desired differences from defaults
	* @uses add_meta_box WordPress API To generate the metabox
	* @uses \CFPB\Utils\MetaBox\Template\HTML(); For generating form fields
	* @uses add_meta_box (WP Core) To instantiate the meta box
	* @uses $this->check_post_type To check whether the post type given in
	*       $this->post_type exists
	* @uses \WP_Error Error handling is done with WordPress if invalid contexts or
	*        post types are supplied
	**/
	public function generate( ) {
		$parts = array( 'normal', 'advanced', 'side', );
		if ( ! in_array( $this->context, $parts ) ) {
			$error = new $this->error( 'context', __( 'Invalid context: ' . $this->context ) );
			echo $error->get_error_message('context');
			return;
		}

		$fields = $this->fields;
		$post_types = $this->post_type;
		foreach ( $post_types as $p ) {
			$exists = $this->check_post_type($p);
			if ( $exists != false ) {
				add_meta_box(
					$id = $this->slug,
					$title = $this->title,
					$callback = array( $this->View, 'ready_and_print_html' ),
					$post_type = $p,
					$context = $this->context,
					$priority = $this->priority,
					$callback_args = $fields
				);
			} else {
				$error = new $this->error( 'post_type', 'Invalid post type: ' . $p);
				echo $error->get_error_message('post_type');
			}
		}
	}

	/**
	* validate_formset validates a formset
	*
	* @param  arr  $field     The formset to validate.
	* @param  arr  $validate  The array that holds all the data to save in an
	*                         associative array that is passed by reference.
	* @param  arr  $post_ID   The post's ID
	*
	* @return  void           No return value. The validate array is passed by reference
	*                         so data is saved through that array.
	*/

	public function validate_formset( $field, &$validate, $post_ID ) {
		for ( $i = 0; $i < $field['params']['max_num_forms']; $i++ ) {
			$processed[$i] = $field;
			if ( isset( $processed[$i]['meta_key'] ) ) {
				$processed[$i]['meta_key'] .= '_' . $i;
			}
			if ( isset( $processed[$i]['slug'] ) ) {
				$processed[$i]['slug'] .= '_' . $i;
			}
			foreach ( $processed[$i]['fields'] as $f ) {
				if ( isset( $processed[$i]['meta_key'] ) and isset( $f['meta_key'] ) ) {
					$f['meta_key'] = "{$processed[$i]['meta_key']}_{$f['meta_key']}";
				}
				if ( isset( $processed[$i]['slug'] ) and isset( $f['slug'] ) ) {
					$f['slug'] = "{$processed[$i]['slug']}_{$f['slug']}";
				}
				$this->validate( $post_ID, $f, $validate );
			}
		}
	}

	/**
	* validate_fieldset validates a fieldset
	*
	* @param  arr  $field     The formset to validate.
	* @param  arr  $validate  The array that holds all the data to save in an
	*                         associative array that is passed by reference.
	* @param  arr  $post_ID   The post's ID
	*
	* @return  void           No return value. The validate array is passed by reference
	*                         so data is saved through that array.
	*/

	public function validate_fieldset( $field, &$validate, $post_ID ) {
		foreach ( $field['fields'] as $f ) {
			if ( isset( $field['meta_key'] ) and isset( $f['meta_key'] ) ) {
				$f['meta_key'] = "{$field['meta_key']}_{$f['meta_key']}";
			}
			if ( isset( $field['slug'] ) and isset( $f['slug'] ) ) {
				$f['slug'] = "{$field['slug']}_{$f['slug']}";
			}
			$this->validate( $post_ID, $f, $validate );
		}
	}

	/**
	 * validate_link validates a field with type = 'link'
	 *
	 * @param  arr $field   The field to validate, normally passed by looping through
	 *                      $this->fields
	 * @param  int $post_ID The post ID to have post values saved to
	 * @return void         No return value, updates or adds post meta if successful. New
	 *                      metadata are saved to the post as an array of the form
	 *                      array(0 => 'url', 1 => 'text')
	 */
	public function validate_link( $field, $post_ID ) {
		if ( isset( $_POST["{$field['meta_key']}_url"] )
		 and isset( $_POST["{$field['meta_key']}_text"] ) ) {
			$url = $_POST["{$field['meta_key']}_url"];
			$text = $_POST["{$field['meta_key']}_text"];
			$full_link = array( 0 => $url, 1 => $text );
			$existing = get_post_meta( $post_ID, $field['meta_key'], $single = false );

			if ( empty( $_POST["{$field['meta_key']}_url"] )
			  or empty( $_POST["{$field['meta_key']}_text"] ) ) {
				delete_post_meta( $post_ID, $field['meta_key']);
			} elseif ( empty($existing) ) {
				add_post_meta( $post_ID, $field['meta_key'], $url, false );
				add_post_meta( $post_ID, $field['meta_key'], $text, false );
			} elseif ( $existing != $full_link ) {
				update_post_meta( $post_ID, $field['meta_key'], $url, $existing[0] );
				update_post_meta( $post_ID, $field['meta_key'], $text, $existing[1] );
			}
		}
	}

	/**
	 * validate_select validates a <select> field
	 *
	 * @param  arr $field   The field to validate, normally passed by looping through
	 *                      $this->fields
	 * @param  int $post_ID The post ID to have post values saved to
	 * @return void         No return value, adds or deletes post meta if successful. New
	 *                      data are saved to the post as new values in an array or
	 *                      deleted.
	 */

	public function validate_select( $field, $post_ID ) {
		if ( !isset( $_POST[$field['meta_key']] ) ) {
			delete_post_meta( $post_ID, $field['meta_key']);
			return;
		}
		if ( array_key_exists($field['meta_key'], $_POST) ) {
			$existing = get_post_meta( $post_ID, $field['meta_key'], false );
			$data = $_POST[$field['meta_key']];
			foreach ( (array)$data as $d ) {
				// Adding or updating terms
				$term = sanitize_text_field( $d );
				$e_key = array_search($term, $existing);
				if ( ! in_array($d, (array)$existing) ) {
					// if the term is not in $existing, it's a new term, add it
					// we use add_post_meta instead of update so we can have more
					// than one value on the array
					add_post_meta( $post_ID, $field['meta_key'], $term );
				}
			}
			// delete terms if they're not in the $_POST data
			foreach ( (array)$existing as $e ) {
				if ( ! in_array($e, (array)$data) ) {
					delete_post_meta( $post_ID, $field['meta_key'], $meta_value = $e );
				}
			}
		}
	}

	public function validate_taxonomyselect($field, $post_ID) {
		$field['multiple'] = isset( $field['multiple'] ) ? $field['multiple'] : false;
		if ( isset($_POST[$field['slug']] )) {
			$term = sanitize_text_field( $_POST[$field['slug']] );
			$term_exists = get_term_by('id', $term, $field['taxonomy']);
			if ( $term_exists ){
				wp_set_object_terms(
					$post_ID,
					$term_exists->name,
					$field['taxonomy'],
					$append = $field['multiple']
				);
			} else {
				wp_set_object_terms(
					$post_ID,
					$term,
					$field['taxonomy'],
					$append = $field['multiple']
				);
			}
		}
	}

	/** 
	 * Validates a date, time, or datetime field by converting $_POST keys into date strings before passing
	 * to a date method in $this->Callbacks->date()
	 * 
	 * @param  array $field    The field to be processed
	 * @param  [type] $post_ID The ID of the object to be manipulated
	 * @return void
	 */

	public function validate_datetime($field, $post_ID) {
		$terms = wp_get_post_terms( $post_ID, $field['taxonomy'], array( 'fields' => 'ids' ) );
		$terms_to_remove = array();
		for ( $i = 0; $i < count( $terms ); $i++ ) {
			if ( isset( $_POST['rm_' . $field['taxonomy'] . '_' . $i ] ) ) {
				array_push( $terms_to_remove, $i );
			}
		}
		foreach ( $terms_to_remove as $t ) {
			$this->Callbacks->date( $post_ID, $field['taxonomy'], $multiple = $field['multiple'], null, $t );
		}
		$data = array($field['taxonomy'] => '');
		if ( $field['type'] != 'date') {
			$hour = $field['taxonomy'] . '_hour';
			$minute = $field['taxonomy'] . '_minute';
			$ampm = $field['taxonomy'] . '_ampm';
			$timezone = $field['taxonomy'] . '_timezone';
			if ( isset($_POST[$hour]) ) {
				$data[$field['taxonomy']] .= $_POST[$hour][0];
			}
			if ( isset( $_POST[$minute] ) ) {
				$data[$field['taxonomy']] .= ':' . $_POST[$minute][0];
			}
			if ( isset( $_POST[$ampm] ) ) {
				$data[$field['taxonomy']] .= $_POST[$ampm][0];
			}
			if ( isset( $_POST[$timezone] ) ) {
				$data[$field['taxonomy']] .= ' ' . $_POST[$timezone][0];
			}
		}
		if ( $field['type'] == 'datetime' ) {
			$data[$field['taxonomy']] .= ' ';
		}
		if ( $field['type'] != 'time') {
			$month = $field['taxonomy'] . '_month';
			$day = $field['taxonomy'] . '_day';
			$year = $field['taxonomy'] . '_year';
			if ( isset($_POST[$month]) ) {
				$data[$field['taxonomy']] .= $_POST[$month];
			}
			if ( isset( $_POST[$day] ) ) {
				$data[$field['taxonomy']] .= ' ' . $_POST[$day];
			}
			if ( isset( $_POST[$year] ) ) {
				$data[$field['taxonomy']] .= ' ' . $_POST[$year];
			}
		}
		if ( $field['type'] == 'time' ) {
			$date = DateTime::createFromFormat('h:ia T', $data[$field['taxonomy']]);
		} elseif ( $field['type'] == 'date' ) {
			$date = DateTime::createFromFormat('F j Y', $data[$field['taxonomy']]);
		} elseif ( $field['type'] == 'datetime' ) {
			$date = DateTime::createFromFormat('h:ia T F j Y', $data[$field['taxonomy']]);
		}
		if ( $field['type'] != 'date') {
			$this->save( $post_ID, array( $field['taxonomy'] . '_timezone' => $_POST[$field['taxonomy'] . '_timezone'] ) );
		}
		if ( $date ) {
			$this->Callbacks->date( $post_ID, $field['taxonomy'], $multiple = $field['multiple'], $data, null );
		}
	}

	public function validate_file( $field, $post_ID ) {
		if ( isset( $_POST['rm_' . $field['meta_key']] ) and !empty( $_POST['rm_' . $field['meta_key']] ) ) {
			$file = get_post_meta( $post_ID, $_POST['rm_' . $field['meta_key']], true );
			if ( unlink( $file['file'] ) ) {
				delete_post_meta( $post_ID, $field['meta_key'] );
			} else {
				wp_die('There was an error trying to delete your file.');
			}
		}
		if ( !empty( $_FILES[$field['meta_key']] ) and !empty( $_FILES[$field['meta_key']]['name'] ) ) {
			$arr_file_type = wp_check_filetype( basename( $_FILES[$field['meta_key']]['name'] ) );
			$uploaded_type = $arr_file_type['type'];
			if ( in_array( $uploaded_type, $this->supported_types ) ) {
				$upload = wp_upload_bits( $_FILES[$field['meta_key']]['name'], null, file_get_contents( $_FILES[$field['meta_key']]['tmp_name'] ) );
				if ( $upload['error'] ) {
					wp_die( 'File upload error: ' . $upload['error'] );
				} else {
					$upload['name'] = $_FILES[$field['meta_key']]['name'];
					update_post_meta( $post_ID, $field['meta_key'], $upload );
				}
			}
			else {
				wp_die( 'File upload error: Unsupported type ' . $uploaded_type );
			}
		}
	}

	/**
	 * Checks that data is coming through in the types we expect or ferries out form 
	 * data to the appropriate validator. Run before save to ensure you're saving
	 * correct data. Essentially this takes in $_POST and pushes all the good stuff from
	 * $_POST into a separate, cleaned array and returns the cleaned data.
	 *
	 * @param int $post_ID The post targeted for custom data
	 * 
	 * @return array A version of $_POST with cleaned data ready to be sent to a save method
	 *               like $this->save()
	 */
	public function validate( $post_ID, $field, &$validate ) {
		if ( isset( $field['do_not_validate'] ) ) {
			return;
		} elseif ( ! isset( $field['type'] ) ) {
			$error = new $this->error( 'no_type', __( 'No field type set.' ) );
			echo $error->get_error_message('no_type');
			return;
		}
		if ( isset( $field['meta_key'] ) ) {
			$key = $field['meta_key'];
		} elseif ( isset( $field['slug'] ) ) {
			$key = $field['slug'];
		} elseif ( isset( $field['taxonomy'] ) ) {
			$key = $field['taxonomy'];
		} else {
			$error = new $this->error( 'no_slug', __( 'No meta_key/slug/taxonomy is set.' ) );
			echo $error->get_error_message('no_slug');
			return;
		}
		/* if this field is a formset, fieldset, taxonomy select, date, link or 
		   select field, we send it out to another validator
		*/
		if ( $field['type'] == 'formset' ) {
		   if ( isset( $field['params'] ) ) {
				if ( isset( $field['params']['max_num_forms'] ) ) {
					if ( ! is_numeric( $field['params']['max_num_forms'] ) ) {
						$error = new $this->error( 'formset_params_max_num',
									__( "The 'max_num_forms' in params array for the"
										. "formset field needs to be an integer." ) );
						return $error->get_error_message('formset_params_max_num');
					}
				} else {
					$field['params']['max_num_forms'] = 1;
				}            
			} else {
				$error = new $this->error( 'formset_params',
									__( "There must be a params array set in the field"
										 . "for a formset." ) );
				return $error->get_error_message('formset_params');
			}
			$this->validate_formset( $field, $validate, $post_ID );
			return;
		} elseif ( $field['type'] == 'fieldset' ) {
			$this->validate_fieldset( $field, $validate, $post_ID );
			return;
		} elseif ( $field['type'] == 'taxonomyselect') {
			if ( ! isset( $field['taxonomy'] ) ) {
				$error = new $this->error( 'no_taxonomy', __( 'No taxonomy set'
										   . ' for field that requires it.' ) );
				echo $error->get_error_message('no_taxonomy');
				return;
			}
			$this->validate_taxonomyselect( $field, $post_ID );
			return;
		} elseif ( in_array( $field['type'], $this->selects ) ) {
			if ( ! isset( $field['meta_key'] ) ) {
				$error = new $this->error( 'no_meta_key', __( 'No meta_key is set.' ) );
				echo $error->get_error_message('no_meta_key');
				return;
			}
			$this->validate_select( $field, $post_ID );
			return;
		} elseif ( $field['type'] == 'date' or $field['type'] == 'time' or $field['type'] == 'datetime' ) {
			if ( ! isset( $field['taxonomy'] ) ) {
				$error = new $this->error( 'no_taxonomy', __( 'No taxonomy set'
										   . ' for field that requires it.' ) );
				echo $error->get_error_message('no_taxonomy');
				return;
			}
			$this->validate_datetime( $field, $post_ID );
			return;
		} elseif ( $field['type'] == 'link' ) {
			$this->validate_link($field, $post_ID);
			return;
		} elseif ( $field['type'] == 'file' ) {
			$this->validate_file( $field, $post_ID );
			return;
		} else {
			/* 
				For most field types we just need to make sure we have the data
				we expect from the form and sanitize them before sending them to
				save
			*/
			if ( ! isset( $_POST[$key] ) ) {
				return;
			}
			$value = $_POST[$key];
			if ( $field['type'] == 'number' ) {
				if ( is_numeric( $value ) ) {
					// if we're expecting a number, make sure we get a number
					$value = intval( $value ); 
				} else {
					$value = null;
				}
			} elseif ( $field['type'] == 'url' && isset( $value ) ) {
				// if we're expecting a url, make sure we get a url
				$value = esc_url_raw( $value ); 
			} elseif ( $field['type'] == 'email' ) {
				// if we're expecting an email, make sure we get an email
				$value = sanitize_email( $value ); 
			} elseif ( ! empty( $value ) && ! is_array($value ) ) {
				// make sure whatever we get for anything else is a string
				$value = (string)$value;
			}
		}
		$validate[$key] = $value;
	}

	/**
	 * Takes cleaned $_POST data and save it as custom post meta
	 *
	 * @param  int $post_ID      The post we save data to
	 * @param  array $postvalues Cleaned version of $_POST or some other array of trusted
	 *                           data to be saved
	 * @return nothing           Either deletes or updates post meta or returns empty
	 */
	public function save( $post_ID, $postvalues ) {
		// Do nothing if we're auto saving
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

		if ( empty( $postvalues ) ) {
			// if we're passed an empty array, don't do anything
			return;
		}

		// save post data for any fields that sent them
		foreach ( $postvalues as $key => $value ) {
			$existing = get_post_meta( $post_ID, $key, $single = true );
			if ( $value == null && isset( $existing ) ) {
				delete_post_meta( $post_ID, $key );
			} elseif ( isset( $value ) ) {
				update_post_meta( $post_ID, $meta_key = $key, $meta_value = $value );
			} else {
				return;
			}
		}
	}
	/**
	 * Runs validate, then save on $_POST data
	 *
	 * @param  int $post_ID The id of the object we're saving
	 * @return void
	 */
	public function validate_and_save( $post_ID ) {
		$validate = array();
		if ( empty( $this->fields ) ) {
			$error = new $this->error( 'empty_fields', __( 'Empty fields array' ) );
			echo $error->get_error_message('empty_fields');
			return;
		}
		foreach ( $this->fields as $field ) {
			$this->validate( $post_ID, $field, $validate );
		}
		$this->save( $post_ID, $validate );
	}
}
