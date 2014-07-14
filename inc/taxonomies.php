<?php
/**
* taxonomies.php set of functions for creating and managing taxonomies
* 
*
**/
namespace CFPB\Utils;
class Taxonomy {
	public $name;
	public $plural;
	public $slug;
	public $post_type;
	public $args = array();

/**
 *
 *
 * @since 0.1.0
 * Create a custom taxonomy with many standard defaults.
 *
 * This plugin eases the pain of having to type the name of your taxonomy some 13 times just to fill in the labels field.
 * It takes four required parameters and returns a registered taxonomy. You must manually call flush_rewrite_rules in
 * order for the urls to update.
 *
 * @uses register_taxonomy to register the taxonomy.
 *
 * @param unknown $name                  str the singular natural name of the taxonomy like 'Taxonomy'
 * @param unknown $plural                str the plural version of $name
 * @param unknown $post_type             str|array the post types that may accept this taxonomy
 * @param unknown $slug                  str the name of the taxonomy as it will be registered and queried in the database
 *
 * The following parameters are optional and correspond directly to args of the same name in register_taxonomy()
 * @see register_taxonomy()
 * @param unknown $hierarchical          bool whether this taxonomy accepts child terms, like a category, or not. Default 'false'
 * @param unknown $query_var             bool default 'false'
 * @param unknown $rewrite               bool default 'true'
 * @param unknown $public_val            bool default 'true'
 * @param unknown $delim_message         str corresponds to 'separate_items_with_commas' default 'Separate with commas.'
 * @param unknown $show_ui               default bool default NULL
 * @param unknown $show_in_nav_menus     bool default NULL
 * @param unknown $show_tag_cloud        bool default NULL
 * @param unknown $show_admin_column     bool default 'false'
 * @param unknown $update_count_callback bool default NULL
 *
 */
	function build_taxonomy( $name, $plural, $slug, $post_type, $args = array() ) {
		$labels = array(
			'name' => $plural,
			'singular_name' => $name,
			'all_items' => 'All ' . $name,
			'edit_item' => 'Edit ' . $name,
			'view_item' => 'View ' . $name,
			'update_item' => 'Update ' . $name,
			'new_item_name' => 'New ' . $name,
			'add_new_item' => 'Add new ' . $name,
			'search_items' => 'Search ' . $plural,
			'popular_items' => 'Popular '. $plural,
			'add_or_remove_items' => 'Add or remove ' . $plural,
			'choose_from_most_used' => 'Choose from the most used ' . $plural,
			'not_found' => 'No '. $plural . ' found. Create one!',
		);
		$defaults = array(
			'labels' => $labels,
			'hierarchical'          => false,
			'query_var'             => true,
			'rewrite'               => true,
			'public'                => true,
			'show_admin_column'     => false,
			'update_count_callback' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		register_taxonomy( $slug, $post_type, $args );
	}

	/**
	 * Delete the object of the term from a given taxonomy.
	 *
	 * @uses get_term() is used to get all the term available in the datbase of given taxonomy.
	 *
	 * @return wp_get_object_terms() return a list of all given taxonomy terms which are applied to $post_id
	 * @return wp_set_object_terms() set the term to $new_terms variable.
	 */
	static function remove_post_term( $post_id, $term, $taxonomy ) {

		if ( ! is_numeric( $term ) ) {
			// if a slug is given, get the ID from the slug
			$term = get_term_by( 'slug', $term, $taxonomy );
			if ( is_wp_error( $term ) )
				return new WP_Error( '_invalid_term_', 'The term ' . $term . ' does not exist.' );
			$term = $term->term_id;
		}

		// Get the existing terms and only keep the ones we don't want removed
		$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );

		if ( in_array( $term, $terms ) ) {
			$index = array_search( $term, $terms );
			unset( $terms[$index] );
		}

		return wp_set_object_terms( $post_id, $terms, $taxonomy );
	}
}