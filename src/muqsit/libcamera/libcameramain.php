<?php
declare(strict_types=1);


namespace muqsit\libcamera;

use pocketmine\plugin\PluginBase;

class libcameramain extends PluginBase{
	protected function onEnable() : void{
		if(!libcamera::isRegistered()){
			libcamera::register($this);
		}
	}
}