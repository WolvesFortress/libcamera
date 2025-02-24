<?php

declare(strict_types=1);

namespace muqsit\libcamera;

use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use function spl_object_id;

final class CameraPresetRegistry{

	/** @var array<int, int> */
	readonly public array $network_ids;

	/**
	 * @param array<non-empty-string, CameraPreset> $registered
	 */
	public function __construct(
		readonly public array $registered
	){
		$network_ids = [];
		$id = 0;
		foreach($this->registered as $preset){
			$network_ids[spl_object_id($preset)] = $id++;
		}
		$this->network_ids = $network_ids;
	}
}