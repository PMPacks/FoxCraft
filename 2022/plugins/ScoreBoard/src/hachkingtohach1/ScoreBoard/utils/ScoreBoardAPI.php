<?php

declare(strict_types=1);

namespace hachkingtohach1\ScoreBoard\utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use BadFunctionCallException;
use OutOfBoundsException;
use function mb_strtolower;

class ScoreBoardAPI {

	private const OBJECTIVE_NAME = "objective";
	private const CRITERIA_NAME = "dummy";
	private const MIN_LINES = 1;
	private const MAX_LINES = 15;
	public const SORT_ASCENDING = 0;
	public const SORT_DESCENDING = 1;
	public const SLOT_LIST = "list";
	public const SLOT_SIDEBAR = "sidebar";
	public const SLOT_BELOW_NAME = "belowname";
	private static $scoreboards = [];

	public static function setScore(Player $player, string $displayName, int $slotOrder = self::SORT_ASCENDING, string $displaySlot = self::SLOT_SIDEBAR, string $objectiveName = self::OBJECTIVE_NAME, string $criteriaName = self::CRITERIA_NAME): void{
		if(isset(self::$scoreboards[mb_strtolower($player->getName())])){
			self::removeScore($player);
		}

		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = $displaySlot;
		$pk->objectiveName = $objectiveName;
		$pk->displayName = $displayName;
		$pk->criteriaName = $criteriaName;
		$pk->sortOrder = $slotOrder;
		$player->getNetworkSession()->sendDataPacket($pk);

		self::$scoreboards[mb_strtolower($player->getName())] = $objectiveName;
	}

	public static function removeScore(Player $player): void{
		$objectiveName = self::$scoreboards[mb_strtolower($player->getName())] ?? self::OBJECTIVE_NAME;

		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $objectiveName;
		$player->getNetworkSession()->sendDataPacket($pk);

		unset(self::$scoreboards[mb_strtolower($player->getName())]);
	}

	public static function getScoreboards(): array{
		return self::$scoreboards;
	}

	public static function hasScore(Player $player): bool{
		return isset(self::$scoreboards[mb_strtolower($player->getName())]);
	}

	public static function setScoreLine(Player $player, int $line, string $message, int $type = ScorePacketEntry::TYPE_FAKE_PLAYER): void{
		if(!isset(self::$scoreboards[mb_strtolower($player->getName())])){
			throw new \BadFunctionCallException("Cannot set a score to a player without a scoreboard");
		}
		
		if($line > 15 || $line < 1){
            return;
        }

		$entry = new ScorePacketEntry();
		$entry->objectiveName = self::$scoreboards[mb_strtolower($player->getName())] ?? self::OBJECTIVE_NAME;
		$entry->type = $type;
		$entry->customName = $message;
		$entry->score = $line;
		$entry->scoreboardId = $line;

		$pk = new SetScorePacket();
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->getNetworkSession()->sendDataPacket($pk);
	}
}