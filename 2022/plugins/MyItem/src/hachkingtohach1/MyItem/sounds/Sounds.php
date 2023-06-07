<?php

declare(strict_types=1);

namespace hachkingtohach1\MyItem\sounds;

use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket; 
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class Sounds {
	
	public const KEY_SOUND = "minecraft->register|sounds->register";	
	public $levelEventPacket = [
	    "EVENT_SOUND_CLICK" => 1000,
	    "EVENT_SOUND_CLICK_FAIL" => 1001,
	    "EVENT_SOUND_SHOOT" => 1002,
	    "EVENT_SOUND_DOOR" => 1003,
	    "EVENT_SOUND_FIZZ" => 1004,
	    "EVENT_SOUND_IGNITE" => 1005,
	    "EVENT_SOUND_GHAST" => 1007,
	    "EVENT_SOUND_GHAST_SHOOT" => 1008,
	    "EVENT_SOUND_BLAZE_SHOOT" => 1009,
	    "EVENT_SOUND_DOOR_BUMP" => 1010,
	    "EVENT_SOUND_DOOR_CRASH" => 1012,
	    "EVENT_SOUND_ENDERMAN_TELEPORT" => 1018,
	    "EVENT_SOUND_ANVIL_BREAK" => 1020,
	    "EVENT_SOUND_ANVIL_USE" => 1021,
	    "EVENT_SOUND_ANVIL_FALL" => 1022,
	    "EVENT_SOUND_POP" => 1030,
	    "EVENT_SOUND_PORTAL" => 1032,
	    "EVENT_SOUND_ITEMFRAME_ADD_ITEM" => 1040,
	    "EVENT_SOUND_ITEMFRAME_REMOVE" => 1041,
	    "EVENT_SOUND_ITEMFRAME_PLACE" => 1042,
	    "EVENT_SOUND_ITEMFRAME_REMOVE_ITEM" => 1043,
	    "EVENT_SOUND_ITEMFRAME_ROTATE_ITEM" => 1044,
	    "EVENT_SOUND_CAMERA" => 1050,
	    "EVENT_SOUND_ORB" => 1051,
	    "EVENT_SOUND_TOTEM" => 1052,
	    "EVENT_SOUND_ARMOR_STAND_BREAK" => 1060,
	    "EVENT_SOUND_ARMOR_STAND_HIT" => 1061,
	    "EVENT_SOUND_ARMOR_STAND_FALL" => 1062,
	    "EVENT_SOUND_ARMOR_STAND_PLACE" => 1063
	];
	
	public $levelSoundEventPacket = [
	    "SOUND_ITEM_USE_ON" => 0,
	    "SOUND_HIT" => 1,
	    "SOUND_STEP" => 2,
	    "SOUND_FLY" => 3,
	    "SOUND_JUMP" => 4,
	    "SOUND_BREAK" => 5,
	    "SOUND_PLACE" => 6,
	    "SOUND_HEAVY_STEP" => 7,
	    "SOUND_GALLOP" => 8,
	    "SOUND_FALL" => 9,
	    "SOUND_AMBIENT" => 10,
	    "SOUND_AMBIENT_BABY" => 11,
	    "SOUND_AMBIENT_IN_WATER" => 12,
	    "SOUND_BREATHE" => 13,
	    "SOUND_DEATH" => 14,
	    "SOUND_DEATH_IN_WATER" => 15,
	    "SOUND_DEATH_TO_ZOMBIE" => 16,
	    "SOUND_HURT" => 17,
	    "SOUND_HURT_IN_WATER" => 18,
	    "SOUND_MAD" => 19,
	    "SOUND_BOOST" => 20,
	    "SOUND_BOW" => 21,
	    "SOUND_SQUISH_BIG" => 22,
	    "SOUND_SQUISH_SMALL" => 23,
	    "SOUND_FALL_BIG" => 24,
	    "SOUND_FALL_SMALL" => 25,
	    "SOUND_SPLASH" => 26,
	    "SOUND_FIZZ" => 27,
	    "SOUND_FLAP" => 28,
	    "SOUND_SWIM" => 29,
	    "SOUND_DRINK" => 30,
	    "SOUND_EAT" => 31,
	    "SOUND_TAKEOFF" => 32,
	    "SOUND_SHAKE" => 33,
	    "SOUND_PLOP" => 34,
	    "SOUND_LAND" => 35,
	    "SOUND_SADDLE" => 36,
	    "SOUND_ARMOR" => 37,
	    "SOUND_MOB_ARMOR_STAND_PLACE" => 38,
	    "SOUND_ADD_CHEST" => 39,
	    "SOUND_THROW" => 40,
	    "SOUND_ATTACK" => 41,
	    "SOUND_ATTACK_NODAMAGE" => 42,
	    "SOUND_ATTACK_STRONG" => 43,
	    "SOUND_WARN" => 44,
	    "SOUND_SHEAR" => 45,
	    "SOUND_MILK" => 46,
	    "SOUND_THUNDER" => 47,
	    "SOUND_EXPLODE" => 48,
	    "SOUND_FIRE" => 49,
	    "SOUND_IGNITE" => 50,
	    "SOUND_FUSE" => 51,
	    "SOUND_STARE" => 52,
	    "SOUND_SPAWN" => 53,
	    "SOUND_SHOOT" => 54,
	    "SOUND_BREAK_BLOCK" => 55,
	    "SOUND_LAUNCH" => 56,
	    "SOUND_BLAST" => 57,
	    "SOUND_LARGE_BLAST" => 58,
	    "SOUND_TWINKLE" => 59,
	    "SOUND_REMEDY" => 60,
	    "SOUND_UNFECT" => 61,
	    "SOUND_LEVELUP" => 62,
	    "SOUND_BOW_HIT" => 63,
	    "SOUND_BULLET_HIT" => 64,
	    "SOUND_EXTINGUISH_FIRE" => 65,
	    "SOUND_ITEM_FIZZ" => 66,
	    "SOUND_CHEST_OPEN" => 67,
	    "SOUND_CHEST_CLOSED" => 68,
	    "SOUND_SHULKERBOX_OPEN" => 69,
	    "SOUND_SHULKERBOX_CLOSED" => 70,
	    "SOUND_ENDERCHEST_OPEN" => 71,
	    "SOUND_ENDERCHEST_CLOSED" => 72,
	    "SOUND_POWER_ON" => 73,
	    "SOUND_POWER_OFF" => 74,
	    "SOUND_ATTACH" => 75,
	    "SOUND_DETACH" => 76,
	    "SOUND_DENY" => 77,
	    "SOUND_TRIPOD" => 78,
	    "SOUND_POP" => 79,
	    "SOUND_DROP_SLOT" => 80,
	    "SOUND_NOTE" => 81,
	    "SOUND_THORNS" => 82,
	    "SOUND_PISTON_IN" => 83,
	    "SOUND_PISTON_OUT" => 84,
	    "SOUND_PORTAL" => 85,
	    "SOUND_WATER" => 86,
	    "SOUND_LAVA_POP" => 87,
	    "SOUND_LAVA" => 88,
	    "SOUND_BURP" => 89,
	    "SOUND_BUCKET_FILL_WATER" => 90,
	    "SOUND_BUCKET_FILL_LAVA" => 91,
	    "SOUND_BUCKET_EMPTY_WATER" => 92,
	    "SOUND_BUCKET_EMPTY_LAVA" => 93,
	    "SOUND_ARMOR_EQUIP_CHAIN" => 94,
	    "SOUND_ARMOR_EQUIP_DIAMOND" => 95,
	    "SOUND_ARMOR_EQUIP_GENERIC" => 96,
	    "SOUND_ARMOR_EQUIP_GOLD" => 97,
	    "SOUND_ARMOR_EQUIP_IRON" => 98,
	    "SOUND_ARMOR_EQUIP_LEATHER" => 99,
	    "SOUND_ARMOR_EQUIP_ELYTRA" => 100,
	    "SOUND_RECORD_13" => 101,
	    "SOUND_RECORD_CAT" => 102,
	    "SOUND_RECORD_BLOCKS" => 103,
	    "SOUND_RECORD_CHIRP" => 104,
	    "SOUND_RECORD_FAR" => 105,
	    "SOUND_RECORD_MALL" => 106,
	    "SOUND_RECORD_MELLOHI" => 107,
	    "SOUND_RECORD_STAL" => 108,
	    "SOUND_RECORD_STRAD" => 109,
	    "SOUND_RECORD_WARD" => 110,
	    "SOUND_RECORD_11" => 111,
	    "SOUND_RECORD_WAIT" => 112,
	    "SOUND_STOP_RECORD" => 113,
	    "SOUND_FLOP" => 114,
	    "SOUND_ELDERGUARDIAN_CURSE" => 115,
	    "SOUND_MOB_WARNING" => 116,
	    "SOUND_MOB_WARNING_BABY" => 117,
	    "SOUND_TELEPORT" => 118,
	    "SOUND_SHULKER_OPEN" => 119,
	    "SOUND_SHULKER_CLOSE" => 120,
	    "SOUND_HAGGLE" => 121,
	    "SOUND_HAGGLE_YES" => 122,
	    "SOUND_HAGGLE_NO" => 123,
	    "SOUND_HAGGLE_IDLE" => 124,
	    "SOUND_CHORUSGROW" => 125,
	    "SOUND_CHORUSDEATH" => 126,
	    "SOUND_GLASS" => 127,
	    "SOUND_POTION_BREWED" => 128,
	    "SOUND_CAST_SPELL" => 129,
	    "SOUND_PREPARE_ATTACK" => 130,
	    "SOUND_PREPARE_SUMMON" => 131,
	    "SOUND_PREPARE_WOLOLO" => 132,
	    "SOUND_FANG" => 133,
	    "SOUND_CHARGE" => 134,
	    "SOUND_CAMERA_TAKE_PICTURE" => 135,
	    "SOUND_LEASHKNOT_PLACE" => 136,
	    "SOUND_LEASHKNOT_BREAK" => 137,
	    "SOUND_GROWL" => 138,
	    "SOUND_WHINE" => 139,
	    "SOUND_PANT" => 140,
	    "SOUND_PURR" => 141,
	    "SOUND_PURREOW" => 142,
	    "SOUND_DEATH_MIN_VOLUME" => 143,
	    "SOUND_DEATH_MID_VOLUME" => 144,
	    "SOUND_IMITATE_BLAZE" => 145,
	    "SOUND_IMITATE_CAVE_SPIDER" => 146,
	    "SOUND_IMITATE_CREEPER" => 147,
	    "SOUND_IMITATE_ELDER_GUARDIAN" => 148,
	    "SOUND_IMITATE_ENDER_DRAGON" => 149,
	    "SOUND_IMITATE_ENDERMAN" => 150,
	    "SOUND_IMITATE_EVOCATION_ILLAGER" => 152,
	    "SOUND_IMITATE_GHAST" => 153,
	    "SOUND_IMITATE_HUSK" => 154,
	    "SOUND_IMITATE_ILLUSION_ILLAGER" => 155,
	    "SOUND_IMITATE_MAGMA_CUBE" => 156,
	    "SOUND_IMITATE_POLAR_BEAR" => 157,
	    "SOUND_IMITATE_SHULKER" => 158,
	    "SOUND_IMITATE_SILVERFISH" => 159,
	    "SOUND_IMITATE_SKELETON" => 160,
	    "SOUND_IMITATE_SLIME" => 161,
	    "SOUND_IMITATE_SPIDER" => 162,
	    "SOUND_IMITATE_STRAY" => 163,
	    "SOUND_IMITATE_VEX" => 164,
	    "SOUND_IMITATE_VINDICATION_ILLAGER" => 165,
	    "SOUND_IMITATE_WITCH" => 166,
	    "SOUND_IMITATE_WITHER" => 167,
	    "SOUND_IMITATE_WITHER_SKELETON" => 168,
	    "SOUND_IMITATE_WOLF" => 169,
	    "SOUND_IMITATE_ZOMBIE" => 170,
	    "SOUND_IMITATE_ZOMBIE_PIGMAN" => 171,
	    "SOUND_IMITATE_ZOMBIE_VILLAGER" => 172,
	    "SOUND_BLOCK_END_PORTAL_FRAME_FILL" => 173,
	    "SOUND_BLOCK_END_PORTAL_SPAWN" => 174,
	    "SOUND_RANDOM_ANVIL_USE" => 175,
	    "SOUND_BOTTLE_DRAGONBREATH" => 176,
	    "SOUND_PORTAL_TRAVEL" => 177,
	    "SOUND_ITEM_TRIDENT_HIT" => 178,
	    "SOUND_ITEM_TRIDENT_RETURN" => 179,
	    "SOUND_ITEM_TRIDENT_RIPTIDE_1" => 180,
	    "SOUND_ITEM_TRIDENT_RIPTIDE_2" => 181,
	    "SOUND_ITEM_TRIDENT_RIPTIDE_3" => 182,
	    "SOUND_ITEM_TRIDENT_THROW" => 183,
	    "SOUND_ITEM_TRIDENT_THUNDER" => 184,
	    "SOUND_ITEM_TRIDENT_HIT_GROUND" => 185,
	    "SOUND_DEFAULT" => 186,
	    "SOUND_BLOCK_FLETCHING_TABLE_USE" => 187,
	    "SOUND_ELEMCONSTRUCT_OPEN" => 188,
	    "SOUND_ICEBOMB_HIT" => 189,
	    "SOUND_BALLOONPOP" => 190,
	    "SOUND_LT_REACTION_ICEBOMB" => 191,
	    "SOUND_LT_REACTION_BLEACH" => 192,
	    "SOUND_LT_REACTION_EPASTE" => 193,
	    "SOUND_LT_REACTION_EPASTE2" => 194,
	    "SOUND_LT_REACTION_FERTILIZER" => 199,
	    "SOUND_LT_REACTION_FIREBALL" => 200,
	    "SOUND_LT_REACTION_MGSALT" => 201,
	    "SOUND_LT_REACTION_MISCFIRE" => 202,
	    "SOUND_LT_REACTION_FIRE" => 203,
	    "SOUND_LT_REACTION_MISCEXPLOSION" => 204,
	    "SOUND_LT_REACTION_MISCMYSTICAL" => 205,
	    "SOUND_LT_REACTION_MISCMYSTICAL2" => 206,
	    "SOUND_LT_REACTION_PRODUCT" => 207,
	    "SOUND_SPARKLER_USE" => 208,
	    "SOUND_GLOWSTICK_USE" => 209,
	    "SOUND_SPARKLER_ACTIVE" => 210,
	    "SOUND_CONVERT_TO_DROWNED" => 211,
	    "SOUND_BUCKET_FILL_FISH" => 212,
	    "SOUND_BUCKET_EMPTY_FISH" => 213,
	    "SOUND_BUBBLE_UP" => 214,
	    "SOUND_BUBBLE_DOWN" => 215,
	    "SOUND_BUBBLE_POP" => 216,
	    "SOUND_BUBBLE_UPINSIDE" => 217,
	    "SOUND_BUBBLE_DOWNINSIDE" => 218,
	    "SOUND_HURT_BABY" => 219,
	    "SOUND_DEATH_BABY" => 220,
	    "SOUND_STEP_BABY" => 221,
	    "SOUND_BORN" => 223,
	    "SOUND_BLOCK_TURTLE_EGG_BREAK" => 224,
	    "SOUND_BLOCK_TURTLE_EGG_CRACK" => 225,
	    "SOUND_BLOCK_TURTLE_EGG_HATCH" => 226,
	    "SOUND_LAY_EGG" => 227,
	    "SOUND_BLOCK_TURTLE_EGG_ATTACK" => 228,
	    "SOUND_BEACON_ACTIVATE" => 229,
	    "SOUND_BEACON_AMBIENT" => 230,
	    "SOUND_BEACON_DEACTIVATE" => 231,
	    "SOUND_BEACON_POWER" => 232,
	    "SOUND_CONDUIT_ACTIVATE" => 233,
	    "SOUND_CONDUIT_AMBIENT" => 234,
	    "SOUND_CONDUIT_ATTACK" => 235,
	    "SOUND_CONDUIT_DEACTIVATE" => 236,
	    "SOUND_CONDUIT_SHORT" => 237,
	    "SOUND_SWOOP" => 238,
	    "SOUND_BLOCK_BAMBOO_SAPLING_PLACE" => 239,
	    "SOUND_PRESNEEZE" => 240,
	    "SOUND_SNEEZE" => 241,
	    "SOUND_AMBIENT_TAME" => 242,
	    "SOUND_SCARED" => 243,
	    "SOUND_BLOCK_SCAFFOLDING_CLIMB" => 244,
	    "SOUND_CROSSBOW_LOADING_START" => 245,
	    "SOUND_CROSSBOW_LOADING_MIDDLE" => 246,
	    "SOUND_CROSSBOW_LOADING_END" => 247,
	    "SOUND_CROSSBOW_SHOOT" => 248,
	    "SOUND_CROSSBOW_QUICK_CHARGE_START" => 249,
	    "SOUND_CROSSBOW_QUICK_CHARGE_MIDDLE" => 250,
	    "SOUND_CROSSBOW_QUICK_CHARGE_END" => 251,
	    "SOUND_AMBIENT_AGGRESSIVE" => 252,
	    "SOUND_AMBIENT_WORRIED" => 253,
	    "SOUND_CANT_BREED" => 254,
	    "SOUND_ITEM_SHIELD_BLOCK" => 255,
	    "SOUND_ITEM_BOOK_PUT" => 256,
	    "SOUND_BLOCK_GRINDSTONE_USE" => 257,
	    "SOUND_BLOCK_BELL_HIT" => 258,
	    "SOUND_BLOCK_CAMPFIRE_CRACKLE" => 259,
	    "SOUND_ROAR" => 260,
	    "SOUND_STUN" => 261,
	    "SOUND_BLOCK_SWEET_BERRY_BUSH_HURT" => 262,
	    "SOUND_BLOCK_SWEET_BERRY_BUSH_PICK" => 263,
	    "SOUND_BLOCK_CARTOGRAPHY_TABLE_USE" => 264,
	    "SOUND_BLOCK_STONECUTTER_USE" => 265,
	    "SOUND_BLOCK_COMPOSTER_EMPTY" => 266,
	    "SOUND_BLOCK_COMPOSTER_FILL" => 267,
	    "SOUND_BLOCK_COMPOSTER_FILL_SUCCESS" => 268,
	    "SOUND_BLOCK_COMPOSTER_READY" => 269,
	    "SOUND_BLOCK_BARREL_OPEN" => 270,
	    "SOUND_BLOCK_BARREL_CLOSE" => 271,
	    "SOUND_RAID_HORN" => 272,
	    "SOUND_BLOCK_LOOM_USE" => 273,
	    "SOUND_AMBIENT_IN_RAID" => 274,
	    "SOUND_UI_CARTOGRAPHY_TABLE_TAKE_RESULT" => 275,
	    "SOUND_UI_STONECUTTER_TAKE_RESULT" => 276,
	    "SOUND_UI_LOOM_TAKE_RESULT" => 277,
	    "SOUND_BLOCK_SMOKER_SMOKE" => 278,
	    "SOUND_BLOCK_BLASTFURNACE_FIRE_CRACKLE" => 279,
	    "SOUND_BLOCK_SMITHING_TABLE_USE" => 280,
	    "SOUND_SCREECH" => 281,
	    "SOUND_SLEEP" => 282,
	    "SOUND_BLOCK_FURNACE_LIT" => 283,
	    "SOUND_CONVERT_MOOSHROOM" => 284,
	    "SOUND_MILK_SUSPICIOUSLY" => 285,
	    "SOUND_CELEBRATE" => 286,
	    "SOUND_JUMP_PREVENT" => 287,
	    "SOUND_AMBIENT_POLLINATE" => 288,
	    "SOUND_BLOCK_BEEHIVE_DRIP" => 289,
	    "SOUND_BLOCK_BEEHIVE_ENTER" => 290,
	    "SOUND_BLOCK_BEEHIVE_EXIT" => 291,
	    "SOUND_BLOCK_BEEHIVE_WORK" => 292,
	    "SOUND_BLOCK_BEEHIVE_SHEAR" => 293,
	    "SOUND_DRINK_HONEY" => 294,
	    "SOUND_AMBIENT_CAVE" => 295,
	    "SOUND_RETREAT" => 296,
	    "SOUND_CONVERTED_TO_ZOMBIFIED" => 297,
	    "SOUND_ADMIRE" => 298,
	    "SOUND_STEP_LAVA" => 299,
	    "SOUND_TEMPT" => 300,
	    "SOUND_PANIC" => 301,
	    "SOUND_ANGRY" => 302,
	    "SOUND_AMBIENT_WARPED_FOREST_MOOD" => 303,
	    "SOUND_AMBIENT_SOULSAND_VALLEY_MOOD" => 304,
	    "SOUND_AMBIENT_NETHER_WASTES_MOOD" => 305,
	    "SOUND_RESPAWN_ANCHOR_BASALT_DELTAS_MOOD" => 306,
	    "SOUND_AMBIENT_CRIMSON_FOREST_MOOD" => 307,
	    "SOUND_RESPAWN_ANCHOR_CHARGE" => 308,
	    "SOUND_RESPAWN_ANCHOR_DEPLETE" => 309,
	    "SOUND_RESPAWN_ANCHOR_SET_SPAWN" => 310,
	    "SOUND_RESPAWN_ANCHOR_AMBIENT" => 311,
	    "SOUND_PARTICLE_SOUL_ESCAPE_QUIET" => 312,
	    "SOUND_PARTICLE_SOUL_ESCAPE_LOUD" => 313,
	    "SOUND_RECORD_PIGSTEP" => 314,
	    "SOUND_LODESTONE_COMPASS_LINK_COMPASS_TO_LODESTONE" => 315,
	    "SOUND_SMITHING_TABLE_USE" => 316,
	    "SOUND_ARMOR_EQUIP_NETHERITE" => 317,
	    "SOUND_AMBIENT_WARPED_FOREST_LOOP" => 318,
	    "SOUND_AMBIENT_SOULSAND_VALLEY_LOOP" => 319,
	    "SOUND_AMBIENT_NETHER_WASTES_LOOP" => 320,
	    "SOUND_AMBIENT_BASALT_DELTAS_LOOP" => 321,
	    "SOUND_AMBIENT_CRIMSON_FOREST_LOOP" => 322,
	    "SOUND_AMBIENT_WARPED_FOREST_ADDITIONS" => 323,
	    "SOUND_AMBIENT_SOULSAND_VALLEY_ADDITIONS" => 324,
	    "SOUND_AMBIENT_NETHER_WASTES_ADDITIONS" => 325,
	    "SOUND_AMBIENT_BASALT_DELTAS_ADDITIONS" => 326,
	    "SOUND_AMBIENT_CRIMSON_FOREST_ADDITIONS" => 327,
	    "SOUND_BUCKET_FILL_POWDER_SNOW" => 328,
	    "SOUND_BUCKET_EMPTY_POWDER_SNOW" => 329,
	    "SOUND_UNDEFINED" => 330
	];
	
	public function playSound(Player $player, string $datad){
		$sounds = explode(":", $datad);
		foreach($sounds as $sound){
			$data = explode("->", $sound);
			if($data[0] == "LevelEventPacket"){
				$player->getWorld()->broadcastLevelEvent($player, $this->levelEventPacket[$data[1]], mt_rand());
			}
			if($data[0] == "LevelSoundEventPacket"){
				$player->getWorld()->broadcastLevelSoundEvent($player->asVector3(), $this->levelSoundEventPacket[$data[1]]);
			}
		}			
	}
	
	public function addSound(string $data, string $sound) :string{
		$newData = [];
		if($data == ""){
			$sounds = explode(":", self::KEY_SOUND);
		}else{
			$sounds = explode(":", $data);
		}
		foreach($sounds as $soundD){
			$newData[] = $soundD;
		}
		$newData[] = $sound;
		return implode(":", $newData);
	}
	
	public function insertSound(string $sound) :string{
		$newData = [];
		$sounds = explode(":", self::KEY_SOUND);
		foreach($sounds as $soundD){
			$newData[] = $soundD;
		}
		$newData[] = $sound;
		return implode(":", $newData);
	}
}