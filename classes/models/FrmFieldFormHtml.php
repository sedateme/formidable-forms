<?php
/**
 * @since 3.0
 */

class FrmFieldFormHtml {

	private $html;

	private $html_id;

	/**
	 *   @var FrmFieldType
	 */
	private $field_obj;

	private $field_id;

	private $form = array();

	private $pass_args = array();

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		$this->_set( 'field_obj', $atts );
		$this->set_field_id( $atts );
		$this->_set( 'form', $atts );
		$this->_set( 'html_id', $atts );
		$this->set_html( $atts );
		$this->set_pass_args( $atts );
	}

	/**
	 * @since 3.0
	 *
	 * @param string $param
	 * @param array $atts
	 */
	private function _set( $param, $atts ) {
		if ( isset( $atts[ $param ] ) ) {
			$this->{$param} = $atts[ $param ];
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	private function set_html( $atts ) {
		$this->set_from_field( $atts, array( 'param' => 'html', 'default' => 'custom_html' ) );
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	private function set_field_id( $atts ) {
		$this->set_from_field( $atts, array( 'param' => 'field_id', 'default' => 'id' ) );
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	private function set_pass_args( $atts ) {
		$this->pass_args = $atts;
		$exclude = array( 'field_obj', 'html' );
		
		foreach ( $exclude as $ex ) {
			if ( isset( $atts[ $ex ] ) ) {
				unset( $this->pass_args[ $ex ] );
			}
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param array $atts
	 * @param array $set
	 */
	private function set_from_field( $atts, $set ) {
		if ( isset( $atts[ $set['param'] ] ) ) {
			$this->{$set['param']} = $atts[ $set['param'] ];
		} else {
			$this->{$set['param']} = $this->field_obj->get_field_column( $set['default'] );
		}
	}

	public function get_html() {
		$this->replace_shortcodes_before_input();
		$this->replace_shortcodes_with_atts();
		$this->replace_shortcodes_after_input();

		return $this->html;
	}

	/**
	 * @since 3.0
	 */
	private function replace_shortcodes_before_input() {

		// Remove the for attribute for captcha
		if ( $this->field_obj->get_field_column('type') == 'captcha' ) {
			$this->html = str_replace( ' for="field_[key]"', '', $this->html );
		}

		$this->replace_field_values();
		$this->add_class_to_divider();

		$this->replace_required_label_shortcode();
		$this->replace_required_class();
		$this->replace_description_shortcode();
		$this->replace_error_shortcode();
		$this->add_class_to_label();
		$this->add_field_div_classes();

		$this->replace_entry_key();
		$this->replace_form_shortcodes();
		$this->process_wp_shortcodes();
	}

	/**
	 * @since 3.0
	 */
	private function replace_field_values() {
		//replace [id]
		$this->html = str_replace( '[id]', $this->field_id, $this->html );

		// set the label for
		$this->html = str_replace( 'field_[key]', $this->html_id, $this->html );

		//replace [key]
		$this->html = str_replace( '[key]', $this->field_obj->get_field_column('field_key'), $this->html );

		//replace [field_name]
		$this->html = str_replace('[field_name]', $this->field_obj->get_field_column('name'), $this->html );
	}

	/**
	 * If field type is section heading, add class so a bottom margin
	 * can be added to either the h3 or description
	 *
	 * @since 3.0
	 */
	private function add_class_to_divider() {
		if ( $this->field_obj->get_field_column('type') == 'divider' ) {
			if ( FrmField::is_option_true( $this->field_obj->get_field(), 'description' ) ) {
				$this->html = str_replace( 'frm_description', 'frm_description frm_section_spacing', $this->html );
			} else {
				$this->html = str_replace( '[label_position]', '[label_position] frm_section_spacing', $this->html );
			}
		}
	}

	/**
	 * @since 3.0
	 */
	private function replace_required_label_shortcode() {
		$required = FrmField::is_required( $this->field_obj->get_field() ) ? $this->field_obj->get_field_column('required_indicator') : '';
		FrmShortcodeHelper::remove_inline_conditions( ! empty( $required ), 'required_label', $required, $this->html );
	}

	/**
	 * @since 3.0
	 */
	private function replace_description_shortcode() {
		$description = $this->field_obj->get_field_column('description');
		FrmShortcodeHelper::remove_inline_conditions( ( $description && $description != '' ), 'description', $description, $this->html );
	}

	/**
	 * @since 3.0
	 */
	private function replace_error_shortcode() {
		$error = isset( $this->pass_args['errors'][ 'field' . $this->field_id ] ) ? $this->pass_args['errors'][ 'field' . $this->field_id ] : false;
		FrmShortcodeHelper::remove_inline_conditions( ! empty( $error ), 'error', $error, $this->html );
	}

	/**
	 * replace [required_class]
	 *
	 * @since 3.0
	 */
	private function replace_required_class() {
		$required_class = FrmField::is_required( $this->field_obj->get_field() ) ? ' frm_required_field' : '';
		$this->html = str_replace( '[required_class]', $required_class, $this->html );
	}

	/**
	 * @since 3.0
	 */
	private function replace_form_shortcodes() {
		if ( $this->form ) {
			$form = (array) $this->form;

			//replace [form_key]
			$this->html = str_replace( '[form_key]', $form['form_key'], $this->html );

			//replace [form_name]
			$this->html = str_replace( '[form_name]', $form['name'], $this->html );
		}
	}

	/**
	 * @since 3.0
	 */
	public function replace_shortcodes_after_input() {
		$this->html .= "\n";

		//Return html if conf_field to prevent loop
		if ( $this->field_obj->get_field_column('conf_field') == 'stop' ) {
			return;
		}

		$this->filter_for_more_shortcodes();
		$this->filter_html_field_shortcodes();
		$this->remove_collapse_shortcode();
	}

	/**
	 * @since 3.0
	 */
	private function filter_for_more_shortcodes() {
		$atts = $this->pass_args;

		//If field is not in repeating section
		if ( empty( $atts['section_id'] ) ) {
			$atts = array( 'errors' => $this->pass_args['errors'], 'form' => $this->form );
		}
		$this->html = apply_filters( 'frm_replace_shortcodes', $this->html, $this->field_obj->get_field(), $atts );
	}

	/**
	 * @since 3.0
	 */
	private function filter_html_field_shortcodes() {
		if ( $this->field_obj->get_field_column('type') == 'html' ) {
			FrmFieldsHelper::run_wpautop( array( 'wpautop' => true ), $this->html );

			$this->html = apply_filters( 'frm_get_default_value', $this->html, (object) $this->field_obj->get_field(), false );
			$this->html = do_shortcode( $this->html );
		}
	}

	/**
	 * Remove [collapse_this] if it's still included after all processing
	 * @since 3.0
	 */
	private function remove_collapse_shortcode() {
		if ( preg_match( '/\[(collapse_this)\]/s', $this->html ) ) {
			$this->html = str_replace( '[collapse_this]', '', $this->html );
		}
	}

	/**
	 * @since 3.0
	 */
	private function replace_shortcodes_with_atts() {
		preg_match_all("/\[(input|deletelink)\b(.*?)(?:(\/))?\]/s", $this->html, $shortcodes, PREG_PATTERN_ORDER);

		foreach ( $shortcodes[0] as $short_key => $tag ) {
			$shortcode_atts = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[2][ $short_key ] );
			$tag = FrmShortcodeHelper::get_shortcode_tag( $shortcodes, $short_key, array( 'conditional' => false, 'conditional_check' => false ) );

			$replace_with = '';

			if ( $tag == 'deletelink' && FrmAppHelper::pro_is_installed() ) {
				$replace_with = FrmProEntriesController::entry_delete_link( $shortcode_atts );
			} elseif ( $tag == 'input' ) {
				$replace_with = $this->replace_input_shortcode( $shortcode_atts );
			}

			$this->html = str_replace( $shortcodes[0][ $short_key ], $replace_with, $this->html );
		}
	}

	/**
	 * @param array $shortcode_atts
	 *
	 * @return string
	 */
	private function replace_input_shortcode( $shortcode_atts ) {
		$shortcode_atts = $this->prepare_input_shortcode_atts( $shortcode_atts );
		return $this->field_obj->include_front_field_input( $this->pass_args, $shortcode_atts );
	}

	/**
	 * @param array $shortcode_atts
	 *
	 * @return array
	 */
	private function prepare_input_shortcode_atts( $shortcode_atts ) {
		if ( isset( $shortcode_atts['opt'] ) ) {
			$shortcode_atts['opt']--;
		}

		$field_class = isset( $shortcode_atts['class'] ) ? $shortcode_atts['class'] : '';
		$this->field_obj->set_field_column( 'input_class', $field_class );

		if ( isset( $shortcode_atts['class'] ) ) {
			unset( $shortcode_atts['class'] );
		}

		$this->field_obj->set_field_column( 'shortcodes', $shortcode_atts );

		return $shortcode_atts;
	}

	/**
	 * Add the label position class into the HTML
	 * If the label position is inside, add a class to show the label if the field has a value.
	 *
	 * @since 3.0
	 */
	private function add_class_to_label() {
		$label_class = in_array( $this->field_obj->get_field_column('type'), array( 'divider', 'end_divider', 'break' ) ) ? $this->field_obj->get_field_column('label') : ' frm_primary_label';
		$this->html = str_replace( '[label_position]', $label_class, $this->html );
		if ( $this->field_obj->get_field_column('label') == 'inside' && $this->field_obj->get_field_column('value') != '' ) {
			$this->html = str_replace( 'frm_primary_label', 'frm_primary_label frm_visible', $this->html );
		}
	}

	/**
	 * replace [entry_key]
	 *
	 * @since 3.0
	 */
	private function replace_entry_key() {
		$entry_key = FrmAppHelper::simple_get( 'entry', 'sanitize_title' );
		$this->html = str_replace( '[entry_key]', $entry_key, $this->html );
	}

	/**
	 * Add classes to a field div
	 *
	 * @since 3.0
	 */
	private function add_field_div_classes() {
		$classes = $this->get_field_div_classes();

		if ( $this->field_obj->get_field_column('type') == 'html' && strpos( $this->html, '[error_class]' ) === false ) {
			// there is no error_class shortcode for HTML fields
			$this->html = str_replace( 'class="frm_form_field', 'class="frm_form_field ' . $classes, $this->html );
		}
		$this->html = str_replace( '[error_class]', $classes, $this->html );
	}


	/**
	 * Get the classes for a field div
	 *
	 * @since 3.0
	 *
	 * @return string $classes
	 */
	private function get_field_div_classes() {
		// Add error class
		$classes = isset( $this->pass_args['errors'][ 'field' . $this->field_id ] ) ? ' frm_blank_field' : '';

		// Add label position class
		$classes .= ' frm_' . $this->field_obj->get_field_column('label') . '_container';

		// Add CSS layout classes
		if ( ! empty( $this->field_obj->get_field_column('classes') ) ) {
			if ( ! strpos( $this->html, 'frm_form_field ') ) {
				$classes .= ' frm_form_field';
			}
			$classes .= ' ' . $this->field_obj->get_field_column('classes');
		}

		// Add class to HTML field
		if ( $this->field_obj->get_field_column('type') == 'html' ) {
			$classes .= ' frm_html_container';
		}

		// Get additional classes
		return apply_filters( 'frm_field_div_classes', $classes, $this->field_obj->get_field(), array( 'field_id' => $this->field_id ) );
	}

	/**
	 * This filters shortcodes in the field HTML
	 *
	 * @since 3.0
	 */
	private function process_wp_shortcodes() {
		if ( apply_filters( 'frm_do_html_shortcodes', true ) ) {
			$this->html = do_shortcode( $this->html );
		}
	}
}
