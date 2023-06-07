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

namespace revivalpmmp\pureentities\entity\animal\walking;

use pocketmine\entity\Creature;
use pocketmine\entity\Rideable;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use revivalpmmp\pureentities\components\BreedingComponent;
use revivalpmmp\pureentities\components\MobEquipment;
use revivalpmmp\pureentities\data\Data;
use revivalpmmp\pureentities\entity\monster\Monster;
use revivalpmmp\pureentities\entity\monster\WalkingMonster;
use revivalpmmp\pureentities\features\IntfCanBreed;
use revivalpmmp\pureentities\features\IntfCanEquip;
use revivalpmmp\pureentities\PureEntities;
use revivalpmmp\pureentities\traits\Breedable;
use revivalpmmp\pureentities\traits\Feedable;
use revivalpmmp\pureentities\utils\MobDamageCalculator;

class SkeletonHorse extends WalkingMonster implements Rideable{
	
	/**
	 * @var MobEquipment
	 */
	private $mobEquipment;

	// Base created from Horse
	// TODO Implement specific Methods for Skeleton Horse.

	const NETWORK_ID = Data::NETWORK_IDS["skeleton_horse"];

	public function initEntity() : void{
		parent::initEntity();
	}

	public function getName() : string{
		return "SkeletonHorse";
	}

	public function targetOption(Creature $creature, float $distance) : bool{
		if($creature instanceof Player){
			return $creature->spawned && $creature->isAlive() && !$creature->isClosed() && $creature->getInventory()->getItemInHand()->getId() == Item::APPLE && $distance <= 49;
		}
		return false;
	}

	public function getDrops() : array{
		if($this->isLootDropAllowed()){
			return [Item::get(Item::LEATHER, 0, mt_rand(0, 2))];
		}else{
			return [];
		}
	}

	public function updateXpDropAmount() : void{
		$this->xpDropAmount = mt_rand(1, 3);
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
		if($this->getMobEquipment() !== null){
			$reduceDamagePercent = $this->getMobEquipment()->getArmorDamagePercentToReduce();
		}
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
			if($this->getMobEquipment() !== null){
				$damage = $damage + $this->getMobEquipment()->getWeaponDamageToAdd();
			}
			$ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK,
				MobDamageCalculator::calculateFinalDamage($player, $damage));
			$player->attack($ev);

			$this->checkTamedMobsAttack($player);
		}
	}

    // -------------------- equipment methods --------------------

	/**
	 * @return MobEquipment
	 */
	public function getMobEquipment() : MobEquipment{
		return $this->mobEquipment;
	}

	/**
	 * @return array
	 */
	public function getPickupLoot() : array{
		return $this->pickUpLoot;
	}

	/**
	 * @return null
	 */
	public function getRidePosition(){
		return null;
	}
}