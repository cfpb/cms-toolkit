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
	private $other   = array( 'nonce', 'hidden', 'separator', 'fieldset' );
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
	* validate_repeated_field validates a field that repeats
	*
	* @param  arr  $post_ID    The post's ID
	* @param  arr  $field      The repeated field to validate.
	* @param  arr  $validated  The array that holds all the data to save in an
	*                           associative array that is passed by reference.
	* @param  mixed  $saved    The data that is associated with the fields
	*
	* @return  void            No return value. The validate array is passed by
								reference so data is saved through that array.
	*/

	public function validate_repeated_field( $post_ID, &$field, &$validated, $saved ) {
		$validated = array();
		$params = $field['params']['repeated'];
		unset( $field['params']['repeated'] );
		for ( $i = 0; $i < $params['max']; $i++ ) {
			$processed[$i] = $field;
			$processed[$i]['key'] .= '_' . $i;
			$validated[$i] = null;
			$saved_field = ( $saved and isset( $saved[$i] ) ) ? $saved[$i] : null;
			$this->validate( $post_ID, $processed[$i], $validated[$i], $saved_field );
			if ( empty( $validated[$i] ) ) {
				unset( $validated[$i] );
			}
		}
		if ( empty( $validated ) ) {
			unset( $validated );
		} else {
			$validated = array_values( $validated );
		}
		$field['fields'] = $processed;
	}

	/**
	* validate_fieldset validates a fieldset
	*
	* @param  arr  $post_ID    The post's ID
	* @param  arr  $field      The fieldset to validate.
	* @param  arr  $validated  The array that holds all the data to save in an
	*                              associative array that is passed by reference.
	* @param  mixed  $saved    The data that is associated with the fields
	*
	* @return  void            No return value. The validate array is passed by reference
	*                              so data is saved through that array.
	*/

	public function validate_fieldset( $post_ID, &$field, &$validated, $saved ) {
		$validated = array();
		foreach ( array_keys( $field['fields'] ) as $key ) {
			$field_key = Models::validate_keys( $field['fields'][$key] );
			$field['fields'][$key]['old_key'] = $field_key;
			$field['fields'][$key]['key'] = "{$field['key']}_{$field_key}";
			$validated[$field_key] = null;
			$saved_field = ( $saved and isset( $saved[$field_key] ) ) ? $saved[$field_key] : null;
			$this->validate( $post_ID, $field['fields'][$key], $validated[$field_key], $saved_field );
			if ( ! $validated[$field_key] ) {
				unset( $validated[$field_key] );
			}
		}
		if ( empty( $validated ) ) {
			unset( $validated );
		}
	}

	/**
	 * validate_link validates a field with type = 'link'
	 *
	 * @param string $key         The key of the $_POST array
	 * @param array  $validated   The array to hold the data to be saved
	 */
	public function validate_link( $key, &$validated ) {
		if ( isset( $_POST["{$key}_label"] )
		 and isset( $_POST["{$key}_url"] ) ) {
			$label = $_POST["{$key}_label"];
			$url = $_POST["{$key}_url"];
			$full_link = array( 'label' => $label, 'url' => $url );

			$validated = ( empty( $label ) or empty( $url ) ) ? "" : $full_link;
		}
	}

	/**
	 * validate_select validates a <select> field
	 *
	 * @param string $key       The key of the $_POST array
	 * @param array $validated  The array to hold the data to be saved
	 */

	public function validate_select( $key, &$validated ) {
		if ( isset( $_POST[$key] ) ) {
			$validated = $_POST[$key];
		}
	}

	public function validate_taxonomyselect( $post_ID, $field, $key ) {
		$field['multiple'] = isset( $field['multiple'] ) ? $field['multiple'] : false;
		if ( isset($_POST[$key] )) {
			$term = sanitize_text_field( $_POST[$key] );
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
	 * @param int   $post_ID   The ID of the object to be manipulated
	 * @param array $field     The field to be processed
	 * @param array $validated The array to hold the data to be saved
	 * @return void
	 */

	public function validate_datetime( $post_ID, $field, &$validated ) {
		$terms = wp_get_post_terms( $post_ID, $field['taxonomy'], array( 'fields' => 'ids' ) );
		$terms_to_remove = array();
		for ( $i = 0; $i < count( $terms ); $i++ ) {
			if ( isset( $_POST['rm_' . $field['key'] . '_' . $i ] ) ) {
				array_push( $terms_to_remove, $i );
			}
		}
		foreach ( $terms_to_remove as $t ) {
			$this->Callbacks->date( $post_ID, $field['taxonomy'], $multiple = $field['multiple'], null, null, $t );
		}
		$data = array($field['key'] => '');
		if ( $field['type'] != 'time') {
			$month = $field['key'] . '_month';
			$day = $field['key'] . '_day';
			$year = $field['key'] . '_year';
			if ( isset($_POST[$month]) ) {
				$data[$field['key']] .= $_POST[$month];
			}
			if ( isset( $_POST[$day] ) ) {
				$data[$field['key']] .= ' ' . $_POST[$day];
			}
			if ( isset( $_POST[$year] ) ) {
				$data[$field['key']] .= ' ' . $_POST[$year];
			}
		}
		if ( $field['type'] == 'datetime' ) {
			$data[$field['key']] .= ' ';
		}
		if ( $field['type'] != 'date') {
			$hour = $field['key'] . '_hour';
			$minute = $field['key'] . '_minute';
			$ampm = $field['key'] . '_ampm';
			$timezone = $field['key'] . '_timezone';
			if ( isset($_POST[$hour]) ) {
				$data[$field['key']] .= $_POST[$hour][0];
			}
			if ( isset( $_POST[$minute] ) ) {
				$data[$field['key']] .= ':' . $_POST[$minute][0];
			}
			if ( isset( $_POST[$ampm] ) ) {
				$data[$field['key']] .= $_POST[$ampm][0];
			}
			if ( isset( $_POST[$timezone] ) ) {
				$data[$field['key']] .= ' ' . $_POST[$timezone][0];
			}
		}
		$timezone = $_POST[$field['key'] . '_timezone'][0];
		if ( $field['type'] == 'date' ) {
			$date = DateTime::createFromFormat('F j Y', $data[$field['key']]);
		} else {
			if ( isset( $_POST[$field['key'] . '_timezone'] ) and
				 is_array( $_POST[$field['key'] . '_timezone'] ) and
				 !empty( $_POST[$field['key'] . '_timezone'][0] ) ) {
				
				date_default_timezone_set( $_POST[$field['key'] . '_timezone'][0] );
			}
			if ( $field['type'] == 'time' ) {
				$date = DateTime::createFromFormat('h:ia T', $data[$field['key']]);
			} elseif ( $field['type'] == 'datetime' ) {
				$date = DateTime::createFromFormat('F j Y h:ia T', $data[$field['key']]);
			}
		}
		if ( $date ) {
			if ( $field['key'] == $field['taxonomy'] ) {
				$this->Callbacks->date( $post_ID, $field['taxonomy'], $multiple = $field['multiple'], $date, $timezone, null );
			}
			$validated = array( 'date' => $date->format( 'c' ), 'timezone' => $timezone );
		}
	}

	public function validate_file( $post_ID, $field, &$validated, $saved ) {
		if ( isset( $field['allowed_file_types'] ) and 
			( is_array( $field['allowed_file_types'] ) and !empty( $field['allowed_file_types'] ) ) ) {
			foreach ( $field['allowed_file_types'] as $type ) {
				if ( in_array( $type, $this->supported_types ) ) {
					$supported_types[] = $type;
				} else {
					wp_die( $type . ' is not a supported type.' );
				}
			}
		} else {
			$supported_types = $this->supported_types;
		}
		if ( isset( $_POST['rm_' . $field['key']] ) and !empty( $_POST['rm_' . $field['key']] ) ) {
			if ( get_post( $saved['id'] ) ) {
				if ( ! wp_delete_attachment( $saved['id'], $force_delete = false ) ) {
					wp_die('There was an error trying to delete your file.');
				}
			}
		}
		if ( !empty( $_FILES[$field['key']] ) and !empty( $_FILES[$field['key']]['name'] ) ) {
			$arr_file_type = wp_check_filetype( basename( $_FILES[$field['key']]['name'] ) );
			$uploaded_type = $arr_file_type['type'];
			if ( in_array( $uploaded_type, $supported_types ) ) {
				$attachment_id = media_handle_upload( $field['key'], $post_ID );
				if ( is_wp_error( $attachment_id ) ) {
					wp_die( 'File upload error. WP_Error: ' . print_r( $attachment_id ) );
				} else {
					$attachment = get_post( $attachment_id );
					$url = parse_url( $attachment->guid );
					$validated = array(
						'id' =>   $attachment_id,
						'name' => $_FILES[$field['key']]['name'],
						'url' =>  $url['path']
					);
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
	 * $_POST into a separate, cleaned array.
	 *
	 * @param int $post_ID   The post targeted for custom data
	 * @param int $field     The field to be validated
	 * @param int $validated The array that holds the data to save
	 * @param int $saved     The data that is already associated with the field
	 * 
	 */
	public function validate( $post_ID, &$field, &$validated, $saved = NULL ) {
		if ( isset( $field['do_not_validate'] ) ) {
			return;
		} elseif ( ! isset( $field['type'] ) ) {
			wp_die("No field type set.");
		}

		// Special fields require external validators
	   if ( isset( $field['params'] ) and isset( $field['params']['repeated'] ) ) {
	   		$this->View->process_repeated_field_params( $field );
			$this->validate_repeated_field( $post_ID, $field, $validated, $saved );

		} elseif ( $field['type'] == 'fieldset' ) {
			$this->validate_fieldset( $post_ID, $field, $validated, $saved );

		} elseif ( $field['type'] == 'taxonomyselect') {
			if ( ! isset( $field['taxonomy'] ) ) {
				wp_die( "No taxonomy set for field that requires it.");
			}
			$this->validate_taxonomyselect( $post_ID, $field, $field['key'] );

		} elseif ( in_array( $field['type'], $this->selects ) ) {
			$this->validate_select( $field['key'], $validated );

		} elseif ( $field['type'] == 'date' or $field['type'] == 'time' or $field['type'] == 'datetime' ) {
			if ( ! isset( $field['taxonomy'] ) ) {
				wp_die( "No taxonomy set for field that requires it.");
			}
			$this->validate_datetime( $post_ID, $field, $validated );

		} elseif ( $field['type'] == 'link' ) {
			$this->validate_link( $field['key'], $validated );

		} elseif ( $field['type'] == 'file' ) {
			$this->validate_file( $post_ID, $field, $validated, $saved );

		} else {
			/* 
				For most field types we just need to make sure we have the data
				we expect from the form and sanitize them before sending them to
				save
			*/
			if ( ! isset( $_POST[$field['key']] ) ) {
				/*
					Unchecked checkboxes that were previously checked
					need the key/slug to be manually added to the
					$_POST global to be properly saved.
				*/
				if ( $field['type'] == 'boolean' ) {
					if ( $field['key'] == $field['old_key'] ) {
						$_POST[$field['key']] = "";
					} else {
						$_POST[$field['key']] = null;
					}
				} else {
					return;
				}
			}
			$value = $_POST[$field['key']];
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
			$validated = $value;
		}
	}

	public static function validate_keys( $field ) {
		$field_key = null;
		if ( isset( $field['key'] ) ) {
			$field_key = $field['key'];
		} elseif ( isset( $field['meta_key'] ) ) {
			$field_key = $field['meta_key'];
		} elseif ( isset( $field['slug'] ) ) {
			$field_key = $field['slug'];
		} elseif ( isset( $field['taxonomy'] ) ) {
			$field_key = $field['taxonomy'];
		} else {
			wp_die( 'No meta_key/slug/taxonomy is set.' );
		}
		return $field_key;
	}

// added to delete old-formatted data for phasing out backwards compatibility
public function delete_old_data( $post_ID, $fields ) {
	foreach ( $fields as $field ) {
		if ( ! isset( $field['fields'] ) ) {
			delete_post_meta( $post_ID, $field['key'] );
		} else {
			foreach ( array_keys($field['fields']) as $key ) {
				$this->delete_old_data( $post_ID, array( $field['fields'][$key] ) );
			}
		}
	}
}

	/**
	 * Takes cleaned $_POST data and save it as custom post meta
	 *
	 * @param  int $post_ID      The post we save data to
	 * @param  array $postvalues Cleaned version of $_POST or some other array of trusted
	 *                           data to be saved
	 * @return nothing           Either deletes or updates post meta or returns empty
	 */
	public static function save( $post_ID, $postvalues ) {
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
			if ( isset( $value ) ) {
				update_post_meta( $post_ID, $key = $key, $meta_value = $value );
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
		if ( empty( $this->fields ) ) {
			wp_die("Empty fields array");
		}

		// create an array that will be saved
		$validated = array();

		// duplicate metabox's fields to modify to match with $_POST array's keys
		$saved = array();

		foreach ( array_keys( $this->fields ) as $key ) {
			
			//verify meta_key/slug/taxonomy and assign keys
			$this->fields[$key]['old_key'] = Models::validate_keys( $this->fields[$key] );
			$this->fields[$key]['key'] = $this->fields[$key]['old_key'];
			
			//create keys in array that are to be saved
			$validated[$this->fields[$key]['old_key']] = "";

			// retrieve saved data
			$saved[$this->fields[$key]['old_key']] = get_post_meta( $post_ID, $this->fields[$key]['old_key'], $single = true );

			//start validation
			$this->validate( $post_ID, $this->fields[$key], $validated[$this->fields[$key]['old_key']], $saved[$this->fields[$key]['old_key']] );
		}
		// delete old formatted data for phasing out backwards compatibility
		$this->delete_old_data( $post_ID, $this->fields );

		Models::save( $post_ID, $validated );
	}
}
