<?php

declare(strict_types=1);

namespace muqsit\libcamera;

use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\math\Vector2;
use pocketmine\utils\RegistryTrait;

/**
 * @method static CameraPreset FREE()
 * @method static CameraPreset FIRST_PERSON()
 * @method static CameraPreset THIRD_PERSON()
 * @method static CameraPreset THIRD_PERSON_FRONT()
 * @method static CameraPreset TARGET()
 */
final class CameraPresetRegistry{
	use RegistryTrait;

	final private function __construct(){
		//none
	}


	/**
	 * @internal Injecting this function will not send more presets!
	 * @see libcamera::registerPreset()
	 */
	private static function register(string $name, CameraPreset $member) : void{
		self::_registryRegister($name, $member);
	}

	/** @return array<string, CameraPreset> */
	public static function getAll() : array{
		return self::_registryGetAll();
	}

	protected static function setup() : void{
		self::register("free", new CameraPreset(
			name: "minecraft:free",
			parent: "",
			xPosition: null,
			yPosition: null,
			zPosition: null,
			pitch: null,
			yaw: null,
			rotationSpeed: null,
			snapToTarget: null,
			horizontalRotationLimit: null,
			verticalRotationLimit: null,
			continueTargeting: null,
			blockListeningRadius: null,
			viewOffset: null,
			entityOffset: null,
			radius: null,
			yawLimitMin: null,
			yawLimitMax: null,
			audioListenerType: CameraPreset::AUDIO_LISTENER_TYPE_CAMERA,
			playerEffects: false,
			aimAssist: null,
			controlScheme: null
		));

		self::register("first_person", new CameraPreset(
			name: "minecraft:first_person",
			parent: "",
			xPosition: null,
			yPosition: null,
			zPosition: null,
			pitch: null,
			yaw: null,
			rotationSpeed: null,
			snapToTarget: null,
			horizontalRotationLimit: null,
			verticalRotationLimit: null,
			continueTargeting: null,
			blockListeningRadius: null,
			viewOffset: null,
			entityOffset: null,
			radius: null,
			yawLimitMin: null,
			yawLimitMax: null,
			audioListenerType: CameraPreset::AUDIO_LISTENER_TYPE_PLAYER,
			playerEffects: false,
			aimAssist: null,
			controlScheme: null
		));
		self::register("third_person", new CameraPreset(
			name: "minecraft:third_person",
			parent: "",
			xPosition: null,
			yPosition: null,
			zPosition: null,
			pitch: null,
			yaw: null,
			rotationSpeed: null,
			snapToTarget: null,
			horizontalRotationLimit: null,
			verticalRotationLimit: null,
			continueTargeting: null,
			blockListeningRadius: null,
			viewOffset: null,
			entityOffset: null,
			radius: null,
			yawLimitMin: null,
			yawLimitMax: null,
			audioListenerType: CameraPreset::AUDIO_LISTENER_TYPE_PLAYER,
			playerEffects: false,
			aimAssist: null,
			controlScheme: null
		));
		self::register("third_person_front", new CameraPreset(
			name: "minecraft:third_person_front",
			parent: "",
			xPosition: null,
			yPosition: null,
			zPosition: null,
			pitch: null,
			yaw: null,
			rotationSpeed: null,
			snapToTarget: null,
			horizontalRotationLimit: null,
			verticalRotationLimit: null,
			continueTargeting: null,
			blockListeningRadius: null,
			viewOffset: null,
			entityOffset: null,
			radius: null,
			yawLimitMin: null,
			yawLimitMax: null,
			audioListenerType: CameraPreset::AUDIO_LISTENER_TYPE_PLAYER,
			playerEffects: false,
			aimAssist: null,
			controlScheme: null
		));

		self::register("target", new CameraPreset(
			name: "minecraft:target",
			parent: "minecraft:free",
			xPosition: null,
			yPosition: null,
			zPosition: null,
			pitch: null,
			yaw: null,
			rotationSpeed: 0.0,
			snapToTarget: true,
			horizontalRotationLimit: new Vector2(0.0, 360.0),
			verticalRotationLimit: new Vector2(0.0, 180.0),
			continueTargeting: true,
			blockListeningRadius: 50.0,
			viewOffset: null,
			entityOffset: null,
			radius: null,
			yawLimitMin: null,
			yawLimitMax: null,
			audioListenerType: CameraPreset::AUDIO_LISTENER_TYPE_CAMERA,
			playerEffects: false,
			aimAssist: null,
			controlScheme: null
		));
	}
}