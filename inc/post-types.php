<?php
/**
* post-types.php functions for building and manipluating post types
*
**/

/**
 * Build a post type with standardized parameters.
 *
 * @since 0.1.0
 *
 * This function fills in the $args and $labels arrays of register_post_type with default values and automated inputs.
 * It saves you the pain of having to type the name of the post type 13 times and registers post types the same way
 * each time. Unlike build_cfpb_taxonomy() this function _does not_ flush_rewrite_rules(), that must be done manually.
 *
 * @see register_post_type();
 * @uses register_post_type();
 *
 * @param unknown $name   str The singular name of this post type like "Post"
 * @param unknown $plural str The plural of $name
 * @param unknown $slug   str The slug you want to use to register the post type
 * @param unknown $prefix str A prefix to append to the post-type, default 'cfpb_'
 */
namespace CFPB\Utils;
class PostType {
	public $name;
	public $plural;
	public $slug;
	public $prefix;
	public $args;

	public function build_post_type( $name, $plural, $slug, $prefix = '', $args = array() ) {
		$labels = array(
			'name' => $plural,
			'singular_name' => $name,
			'add_new' => 'Add New',
			'add_new_item' => 'Add New ' . $name,
			'edit_item' => 'Edit ' . $name,
			'new_item' => 'New ' . $name,
			'all_items' => 'All ' . $plural,
			'view_item' => 'View ' . $name,
			'search_items' => 'Search ' . $plural,
			'not_found' => 'No ' . $plural . ' found.',
			'not_found_in_trash' => 'No ' . $plural . ' found.',
			'parent_item_colon' => 'Parent ' . $name . ':',
			'menu_name' => $plural,
		);
		$defaults = array(
			'labels'              => $labels,
			'description'         => 'CFPB_theme custom type for ' . $plural,
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 100,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'author', 'editor', 'revisions', 'page-attributes', 'custom-fields', ),
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => $slug, 'with_front' => false ),
			'query_var'           => true,
		);
		$args = wp_parse_args( $args, $defaults );
		register_post_type( $prefix . $slug, $args );
	}

	// function set_posttype_parent_type() {
	// 	remove_meta_box( 'pageparentdiv', , );
	// }
	
	function maybe_flush_rewrite_rules( $target ) {
		global $wp_rewrite;
		$rules  = $wp_rewrite;
		if ( ! array_key_exists( $target . '/?$', $rules->extra_rules_top ) ) {
			flush_rewrite_rules( $hard = true );
		}
	}
}