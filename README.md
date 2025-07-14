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
		camera_pos: null,
		rot: new CameraSetInstructionRotation(
			(float)20.0, //pitch
			(float)180.0 //yaw
		),
		facing_pos: null
	)->send($player);
}
```

- Fade

```php
use muqsit\libcamera\libcamera;
use muqsit\libcamera\CameraInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;
use pocketmine\player\Player;

// ...
if($player instanceof Player && $player->isOnline()){
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

```php
use muqsit\libcamera\libcamera;
use muqsit\libcamera\CameraInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraTargetInstruction;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

// ...
if($player instanceof Player && $player->isOnline()){
	/** 
	 * @phpstan-param Vector3|null $targetCenterOffset
	 * @phpstan-param int $actorUniqueId
	 */
	CameraInstruction::target(
		targetCenterOffset: Vector3::zero(), // no offset
		actorUniqueId: $player->getId() // for example target the player
	)->send($player);
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