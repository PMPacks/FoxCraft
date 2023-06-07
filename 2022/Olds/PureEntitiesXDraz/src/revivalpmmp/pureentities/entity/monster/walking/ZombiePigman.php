<?php

/**
 * PureEntitiesX: Mob AI Plugin for PMMP
 * Copyright (C)  2018 RevivalPMMP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace revivalpmmp\pureentities\entity\monster\walking;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\Player;
use revivalpmmp\pureentities\components\BreedingComponent;
use revivalpmmp\pureentities\components\MobEquipment;
use revivalpmmp\pureentities\data\Data;
use revivalpmmp\pureentities\data\NBTConst;
use revivalpmmp\pureentities\entity\monster\Monster;
use revivalpmmp\pureentities\entity\monster\WalkingMonster;
use revivalpmmp\pureentities\features\IntfCanBreed;
use revivalpmmp\pureentities\features\IntfCanEquip;
use revivalpmmp\pureentities\PluginConfiguration;
use revivalpmmp\pureentities\PureEntities;
use revivalpmmp\pureentities\traits\Breedable;
use revivalpmmp\pureentities\traits\Feedable;
use revivalpmmp\pureentities\utils\MobDamageCalculator;

//use pocketmine\event\Timings;

class ZombiePigman extends WalkingMonster implements IntfCanBreed, Monster{

	// Base Derived from Zombie
	// TODO Update Methods to be Zombie Pigman Specific
	// TODO Add Golden Sword


	use Breedable, Feedable;
	const NETWORK_ID = Data::NETWORK_IDS["zombie_pigman"];

	/**
	 * @var int
	 */
	private $angryValue = 0;

	/**
	 * @var MobEquipment
	 */
	private $mobEquipment;

	/**
	 * Not a complete list yet ...
	 *
	 * @var array
	 */
	private $pickUpLoot = [];

	public function initEntity() : void{
		parent::initEntity();
		$this->speed = 1.1;
		$this->setDamage([0, 2, 3, 4]);

		$this->feedableItems = [];

		$this->breedableClass = new BreedingComponent($this);
		$this->breedableClass->init();
		//$this->mobEquipment->setMainHand(Item::get(ItemIds::GOLDEN_SWORD));
	}

	/**
	 * Loads data from NBT and stores to local variables
	 */
	public function loadNBT(){
		if(PluginConfiguration::getInstance()->getEnableNBT()){
			parent::loadNBT();
			if($this->namedtag->hasTag(NBTConst::NBT_KEY_ANGRY)){
				$angry = $this->namedtag->getInt(NBTConst::NBT_KEY_ANGRY, 0, true);
				$this->setAngry($angry);
			}
		}
	}

	// TODO: Determine cause of collar color being improperly applied.

	/**
	 * Saves important variables to the NBT
	 */
	public function saveNBT() : void{
		if(PluginConfiguration::getInstance()->getEnableNBT()){
			parent::saveNBT();
			$this->namedtag->setInt(NBTConst::NBT_KEY_ANGRY, $this->angryValue, true);
		}
		$this->breedableClass->saveNBT();
	}

	/**
	 * @return bool
	 */
	public function isAngry() : bool{
		return $this->angryValue > 0;
	}

	/**
	 * @param int  $val
	 * @param bool $init
	 */
	public function setAngry(int $val, bool $init = false){
		if($val < 0){
			$val = 0;
		}
		$valueBefore = $this->angryValue;
		$this->angryValue = $val;
		// only change the data property when aggression mode changes or in init phase
		if(($valueBefore > 0 and $val <= 0) or ($valueBefore <= 0 and $val > 0) or $init){
			$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ANGRY, $val > 0);
		}
	}

	protected function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);
	}

	public function getName() : string{
		return "ZombiePigman";
	}
	
	/**
	 * Zombie gets attacked. We need to recalculate the damage done with reducing the damage by armor type.
	 *
	 * @param EntityDamageEvent $source
	 */
	public function attack(EntityDamageEvent $source) : void{
		$damage = $this->getDamage();
		PureEntities::logOutput("$this: attacked with original damage of $damage");
		$reduceDamagePercent = 0;
		if($reduceDamagePercent > 0){
			$reduceBy = $damage * $reduceDamagePercent / 100;
			PureEntities::logOutput("$this: reduce damage by $reduceBy");
			$damage = $damage - $reduceBy;
		}

		PureEntities::logOutput("$this: attacked with final damage of $damage");

		parent::attack($source);
	}

	/**
	 * This zombie attacks a player
	 *
	 * @param Entity $player
	 */
	public function attackEntity(Entity $player){
		if($this->attackDelay > 10 && $this->distanceSquared($player) < 2){
			$this->attackDelay = 0;
			// maybe this needs some rework ... as it should be calculated within the event class and take
			// mob's weapon into account. for now, i just add the damage from the weapon the mob wears
			$damage = $this->getDamage();
			$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK,
				MobDamageCalculator::calculateFinalDamage($player, $damage));
			$player->attack($ev);

			$this->checkTamedMobsAttack($player);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->isClosed()) return false;
		// Timings::$timerEntityBaseTick->startTiming();

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$time = $this->getLevel() !== null ? $this->getLevel()->getTime() % Level::TIME_FULL : Level::TIME_NIGHT;
		// Timings::$timerEntityBaseTick->stopTiming();
		return $hasUpdate;
	}

	public function getDrops() : array{
		return [];
	}

	public function updateXpDropAmount() : void{

	}

	/**
	 * @return array
	 */
	public function getPickupLoot() : array{
		return $this->pickUpLoot;
	}

}
