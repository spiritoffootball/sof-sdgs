<?php
/**
 * SDGs ACF Class.
 *
 * Handles ACF functionality for SDGs.
 *
 * @package Spirit_Of_Football_SDGs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * ACF Class.
 *
 * A class that encapsulates ACF functionality for SDGs.
 *
 * @since 1.0.0
 */
class Spirit_Of_Football_SDGs_ACF {

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
	public $loader;

	/**
	 * ACF Field Group prefix.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $group_key = 'group_sof_sdgs_';

	/**
	 * SDG ACF Field prefix.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $field_key = 'field_sof_sdgs_';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Spirit_Of_Football_SDGs_Loader $parent The parent object.
	 */
	public function __construct( $parent ) {

		// Store references.
		$this->loader = $parent;
		$this->plugin = $parent->plugin;

		// Init when this plugin is loaded.
		add_action( 'sof_sdgs/sdgs/loaded', [ $this, 'register_hooks' ] );

	}

	/**
	 * Registers hook callbacks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {

		// Add Field Group and Fields.
		add_action( 'acf/init', [ $this, 'field_groups_add' ] );
		add_action( 'acf/init', [ $this, 'fields_add' ] );

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Adds ACF Field Groups.
	 *
	 * @since 1.0.0
	 */
	public function field_groups_add() {

		// Add our ACF Fields.
		$this->field_group_sdgs_item_add();

	}

	/**
	 * Adds SDGs Field Group.
	 *
	 * @since 1.0.0
	 */
	private function field_group_sdgs_item_add() {

		// Attach the Field Group to our CPT.
		$field_group_location = [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => $this->loader->cpt->post_type_name,
				],
			],
		];

		// Hide UI elements on our CPT edit page.
		$field_group_hide_elements = [
			'the_content',
			'excerpt',
			'discussion',
			'comments',
			// 'revisions',
			'author',
			'format',
			'page_attributes',
			// 'featured_image',
			'tags',
			'send-trackbacks',
		];

		// Define Field Group.
		$field_group = [
			'key'                   => $this->group_key . 'item',
			'title'                 => __( 'SDG Details', 'sof-sdgs' ),
			'fields'                => [],
			'location'              => $field_group_location,
			'hide_on_screen'        => $field_group_hide_elements,
			'label_placement'       => 'left',
			'instruction_placement' => 'field',
		];

		// Now add the Field Group.
		acf_add_local_field_group( $field_group );

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Adds ACF Fields.
	 *
	 * @since 1.0.0
	 */
	public function fields_add() {

		// Add our ACF Fields.
		$this->fields_item_add();

	}

	/**
	 * Adds "SDG" Fields.
	 *
	 * @since 1.0.0
	 */
	private function fields_item_add() {

		// Define Field.
		$field = [
			'key'               => $this->field_key . 'image',
			'parent'            => $this->group_key . 'item',
			'label'             => __( 'SDG Icon', 'sof-sdgs' ),
			'name'              => 'image',
			'type'              => 'image',
			'instructions'      => __( 'The Icon of the SDG.', 'sof-sdgs' ),
			'required'          => 0,
			'conditional_logic' => 0,
			'preview_size'      => 'medium',
			'acfe_thumbnail'    => 0,
			'library'           => 'all',
			'return_format'     => 'array',
			'wrapper'           => [
				'width' => '',
				'class' => '',
				'id'    => '',
			],
		];

		// Now add Field.
		acf_add_local_field( $field );

		// Define Field.
		$field = [
			'key'           => $this->field_key . 'about',
			'parent'        => $this->group_key . 'item',
			'label'         => __( 'About this SDG', 'sof-sdgs' ),
			'name'          => 'about',
			'type'          => 'wysiwyg',
			'instructions'  => __( 'Use this field to describe the SDG.', 'sof-sdgs' ),
			'default_value' => '',
			'placeholder'   => '',
			'wrapper'       => [
				'width' => '',
				'class' => '',
				'id'    => '',
			],
		];

		// Now add Field.
		acf_add_local_field( $field );

		// Define "Image source" Repeater.
		$field = [
			'key'               => $this->field_key . 'file',
			'parent'            => $this->group_key . 'item',
			'label'             => __( 'Links', 'sof-sdgs' ),
			'name'              => 'links',
			'type'              => 'repeater',
			'instructions'      => __( 'Add any links that are relevant to this SDG. The first link should be the link to the UN page for this SDG.', 'sof-sdgs' ),
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => [
				'width' => '',
				'class' => '',
				'id'    => '',
			],
			'collapsed'         => '',
			'min'               => 0,
			'max'               => 0,
			'layout'            => 'table',
			'button_label'      => __( 'Add link', 'sof-sdgs' ),
			'sub_fields'        => [
				[
					'key'               => $this->field_key . 'link_title',
					'label'             => __( 'Link Label', 'sof-sdgs' ),
					'name'              => 'link_label',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 0,
					'placeholder'       => '',
					'conditional_logic' => 0,
				],
				[
					'key'               => $this->field_key . 'link',
					'label'             => __( 'Link', 'sof-sdgs' ),
					'name'              => 'link',
					'type'              => 'url',
					'instructions'      => '',
					'required'          => 0,
					'allow_null'        => 1,
					'placeholder'       => '',
					'conditional_logic' => 0,
				],
			],
		];

		// Now add Field.
		acf_add_local_field( $field );

	}

}
