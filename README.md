<img src="https://github.com/WolvesFortress/libcamera/raw/master/libcamera.png" width="100" align="left" style="margin-right: 1em;position: relative;z-index: 1000;" alt="libcamera icon"/>

# libcamera

A small library for handling camera instructions added in Minecraft Bedrock Edition 1.19.30.

See [the official documentation](https://learn.microsoft.com/en-us/minecraft/creator/documents/camerasystem/cameracommandintroduction?view=minecraft-bedrock-stable)
for more information.

## Installation

### Composer

To install this library through composer, run the following command:

`composer require wolvesfortress/libcamera`

### Virion as Phar

The virion for this library can be found [on Poggit](https://poggit.pmmp.io/ci/WolvesFortress/libcamera/).

## Usage

Here is a basic example on how this library is used:

### Registering the virion

In your main plugin file, register the virion like so:

```php
use muqsit\libcamera\libcamera;
use pocketmine\plugin\PluginBase;

class MyPlugin extends PluginBase{

	public function onEnable() : void{
		if(!libcamera::isRegistered()){
			libcamera::register($this);
		}
	}

	// ...
}
```

### Sending instructions

To send instructions to the camera, use the following code:

- Set

<details align="center">
	<summary>See demo</summary>

https://github.com/user-attachments/assets/338e4b84-1ded-4424-9ffb-6e94298bc53c

</details>


```php
use muqsit\libcamera\libcamera;
use muqsit\libcamera\CameraInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEase;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEaseType;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionRotation;
use pocketmine\network\mcpe\protocol\types\camera\Vector3;
use pocketmine\player\Player;

// ...
if($player instanceof Player && $player->isOnline()){
	/**
	 * @phpstan-param CameraPreset $preset
	 * @phpstan-param CameraSetInstructionEase|null $ease
	 * @phpstan-param Vector3|null $camera_pos
	 * @phpstan-param CameraSetInstructionRotation|null $rot
	 * @phpstan-param Vector3|null $facing_pos
	 */
	CameraInstruction::set(
		preset: libcamera::getPresetRegistry()->registered["target"],
		ease: new CameraSetInstructionEase(
			CameraSetInstructionEaseType::IN_OUT_CUBIC,
			(float) 5.0 // duration (sec)
		),
		camera_pos: $player->getPosition()->add(0.0, $player->getEyeHeight(), 0.0), //Without it, the camera will teleport into subspace
		rot: new CameraSetInstructionRotation(
			(float)20.0, //pitch
			(float)180.0 //yaw
		),
		facing_pos: null
	)->send($player);
}
```

- Fade

<details align="center">
	<summary>See demo</summary>

https://github.com/user-attachments/assets/01bfc489-16bd-4424-aad0-32abb81d7517

</details>

```php
use muqsit\libcamera\libcamera;
use muqsit\libcamera\CameraInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\player\Player;

// ...
if($player instanceof Player && $player->isOnline()){
		$fadeInTime = 5;
		$stayTime = 2;
		$fadeOutTime = 2;
		$r = 0;
		$g = 0;
		$b = 0;

	/** 
	 * @phpstan-param CameraFadeInstructionColor|null $color 
	 * @phpstan-param CameraFadeInstructionTime|null $time
	 */
	CameraInstruction::fade(
		color: new CameraFadeInstructionColor((float)$r,(float)$g,(float)$b),
		time: new CameraFadeInstructionTime((float)$fadeInTime,(float)$stayTime,(float)$fadeOutTime)
	)->send($player);
}
```

- Target

After setting the camera to free mode, you need to explicitly assign a target.  
This allows the camera to visually track a specific entity.  
Unlike SetActorLinkPacket, it does not follow the entity automatically.  

<details align="center">
	<summary>See demo</summary>

https://github.com/user-attachments/assets/38cd6bf1-f666-4635-870b-2d51b12bfa3f

</details>

```php
use pocketmine\event\player\PlayerInteractEvent;
use muqsit\libcamera\CameraInstruction;
use pocketmine\entity\Zombie;
use muqsit\libcamera\libcamera;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEase;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEaseType;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionRotation;
use pocketmine\math\Vector3;

// ...

	/** @var array<string, true> */
	private $set = [];

	public function ina(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		if(!$player->isSneaking()){
			return;
		}

		//Removes camera tracking. Note that target and free cameras are managed separately.
		if(isset($this->set[$player->getName()])){
			CameraInstruction::removeTarget()->send($player);
			CameraInstruction::clear()->send($player);
			unset($this->set[$player->getName()]);
			return;
		}

		//Find the most different zombie entities
		$nearest = null;
		$nearestDistance = PHP_INT_MAX;
		foreach($player->getWorld()->getEntities() as $entity){
			if($entity instanceof Zombie){
				$distance = $player->getPosition()->distance($entity->getPosition());
				if($nearestDistance >= $distance){
					$nearest = $entity;
					$nearestDistance = $distance;
				}
			}
		}

		if($nearest === null){
			$player->sendMessage("No Zombie");
			return;
		}

		//
		CameraInstruction::set(
			preset: libcamera::getPresetRegistry()->registered["target"],
			ease: new CameraSetInstructionEase(
				CameraSetInstructionEaseType::IN_OUT_CUBIC,
				(float) 5.0 // duration (sec)
			),
			camera_pos: $player->getPosition()->add(0, $player->getEyeHeight(), 0),
			rot: new CameraSetInstructionRotation(
				(float) $player->getLocation()->getPitch(), //pitch
				(float) $player->getLocation()->getYaw() //yaw
			),
			facing_pos: null
		)->send($player);

		//To use CameraInstruction::target you first need to make it a free camera.
		CameraInstruction::target(
			targetCenterOffset: Vector3::zero(), // no offset
			actorUniqueId: $nearest->getId() // for example target the player
		)->send($player);

		//Manages which packets have been sent
		$this->set[$player->getName()] = true;
	}
```

- Remove Target

```php
use muqsit\libcamera\libcamera;
use muqsit\libcamera\CameraInstruction;
use pocketmine\player\Player;

// ...
if($player instanceof Player && $player->isOnline()){
	CameraInstruction::removeTarget()->send($player);
}
```

- Clear

```php
use muqsit\libcamera\libcamera;
use muqsit\libcamera\CameraInstruction;
use pocketmine\player\Player;

// ...
if($player instanceof Player && $player->isOnline()){
	CameraInstruction::clear()->send($player);
}
```

- Multi

```php
use muqsit\libcamera\libcamera;
use muqsit\libcamera\CameraInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

// ...
if($player instanceof Player && $player->isOnline()){
	CameraInstruction::multi(
		CameraInstruction::target(
			targetCenterOffset: Vector3::zero(),
			actorUniqueId: $player->getId()
		),
		CameraInstruction::fade(
			color: new CameraFadeInstructionColor((float)$r,(float)$g,(float)$b),
			time: new CameraFadeInstructionTime((float)$fadeInTime,(float)$stayTime,(float)$fadeOutTime)
		)
	)->send($player);
}
```

## Roadmap

At the moment, there are a few improvements that can be/or are being worked on. Here is a list of some of those
improvements:

- [ ] Allow registering new camera presets

## Issues

Any issues/suggestion can be reported [here](https://github.com/WolvesFortress/libcamera/issues).

## Credits

- [Muqsit](https://github.com/Muqsit): Creator of the library
- [inxomnyaa](https://github.com/inxomnyaa): Current maintainer