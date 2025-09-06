<?php
/**
 * SDGs Custom Post Type Class.
 *
 * Handles providing an "SDGs" Custom Post Type.
 *
 * @package Spirit_Of_Football_SDGs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Custom Post Type Class.
 *
 * A class that encapsulates an "SDGs" Custom Post Type.
 *
 * @since 1.0.0
 */
class Spirit_Of_Football_SDGs_CPT {

	/**
	 * Plugin object.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var Spirit_Of_Football_SDGs
	 */
	public $plugin;

	/**
	 * SDGs loader.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var Spirit_Of_Football_SDGs_Loader
	 */
	public $coverage;

	/**
	 * Custom Post Type name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $post_type_name = 'sdg';

	/**
	 * Custom Post Type REST base.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $post_type_rest_base = 'sdg';

	/**
	 * Taxonomy name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $taxonomy_name = 'sdg-type';

	/**
	 * Taxonomy REST base.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $taxonomy_rest_base = 'sdg-type';

	/**
	 * Alternative Taxonomy name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $taxonomy_alt_name = 'sdg-tag';

	/**
	 * Alternative Taxonomy REST base.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $taxonomy_alt_rest_base = 'sdg-tag';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Spirit_Of_Football_SDGs_Loader $parent The parent object.
	 */
	public function __construct( $parent ) {

		// Store references.
		$this->coverage = $parent;
		$this->plugin   = $parent->plugin;

		// Init when this plugin is loaded.
		add_action( 'sof_sdgs/sdgs/loaded', [ $this, 'register_hooks' ] );

	}

	/**
	 * Registers hook callbacks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {

		// Activation and deactivation.
		add_action( 'sof_sdgs/activate', [ $this, 'activate' ] );
		add_action( 'sof_sdgs/deactivate', [ $this, 'deactivate' ] );

		// Always create post type.
		add_action( 'init', [ $this, 'post_type_create' ] );

		// Make sure our feedback is appropriate.
		add_filter( 'post_updated_messages', [ $this, 'post_type_messages' ] );

		// Make sure our UI text is appropriate.
		add_filter( 'enter_title_here', [ $this, 'post_type_title' ] );

		// Create primary taxonomy.
		add_action( 'init', [ $this, 'taxonomy_create' ] );
		add_filter( 'wp_terms_checklist_args', [ $this, 'taxonomy_fix_metabox' ], 10, 2 );
		add_action( 'restrict_manage_posts', [ $this, 'taxonomy_filter_post_type' ] );

		// Create alternative taxonomy.
		add_action( 'init', [ $this, 'taxonomy_alt_create' ] );
		add_filter( 'wp_terms_checklist_args', [ $this, 'taxonomy_alt_fix_metabox' ], 10, 2 );
		add_action( 'restrict_manage_posts', [ $this, 'taxonomy_alt_filter_post_type' ] );

	}

	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 1.0.0
	 */
	public function activate() {

		// Pass through.
		$this->post_type_create();
		$this->taxonomy_create();
		$this->taxonomy_alt_create();

		// Go ahead and flush.
		flush_rewrite_rules();

	}

