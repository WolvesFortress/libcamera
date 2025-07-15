<?php

declare(strict_types=1);

namespace muqsit\libcamera;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEase;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionRotation;
use pocketmine\network\mcpe\protocol\types\camera\CameraTargetInstruction;
use pocketmine\player\Player;
use function array_push;
use function spl_object_id;

final class CameraInstruction{

	public static function clear() : self{
		return new self([[null, true, null, null]]);
	}

	public static function fade(?CameraFadeInstructionColor $color, ?CameraFadeInstructionTime $time) : self{
		$instruction = new CameraFadeInstruction($time, $color);
		return new self([[null, null, $instruction, null]]);
	}

	public static function set(
		CameraPreset $preset,
		?CameraSetInstructionEase $ease = null,
		?Vector3 $camera_pos = null,
		?CameraSetInstructionRotation $rot = null,
		?Vector3 $facing_pos = null
	) : self{
		if(!libcamera::isRegistered()){
			throw new \RuntimeException("libcamera::register() must be called before using CameraInstruction::set()");
		}
		var_dump(libcamera::$network_ids, spl_object_id($preset));
		$preset_id = libcamera::$network_ids[spl_object_id($preset)][1] ?? throw new \InvalidArgumentException("Unknown camera preset, see libcamera::registerPreset()");
		$instruction = new CameraSetInstruction(
			preset: $preset_id,
			ease: $ease,
			cameraPosition: $camera_pos,
			rotation: $rot,
			facingPosition: $facing_pos,
			viewOffset: null,
			entityOffset: null,
			default: null,
			ignoreStartingValuesComponent: false
		);
		return new self([[$instruction, null, null, null]]);
	}

	public static function target(?Vector3 $targetCenterOffset, int $actorUniqueId) : self{
		$target = new CameraTargetInstruction($targetCenterOffset, $actorUniqueId);
		return new self([[null, null, null, $target]]);
	}

	public static function removeTarget() : self{
		return new self([[null, null, null, null]]);
	}

	/**
	 * Combines multiple {@see CameraInstruction} instances into one.
	 * This is useful for sending multiple instructions in a single packet.
	 *
	 * @param CameraInstruction ...$instances
	 */
	public static function multi(self ...$instances) : self{
		$instructions = [];
		foreach($instances as $instance){
			array_push($instructions, ...$instance->instructions);
		}
		return new self($instructions);
	}

	/**
	 * @param list<array{CameraSetInstruction|null, bool|null, CameraFadeInstruction|null, CameraTargetInstruction|null}> $instructions
	 */
	private function __construct(
		readonly private array $instructions
	){}

	public function send(Player $player) : void{
		foreach($this->instructions as [$set, $clear, $fade, $target]){
			$player->getNetworkSession()->sendDataPacket(CameraInstructionPacket::create($set, $clear, $fade, $target, $target === null));
		}
	}
}