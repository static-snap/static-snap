<?php


namespace {
	// WPForms
	class WPForms_Form_Handler {
		public static function get_instance() {
			return new self();
		}
		public function get() {
			return array();
		}
	}
	class WPForms {
		// phpcs:ignore
		public $form = new WPForms_Form_Handler();
		public static function get_instance() {
			return new self();
		}
		public function get() {
			return array();
		}
	}

	function WPForms() {
		return WPForms::get_instance();
	}

	// Contact Form 7 Classes
    class WPCF7_ContactForm {
        public static function get_current() {
            return new self();
        }

		public static function find(){
			return array();
		}

        /** @return int */
        public function id() {
            return 1;
        }

        /** @return string */
        public function title() {
            return 'Example Title';
        }
    }

	// Gravity forms
	class GFAPI {
		public static function get_form( $id ) {
			return array(
				'id'     => 1,
				'title'  => 'Example Title',
				'fields' => array(),
			);
		}

		public static function get_forms(  ) {
			return array(
				array(
					'id'     => 1,
					'title'  => 'Example Title',
					'fields' => array(),
				)
			);
		}
	}
}
namespace Elementor{
	class Element_Base {
		public function get_id() {
			return "1";
		}
		public function get_name() {
			return 'form';
		}

		public function get_settings() {
			return array();
		}
		public function set_settings($settings) {
			// Simulated functionality
			return true;
		}

	}

	class Widget_Base {
		public function get_id() {
			return "1";
		}
		public function get_name() {
			return 'form';
		}

		public function get_settings() {
			return array();
		}
		public function set_settings($settings) {
			// Simulated functionality
			return true;
		}

		public function start_controls_section($id, $args) {
		}

		public function add_control($id, $args) {
		}
		public function end_controls_section() {
		}

	}

	class Controls_Manager {
		const TEXT = 'text';
		const SLIDER= 'slider';
	}

	class Frontend{
		public static function instance() {
			return new self();
		}
		public function get_settings() {
			return array();
		}
		public function set_settings($name,$settings) {
		}

	}

	class Plugin{
		public $widgets_manager;
		public $elements_manager;
		public static function instance() {
			return new self();
		}
		public function elements_manager() {
			return new self();
		}
		public function add_category($id, $args) {
		}

	}
}

// Elementor Forms Module
namespace ElementorPro\Modules\Forms\Widgets {
    class Form {
        public function add_render_attribute($name, $attr, $value) {
            // Simulated functionality
            return true;
        }
		public function remove_render_attribute($name, $attr) {
			// Simulated functionality
			return true;
		}
        public function render_form() {
            echo "Rendering Form";
        }
    }
}
