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
use muqsit\libcamera\CameraPresetRegistry;

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
		preset: CameraPresetRegistry::TARGET(),
		ease: new CameraSetInstructionEase(
			CameraSetInstructionEaseType::IN_OUT_CUBIC,
			(float) 5.0 // duration (sec)
		),
		camera_pos: $player->getPosition()->add(0.0, $player->getEyeHeight(), 0.0), //Without it, the camera will teleport into subspace
		rot: new CameraSetInstructionRotation(
			(float)$player->getLocation()->getPitch(), //pitch
			(float)$player->getLocation()->getYaw() //yaw
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
use muqsit\libcamera\CameraPresetRegistry;
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
			preset: CameraPresetRegistry::TARGET(),
			ease: null,
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

This doesn't work

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

# camera technic

- use

```php
use pocketmine\event\player\PlayerItemUseEvent;
use muqsit\libcamera\CameraInstruction;
use muqsit\libcamera\libcamera;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEase;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEaseType;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionRotation;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\entity\Zombie;
use muqsit\libcamera\CameraPresetRegistry;
```

## linear-demo

Want to move the camera freely? Use ease!   

<details align="center">
	<summary>See demo</summary>

https://github.com/user-attachments/assets/d5ca8d67-1ac6-4d2c-8051-db3455317cd6

</details>

```php
	public function onUse(PlayerItemUseEvent $event) : void{
		$player = $event->getPlayer();
		if(!$player->isSneaking()){
			return;
		}
		if($player instanceof Player&&$player->isOnline()){
			//linear

			$do = $player->getDirectionVector()->multiply(10);

			CameraInstruction::multi(
				CameraInstruction::set(
					preset: CameraPresetRegistry::FREE(),
					ease: null,
					camera_pos: $player->getPosition()->add(0.0, $player->getEyeHeight(), 0.0), //Without it, the camera will teleport into subspace
					rot: new CameraSetInstructionRotation(
						(float) $player->getLocation()->getPitch(), //pitch
						(float) $player->getLocation()->getYaw() //yaw
					),
					facing_pos: null
				),
				CameraInstruction::set(
					preset: CameraPresetRegistry::FREE(),
					ease:  new CameraSetInstructionEase(
						CameraSetInstructionEaseType::LINEAR,
						(float) 7.0 // duration (sec)
					),
					camera_pos: $player->getPosition()->add(0.0, $player->getEyeHeight(), 0.0)->addVector($do), //Without it, the camera will teleport into subspace
					rot: new CameraSetInstructionRotation(
						(float) $player->getLocation()->getPitch(), //pitch
						(float) $player->getLocation()->getYaw() //yaw
					),
					facing_pos: null
				)
			)->send($player);

			$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player){
				CameraInstruction::clear()->send($player);
			}), 20 * 7);
		}
	}
```
 
Usage Example in Minecraft:  
When using minecraft:free with an ease parameter, you can move the free camera smoothly to a specified endpoint over a given duration.  
The easing functions listed above determine how the camera moves.   
  
Reference:  
https://bacchigames.club/mc/howtocamera.html


| Easing Name    | Behavior Description                                           | See Demo                    | IsCrash |
|----------------|----------------------------------------------------------------|-----------------------------|---------|
| in_back        | Moves slightly backward before heading to the endpoint         | [see](#in_back-demo)        | ❌       |
| out_back       | Slightly overshoots the endpoint and returns                   | [see](#out_back-demo)       | ✅       |
| in_out_back    | Combines both in and out behaviors                             | [see](#in_out_back-demo)    | ✅       |
| in_bounce      | Bounces 3 times before heading to the endpoint on the 4th time | [see](#in_bounce-demo)      | ❌       |
| out_bounce     | Bounces 3 times and stops at the endpoint on the 4th time      | [see](#out_bounce-demo)     | ❌       |
| in_out_bounce  | Combines both in and out bounce behaviors                      | [see](#in_out_bounce-demo)  | ❌       |
| in_circ        | Accelerates toward the endpoint                                | [see](#in_circ-demo)        | ❌       |
| out_circ       | Decelerates toward the endpoint                                | [see](#out_circ-demo)       | ❌       |
| in_out_circ    | Combines both in and out circ behaviors                        | [see](#in_out_circ-demo)    | ❌       |
| in_cubic       | Accelerates toward the endpoint                                | [see](#in_cubic-demo)       | ❌       |
| out_cubic      | Decelerates toward the endpoint                                | [see](#out_cubic-demo)      | ❌       |
| in_out_cubic   | Combines both in and out cubic behaviors                       | [see](#in_out_cubic-demo)   | ❌       |
| in_elastic     | Oscillates 3 times and heads to the endpoint on the 4th time   | [see](#in_elastic-demo)     | ❌       |
| out_elastic    | Oscillates 3 times and stops at the endpoint on the 4th time   | [see](#out_elastic-demo)    | ❌       |
| in_out_elastic | Combines both in and out elastic behaviors                     | [see](#in_out_elastic-demo) | ❌       |
| in_expo        | Accelerates toward the endpoint                                | [see](#in_expo-demo)        | ❌       |
| out_expo       | Decelerates toward the endpoint                                | [see](#out_expo-demo)       | ❌       |
| in_out_expo    | Combines both in and out expo behaviors                        | [see](#in_out_expo-demo)    | ❌       |
| in_quad        | Accelerates toward the endpoint                                | [see](#in_quad-demo)        | ❌       |
| out_quad       | Decelerates toward the endpoint                                | [see](#out_quad-demo)       | ❌       |
| in_out_quad    | Combines both in and out quad behaviors                        | [see](#in_out_quad-demo)    | ❌       |
| in_quart       | Accelerates toward the endpoint                                | [see](#in_quart-demo)       | ❌       |
| out_quart      | Decelerates toward the endpoint                                | [see](#out_quart-demo)      | ❌       |
| in_out_quart   | Combines both in and out quart behaviors                       | [see](#in_out_quart-demo)   | ❌       |
| in_quint       | Accelerates toward the endpoint                                | [see](#in_quint-demo)       | ❌       |
| out_quint      | Decelerates toward the endpoint                                | [see](#out_quint-demo)      | ❌       |
| in_out_quint   | Combines both in and out quint behaviors                       | [see](#in_out_quint-demo)   | ❌       |
| in_sine        | Accelerates toward the endpoint                                | [see](#in_sine-demo)        | ❌       |
| out_sine       | Decelerates toward the endpoint                                | [see](#out_sine-demo)       | ❌       |
| in_out_sine    | Combines both in and out sine behaviors                        | [see](#in_out_sine-demo)    | ❌       |
| linear         | Moves from start to end at constant speed                      | [see](#linear-demo)         | ❌       |
| spring         | Slightly oscillates around the endpoint                        | [see](#spring-demo)         | ❌       |



## facing_pos

Do you want to move while looking at a point? Use facing_pos!  

<details align="center">
	<summary>See demo</summary>

https://github.com/user-attachments/assets/1f5d73c2-073a-4777-8b13-8ee6c6badefb

</details>

```php
	public function onUse(PlayerItemUseEvent $event) : void{
		$player = $event->getPlayer();
		if(!$player->isSneaking()){
			return;
		}
		if($player instanceof Player&&$player->isOnline()){
			//linear

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


			$do = $player->getDirectionVector()->multiply(10);

			CameraInstruction::multi(
				CameraInstruction::set(
					preset: CameraPresetRegistry::FREE(),
					ease: null,
					camera_pos: $player->getPosition()->add(0.0, $player->getEyeHeight(), 0.0), //Without it, the camera will teleport into subspace
					rot: new CameraSetInstructionRotation(
						(float) $player->getLocation()->getPitch(), //pitch
						(float) $player->getLocation()->getYaw() //yaw
					),
					facing_pos: $nearest->getLocation()->asVector3()->add(0, $nearest->getEyeHeight(), 0),
				),
				CameraInstruction::set(
					preset: CameraPresetRegistry::FREE(),
					ease: new CameraSetInstructionEase(
						CameraSetInstructionEaseType::LINEAR,
						(float) 7.0 // duration (sec)
					),
					camera_pos: $player->getPosition()->add(0.0, $player->getEyeHeight(), 0.0)->addVector($do), //Without it, the camera will teleport into subspace
					rot: new CameraSetInstructionRotation(
						(float) $player->getLocation()->getPitch(), //pitch
						(float) $player->getLocation()->getYaw() //yaw
					),
					facing_pos: $nearest->getLocation()->asVector3()->add(0, $nearest->getEyeHeight(), 0),
				)
			)->send($player);

			$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player){
				CameraInstruction::clear()->send($player);
			}), 20 * 7);
		}
	}
```

# demos

For convenience, we will use this common code for the demo.  

```php
	public function onDemo(Entity $player, int $easeType) : void{
		if($player instanceof Player&&$player->isOnline()){
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

			$player->setInvisible(true);
			$nearest->despawnFrom($player);

			CameraInstruction::multi(
				CameraInstruction::set(
					preset: CameraPresetRegistry::FREE(),
					ease: null,
					camera_pos: $player->getPosition()->add(0.0, $player->getEyeHeight(), 0.0), //Without it, the camera will teleport into subspace
					rot: new CameraSetInstructionRotation(
						(float) $player->getLocation()->getPitch(), //pitch
						(float) $player->getLocation()->getYaw() //yaw
					),
					facing_pos:null,
				),
				CameraInstruction::set(
					preset: CameraPresetRegistry::FREE(),
					ease: new CameraSetInstructionEase(
						$easeType,
						(float) 10.0 // duration (sec)
					),
					camera_pos: $nearest->getPosition()->add(0.0, $player->getEyeHeight(), 0.0), //Without it, the camera will teleport into subspace
					rot: new CameraSetInstructionRotation(
						(float) $player->getLocation()->getPitch(), //pitch
						(float) $player->getLocation()->getYaw() //yaw
					),
					facing_pos: null,
				)
			)->send($player);

			$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($nearest, $player){
				CameraInstruction::clear()->send($player);
				$player->setInvisible(false);
				$nearest->despawnFrom($player);
			}), 20 * 10);
		}
	}
```


## in_back-demo

<details align="center">
	<summary>see Code</summary>

```php
	public function onUse(PlayerItemUseEvent $event) : void{
		$player = $event->getPlayer();
		if(!$player->isSneaking()){
			return;
		}
		$this->onDemo($player, CameraSetInstructionEaseType::IN_BACK);
	}
```

</details>

<br>

<details align="center">
	<summary>See demo</summary>

https://github.com/user-attachments/assets/67924c3f-f7c8-4216-b11f-943bf5149de9

</details>
---


## out_back-demo


### WARNING: ⚠️ Crashes client on 1.21.93

<details align="center">

<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::OUT_BACK);
}
```

</details>

<br>


<!--details align="center">

<summary>See demo</summary>

[see](#out_back-demo)

</details-->

---

## in_out_back-demo

### WARNING: ⚠️ Crashes client on 1.21.93

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_OUT_BACK);
}
```

</details>

<br>

<!--details align="center">
<summary>See demo</summary>

[see](#in_out_back-demo)

</details-->

---

## in_bounce-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_BOUNCE);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/4f55e402-e27f-4e20-897a-3fd199561498

</details>

---

## out_bounce-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::OUT_BOUNCE);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/365555eb-6193-4121-ac71-b300b857edae

</details>

---

## in_out_bounce-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_OUT_BOUNCE);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/a4a8f132-d31f-4187-9769-b5ea544a5d56

</details>

---

## in_circ-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_CIRC);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/8edb854e-ad07-47d6-ab08-792954209ffe

</details>

---

## out_circ-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::OUT_CIRC);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/73c3bae0-1026-49be-855f-fdf53120bb55

</details>

---

## in_out_circ-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_OUT_CIRC);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/76626fef-d97a-4ed7-aa8a-d54a069c073c

</details>

---

## in_cubic-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_CUBIC);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/1ec8c85b-2a92-4cf2-9f3c-6b4aa8b150db

</details>

---

## out_cubic-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::OUT_CUBIC);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/02d67db4-d6c8-4062-b1f1-9b81f0e99012

</details>

---

## in_out_cubic-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_OUT_CUBIC);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

https://github.com/user-attachments/assets/002867af-8f93-4ca7-b34b-5ab0a9a17855

</details>

---

## in_quart-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_QUART);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

[see](#in_quart-demo)

</details>

---

## out_quart-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::OUT_QUART);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

[see](#out_quart-demo)

</details>

---

## in_out_quart-demo

<details align="center">
<summary>see Code</summary>

```php
public function onUse(PlayerItemUseEvent $event) : void{
	$player = $event->getPlayer();
	if(!$player->isSneaking()){
		return;
	}
	$this->onDemo($player, CameraSetInstructionEaseType::IN_OUT_QUART);
}
```

</details>

<br>

<details align="center">
<summary>See demo</summary>

[see](#in_out_quart-demo)

</details>


## Roadmap

At the moment, there are a few improvements that can be/or are being worked on. Here is a list of some of those
improvements:

- [x] Allow registering new camera presets

## Issues

Any issues/suggestion can be reported [here](https://github.com/WolvesFortress/libcamera/issues).

## Credits

- [Muqsit](https://github.com/Muqsit): Creator of the library
- [inxomnyaa](https://github.com/inxomnyaa): Current maintainer