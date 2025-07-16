<?php

declare(strict_types=1);

namespace muqsit\libcamera;

use BadMethodCallException;
use InvalidArgumentException;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\CameraPresetsPacket;
use pocketmine\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEaseType;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use function array_values;

final class libcamera{

	private static bool $registered = false;

	public static function isRegistered(): bool{
		return self::$registered;
	}

	protected static int $idCounter = 0;
	/** @var array<int, int> */
	public static array $network_ids;
	private static ?CameraPresetsPacket $packetCache = null;

	private static function newId() : int{
		return self::$idCounter++;
	}

	public static function registerPreset(CameraPreset $preset) : void{
		self::$network_ids[spl_object_id($preset)] = [$preset, self::newId()];
		self::$packetCache = null;
	}

	private static function updateCache() : void{
		self::$packetCache ??= CameraPresetsPacket::create(array_values(array_column(self::$network_ids, 0)));
	}

	public static function register(Plugin $plugin) : void{
		!self::$registered || throw new BadMethodCallException("Tried to registered an already existing libcamera instance");

		self::registerPreset(CameraPresetRegistry::FREE());
		self::registerPreset(CameraPresetRegistry::FIRST_PERSON());
		self::registerPreset(CameraPresetRegistry::THIRD_PERSON());
		self::registerPreset(CameraPresetRegistry::THIRD_PERSON_FRONT());
		self::registerPreset(CameraPresetRegistry::TARGET());
		self::updateCache();

		Server::getInstance()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event) : void{
			self::updateCache();
			if($event->getPacket() instanceof SetLocalPlayerAsInitializedPacket){
				$event->getOrigin()->sendDataPacket(self::$packetCache ?? throw new \LogicException("Packet cache is null"));
			}
		}, EventPriority::MONITOR, $plugin);
		Server::getInstance()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event) : void{
			foreach($event->getPackets() as $packet){
				if($packet instanceof StartGamePacket){
					$experiments = $packet->levelSettings->experiments->getExperiments();
					$experiments["experimental_creator_camera"] = true;//It seems to work without it.
					$packet->levelSettings->experiments = new Experiments($experiments, true);
				}
			}
		}, EventPriority::HIGHEST, $plugin);
		self::$registered = true;
	}

	public static function parseEaseType(string $type) : int{
		return match($type){
			"linear" => CameraSetInstructionEaseType::LINEAR,
			"spring" => CameraSetInstructionEaseType::SPRING,
			"in_quad" => CameraSetInstructionEaseType::IN_QUAD,
			"out_quad" => CameraSetInstructionEaseType::OUT_QUAD,
			"in_out_quad" => CameraSetInstructionEaseType::IN_OUT_QUAD,
			"in_cubic" => CameraSetInstructionEaseType::IN_CUBIC,
			"out_cubic" => CameraSetInstructionEaseType::OUT_CUBIC,
			"in_out_cubic" => CameraSetInstructionEaseType::IN_OUT_CUBIC,
			"in_quart" => CameraSetInstructionEaseType::IN_QUART,
			"out_quart" => CameraSetInstructionEaseType::OUT_QUART,
			"in_out_quart" => CameraSetInstructionEaseType::IN_OUT_QUART,
			"in_quint" => CameraSetInstructionEaseType::IN_QUINT,
			"out_quint" => CameraSetInstructionEaseType::OUT_QUINT,
			"in_out_quint" => CameraSetInstructionEaseType::IN_OUT_QUINT,
			"in_sine" => CameraSetInstructionEaseType::IN_SINE,
			"out_sine" => CameraSetInstructionEaseType::OUT_SINE,
			"in_out_sine" => CameraSetInstructionEaseType::IN_OUT_SINE,
			"in_expo" => CameraSetInstructionEaseType::IN_EXPO,
			"out_expo" => CameraSetInstructionEaseType::OUT_EXPO,
			"in_out_expo" => CameraSetInstructionEaseType::IN_OUT_EXPO,
			"in_circ" => CameraSetInstructionEaseType::IN_CIRC,
			"out_circ" => CameraSetInstructionEaseType::OUT_CIRC,
			"in_out_circ" => CameraSetInstructionEaseType::IN_OUT_CIRC,
			"in_bounce" => CameraSetInstructionEaseType::IN_BOUNCE,
			"out_bounce" => CameraSetInstructionEaseType::OUT_BOUNCE,
			"in_out_bounce" => CameraSetInstructionEaseType::IN_OUT_BOUNCE,
			"in_back" => CameraSetInstructionEaseType::IN_BACK,
			"out_back" => CameraSetInstructionEaseType::OUT_BACK,
			"in_out_back" => CameraSetInstructionEaseType::IN_OUT_BACK,
			"in_elastic" => CameraSetInstructionEaseType::IN_ELASTIC,
			"out_elastic" => CameraSetInstructionEaseType::OUT_ELASTIC,
			"in_out_elastic" => CameraSetInstructionEaseType::IN_OUT_ELASTIC,
			default => throw new InvalidArgumentException("Invalid ease type: {$type}")
		};
	}
}