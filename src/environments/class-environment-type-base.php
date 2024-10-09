<?php
/**
 * Base class for Environments
 *
 * @package StaticSnap
 */

namespace StaticSnap\Environments;

use StaticSnap\Extension\Extension_Base;
use StaticSnap\Interfaces\Environment_Type_Interface;
use JsonSerializable;
use StaticSnap\Constants\Actions;

/**
 * This class is used to create the base environment.
 */
abstract class Environment_Type_Base extends Extension_Base implements Environment_Type_Interface, JsonSerializable {

	/**
	 * Disabled reason
	 *
	 * @var string
	 */
	protected $disabled_reason = null;

	/**
	 * Is ready to use extra setup url
	 *
	 * When the environment is not ready to use, this URL will be used to provide extra setup information.
	 *
	 * @var string
	 */
	protected $is_ready_to_use_extra_setup_url = null;


	/**
	 * Is ready to use disabled message
	 *
	 * @var string
	 */
	protected $is_ready_to_use_disabled_message = null;


	/**
	 * Get disabled reason
	 *
	 * @return string
	 */
	public function get_disabled_reason() {
		return $this->disabled_reason;
	}

	/**
	 * Is ready to use
	 *
	 * @return boolean
	 */
	public function is_ready_to_use(): bool {
		return true;
	}


	/**
	 * Needs Static Snap connect to be available
	 *
	 * @return boolean
	 */
	public function needs_connect(): bool {
		return false;
	}
	/**
	 * Needs zip file
	 *
	 * @return bool
	 */
	public function needs_zip(): bool {
		return false;
	}

	/**
	 * Json serialize
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return array(
			'name'                        => $this->get_name(),
			'type'                        => $this->get_type(),
			'available'                   => $this->is_available(),
			'isReadyToUse'                => $this->is_ready_to_use(),
			'isReadyToUseExtraSetupUrl'   => $this->is_ready_to_use_extra_setup_url,
			'isReadyToUseDisabledMessage' => $this->is_ready_to_use_disabled_message,
			'needsConnect'                => $this->needs_connect(),
			'disabledReason'              => $this->get_disabled_reason(),
			'needsZip'                    => $this->needs_zip(),
			'settings'                    => $this->get_settings_fields(),
		);
	}



	/**
	 * Get type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return 'environment_type';
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	abstract public function get_name(): string;
}