	/**
	 * Actions to perform on plugin deactivation (NOT deletion).
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {

		// Flush rules to reset.
		flush_rewrite_rules();

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Creates our Custom Post Type.
	 *
	 * @since 1.0.0
	 */
	public function post_type_create() {

		// Only call this once.
		static $registered;
		if ( $registered ) {
			return;
		}

		// Define labels.
		$labels = [
			'name'               => __( 'SDGs', 'sof-sdgs' ),
			'singular_name'      => __( 'SDG', 'sof-sdgs' ),
			'add_new'            => __( 'Add New', 'sof-sdgs' ),
			'add_new_item'       => __( 'Add New SDG', 'sof-sdgs' ),
			'edit_item'          => __( 'Edit SDG', 'sof-sdgs' ),
			'new_item'           => __( 'New SDG', 'sof-sdgs' ),
			'all_items'          => __( 'All SDGs', 'sof-sdgs' ),
			'view_item'          => __( 'View SDG', 'sof-sdgs' ),
			'search_items'       => __( 'Search SDGs', 'sof-sdgs' ),
			'not_found'          => __( 'No matching SDG found', 'sof-sdgs' ),
			'not_found_in_trash' => __( 'No SDGs found in Trash', 'sof-sdgs' ),
			'menu_name'          => __( 'SDGs', 'sof-sdgs' ),
		];

		// Build args.
		$args = [

			'labels'              => $labels,

			// Defaults.
			'menu_icon'           => 'dashicons-admin-site-alt3',
			'description'         => __( 'Spirit of Football SDGs', 'sof-sdgs' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'has_archive'         => false,
			'query_var'           => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_position'       => 52,
			'map_meta_cap'        => true,
			'pages'               => false,

			// Rewrite.
			'rewrite'             => [
				'slug'       => 'sdgs',
				'with_front' => false,
			],

			// Supports.
			'supports'            => [
				'title',
				'thumbnail',
			],

			// REST setup.
			'show_in_rest'        => true,
			'rest_base'           => $this->post_type_rest_base,

		];

		/**
		 * Filters the Custom Post Type configuration arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The default Custom Post Type configuration arguments.
		 */
		$args = apply_filters( 'sof_sdgs/cpt/post_type/args', $args );

		// Set up the Custom Post Type called "SDG".
		register_post_type( $this->post_type_name, $args );

		// Flag done.
		$registered = true;

	}

	/**
	 * Overrides messages for a Custom Post Type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $messages The existing messages.
	 * @return array $messages The modified messages.
	 */
	public function post_type_messages( $messages ) {

		// Access relevant globals.
		global $post, $post_ID;

		// Define custom messages for our Custom Post Type.
		$messages[ $this->post_type_name ] = [

			// Unused - messages start at index 1.
			0  => '',

			// Item updated.
			1  => sprintf(
				/* translators: %s: The permalink. */
				__( 'SDG updated. <a href="%s">View SDG</a>', 'sof-sdgs' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Custom fields.
			2  => __( 'Custom field updated.', 'sof-sdgs' ),
			3  => __( 'Custom field deleted.', 'sof-sdgs' ),
			4  => __( 'SDG updated.', 'sof-sdgs' ),

			// Item restored to a revision.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			5  => isset( $_GET['revision'] ) ?

				// Revision text.
				sprintf(
					/* translators: %s: The date and time of the revision. */
					__( 'SDG restored to revision from %s', 'sof-sdgs' ),
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					wp_post_revision_title( (int) $_GET['revision'], false )
				) :

				// No revision.
				false,

			// Item published.
			6  => sprintf(
				/* translators: %s: The permalink. */
				__( 'SDG published. <a href="%s">View SDG</a>', 'sof-sdgs' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Item saved.
			7  => __( 'SDG saved.', 'sof-sdgs' ),

			// Item submitted.
			8  => sprintf(
				/* translators: %s: The permalink. */
				__( 'SDG submitted. <a target="_blank" href="%s">Preview SDG</a>', 'sof-sdgs' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

			// Item scheduled.
			9  => sprintf(
				/* translators: 1: The date, 2: The permalink. */
				__( 'SDG scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview SDG</a>', 'sof-sdgs' ),
				/* translators: Publish box date format - see https://php.net/date */
				date_i18n( __( 'M j, Y @ G:i', 'sof-sdgs' ), strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Draft updated.
			10 => sprintf(
				/* translators: %s: The permalink. */
				__( 'SDG draft updated. <a target="_blank" href="%s">Preview SDG</a>', 'sof-sdgs' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

		];

		// --<
		return $messages;

	}

	/**
	 * Overrides the "Add title" label.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title The existing title - usually "Add title".
	 * @return string $title The modified title.
	 */
	public function post_type_title( $title ) {

		// Bail if not our post type.
		if ( get_post_type() !== $this->post_type_name ) {
			return $title;
		}

		// Overwrite with our string.
		$title = __( 'Add an identifying name for the SDG', 'sof-sdgs' );

		// --<
		return $title;

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Creates our Custom Taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function taxonomy_create() {

		// Only register once.
		static $registered;
		if ( $registered ) {
			return;
		}

		// Arguments.
		$args = [

			// Same as "category".
			'hierarchical'      => true,

			// Labels.
			'labels'            => [
				'name'              => _x( 'SDG Types', 'taxonomy general name', 'sof-sdgs' ),
				'singular_name'     => _x( 'SDG Type', 'taxonomy singular name', 'sof-sdgs' ),
				'search_items'      => __( 'Search SDG Types', 'sof-sdgs' ),
				'all_items'         => __( 'All SDG Types', 'sof-sdgs' ),
				'parent_item'       => __( 'Parent SDG Type', 'sof-sdgs' ),
				'parent_item_colon' => __( 'Parent SDG Type:', 'sof-sdgs' ),
				'edit_item'         => __( 'Edit SDG Type', 'sof-sdgs' ),
				'update_item'       => __( 'Update SDG Type', 'sof-sdgs' ),
				'add_new_item'      => __( 'Add New SDG Type', 'sof-sdgs' ),
				'new_item_name'     => __( 'New SDG Type Name', 'sof-sdgs' ),
				'menu_name'         => __( 'SDG Types', 'sof-sdgs' ),
				'not_found'         => __( 'No SDG Types found', 'sof-sdgs' ),
			],

			// Rewrite rules.
			'rewrite'           => [
				'slug' => 'sdg-types',
			],

			// Show column in wp-admin.
			'show_admin_column' => true,
			'show_ui'           => true,

			// REST setup.
			'show_in_rest'      => true,
			'rest_base'         => $this->taxonomy_rest_base,

		];

		// Register a taxonomy for this CPT.
		register_taxonomy( $this->taxonomy_name, $this->post_type_name, $args );

		// Flag done.
		$registered = true;

	}

	/**
	 * Fixes the Custom Taxonomy metabox.
	 *
	 * @see https://core.trac.wordpress.org/ticket/10982
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The existing arguments.
	 * @param int   $post_id The WordSDGs post ID.
	 */
	public function taxonomy_fix_metabox( $args, $post_id ) {

		// If rendering metabox for our taxonomy.
		if ( isset( $args['taxonomy'] ) && $args['taxonomy'] === $this->taxonomy_name ) {

			// Setting 'checked_ontop' to false seems to fix this.
			$args['checked_ontop'] = false;

		}

		// --<
		return $args;

	}

	/**
	 * Adds a filter for this Custom Taxonomy to the Custom Post Type listing.
	 *
	 * @since 1.0.0
	 */
	public function taxonomy_filter_post_type() {

		// Access current post type.
		global $typenow;

		// Bail if not our post type.
		if ( $typenow !== $this->post_type_name ) {
			return;
		}

		// Get tax object.
		$taxonomy = get_taxonomy( $this->taxonomy_name );

		// Build args.
		$args = [
			/* translators: %s: The plural name of the taxonomy terms. */
			'show_option_all' => sprintf( __( 'Show All %s', 'sof-sdgs' ), $taxonomy->label ),
			'taxonomy'        => $this->taxonomy_name,
			'name'            => $this->taxonomy_name,
			'orderby'         => 'name',
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			'selected'        => isset( $_GET[ $this->taxonomy_name ] ) ? wp_unslash( $_GET[ $this->taxonomy_name ] ) : '',
			'show_count'      => true,
			'hide_empty'      => true,
			'value_field'     => 'slug',
			'hierarchical'    => 1,
		];

		// Show a dropdown.
		wp_dropdown_categories( $args );

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Creates our alternative Custom Taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function taxonomy_alt_create() {

		// Only register once.
		static $registered;
		if ( $registered ) {
			return;
		}

		// Arguments.
		$args = [

			// Same as "category".
			'hierarchical'      => true,

			// Labels.
			'labels'            => [
				'name'              => _x( 'SDG Tags', 'taxonomy general name', 'sof-sdgs' ),
				'singular_name'     => _x( 'SDG Tag', 'taxonomy singular name', 'sof-sdgs' ),
				'search_items'      => __( 'Search SDG Tags', 'sof-sdgs' ),
				'all_items'         => __( 'All SDG Tags', 'sof-sdgs' ),
				'parent_item'       => __( 'Parent SDG Tag', 'sof-sdgs' ),
				'parent_item_colon' => __( 'Parent SDG Tag:', 'sof-sdgs' ),
				'edit_item'         => __( 'Edit SDG Tag', 'sof-sdgs' ),
				'update_item'       => __( 'Update SDG Tag', 'sof-sdgs' ),
				'add_new_item'      => __( 'Add New SDG Tag', 'sof-sdgs' ),
				'new_item_name'     => __( 'New SDG Tag Name', 'sof-sdgs' ),
				'menu_name'         => __( 'SDG Tags', 'sof-sdgs' ),
				'not_found'         => __( 'No SDG Tags found', 'sof-sdgs' ),
			],

			// Rewrite rules.
			'rewrite'           => [
				'slug' => 'sdg-tags',
			],

			// Show column in wp-admin.
			'show_admin_column' => true,
			'show_ui'           => true,

			// REST setup.
			'show_in_rest'      => true,
			'rest_base'         => $this->taxonomy_alt_rest_base,

		];

		// Register a taxonomy for this CPT.
		register_taxonomy( $this->taxonomy_alt_name, $this->post_type_name, $args );

		// Flag done.
		$registered = true;

	}

	/**
	 * Fixes the alternative Custom Taxonomy metabox.
	 *
	 * @see https://core.trac.wordpress.org/ticket/10982
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The existing arguments.
	 * @param int   $post_id The WordPress Post ID.
	 */
	public function taxonomy_alt_fix_metabox( $args, $post_id ) {

		// If rendering metabox for our taxonomy.
		if ( isset( $args['taxonomy'] ) && $args['taxonomy'] === $this->taxonomy_alt_name ) {

			// Setting 'checked_ontop' to false seems to fix this.
			$args['checked_ontop'] = false;

		}

		// --<
		return $args;

	}

	/**
	 * Adds a filter for the alternative Custom Taxonomy to the Custom Post Type listing.
	 *
	 * @since 1.0.0
	 */
	public function taxonomy_alt_filter_post_type() {

		// Access current post type.
		global $typenow;

		// Bail if not our post type.
		if ( $typenow !== $this->post_type_name ) {
			return;
		}

		// Get tax object.
		$taxonomy = get_taxonomy( $this->taxonomy_alt_name );

		// Build args.
		$args = [
			/* translators: %s: The plural name of the taxonomy terms. */
			'show_option_all' => sprintf( __( 'Show All %s', 'sof-sdgs' ), $taxonomy->label ),
			'taxonomy'        => $this->taxonomy_alt_name,
			'name'            => $this->taxonomy_alt_name,
			'orderby'         => 'name',
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			'selected'        => isset( $_GET[ $this->taxonomy_alt_name ] ) ? wp_unslash( $_GET[ $this->taxonomy_alt_name ] ) : '',
			'show_count'      => true,
			'hide_empty'      => true,
			'value_field'     => 'slug',
			'hierarchical'    => 1,
		];

		// Show a dropdown.
		wp_dropdown_categories( $args );

	}

}
