<?php
namespace CFPB\Utils\MetaBox;
use \CFPB\Utils\Taxonomy;
use \CFPB\Utils\MetaBox\Models;
use \DateTime;

class Callbacks {
	public $taxonomy;
	function __construct() {
		$this->Taxonomy = new Taxonomy();
	}
	public function replace_Taxonomy($Taxonomy) {
		$this->Taxonomy = $Taxonomy;
	}
	
	/**
	 * Generate a callback for date-type meta boxes created with date_metabox().
	 *
	 * This function generates a standardized callback for taxonomy meta boxes created with the date_metabox() function
	 * included with this plugin (see above). The callback takes input parameters from $_POST, sanitizes them, then
	 * stores them hierarchically as:
	 *
	 *      Year (parent)
	 *      ---- Month (child)
	 *      ---- ---- Day (child)
	 *
	 * This function will not work properly if the target taxonomy was not registered with hierarchical=true, it will also
	 * or any taxonomies that have not had their default metaboxes replaced with date_metabox();
	 *
	 * @see date_meta_box() This function relies on POST data passed from a meta box created with this.
	 *
	 * @uses wp_insert_term() Used to add the term to the database.
	 * @uses get_term_by() Used to get the object of terms stored with wp_insert_term().
	 * @uses wp_set_object_terms Used to attach the newly created terms to the post.
	 * @param int     $post_id,   probably leave this as is. This is the ID of the post to which the terms should attach
	 * @param string  $taxonomy,  The taxonomy to which the terms belong
	 * @param boolean $multiples, Determines whether the term shoud append (true) or replace (false) existing terms
	 * @return identical to wp_set_object_terms
	 */
	public function date( $post_id, $taxonomy, $multiples = false, $date = array(), $timezone = null, $term_num = null ) {
		global $post;
		$rmTerm = 'rm_' . $taxonomy . '_' . $term_num;
		if ( isset( $_POST[$rmTerm] ) and !empty( $_POST[$rmTerm] ) ) {
			$tounset = get_term_by( 'name', $_POST[$rmTerm], $taxonomy );
			if ( $tounset ) {
				$this->Taxonomy->remove_post_term( $post_id, $tounset->term_id, $taxonomy );
			}
		} elseif ( ! empty( $date ) ) {
			wp_set_object_terms( $post_id, $date->format('c'), $taxonomy, $append = $multiples );
			if ( isset( $timezone ) ) {
				$term = get_term_by( 'name', $date->format('c'), $taxonomy );
				wp_update_term( $term->term_id, $taxonomy, $args = array( 'description' => $timezone ) );
			}
		}
	}
}
