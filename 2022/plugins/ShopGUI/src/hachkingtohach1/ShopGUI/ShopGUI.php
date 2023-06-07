<?php

namespace hachkingtohach1\ShopGUI;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\inventory\Inventory;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\EndermanTeleportSound;
use skymin\InventoryLib\{InvLibManager, LibInvType, InvLibAction, LibInventory};

class ShopGUI extends PluginBase implements Listener {
	
	CONST DONT_SELL_ANYTHING = "§cBạn chưa bán thứ gì trong ngày!";
	CONST DONT_ENOUGH_COINS = "§cBạn không đủ coins như yêu cầu!";
	CONST DONT_ENOUGH_ITEM = "§cBạn không đủ item như yêu cầu!";
	
	private $statusShop = true;
	private $items = [];	
	private $dataPlayers = [];
	private $historyPlayers = [];
	public $itemCanSell = [
// id->name
"457->" => 20,
"1->" => 1,
"2->" => 1,
"3->" => 1,
"4->" => 1,
"5->" => 2,
"6->" => 5,
"12->" => 1,
"13->" => 1,
"14->" => 20,
"15->" => 25,
"16->" => 5,
"17->" => 8,
"18->" => 1,
"19->" => 10,
"20->" => 7,
"21->" => 7,
"22->" => 63,
"23->" => 29,
"24->" => 4,
"25->" => 17,
"27->" => 156,
"28->" => 150,
"29->" => 50,
"30->" => 2,
"32->" => 2,
"33->" => 42,
"35->" => 2,
"37->" => 3,
"38->" => 3,
"39->" => 6,
"40->" => 6,
"41->" => 225,
"42->" => 270,
"44->" => 1,
"45->" => 10,
"46->" => 55,
"47->" => 75,
"48->" => 10,
"49->" => 100,
"50->" => 9,
"53->" => 12,
"54->" => 16,
"56->" => 50,
"57->" => 450,
"58->" => 8,
"60->" => 2,
"61->" => 8,
"63->" => 16,
"65->" => 9,
"66->" => 11,
"67->" => 6,
"69->" => 5,
"70->" => 3,
"72->" => 4,
"75->" => 6,
"78->" => 1,
"79->" => 1,
"80->" => 22,
"81->" => 15,
"82->" => 8,
"84->" => 66,
"85->" => 24,
"86->" => 12,
"87->" => 5,
"88->" => 10,
"89->" => 16,
"91->" => 21,
"96->" => 6,
"97->" => 15,
"98->" => 6,
"101->" => 7.5,
"102->" => 1,
"103->" => 10,
"106->" => 1,
"107->" => 20,
"108->" => 10,
"109->" => 6,
"110->" => 8,
"111->" => 2,
"112->" => 10,
"113->" => 10,
"114->" => 10,
"115->" => 15,
"116->" => 321,
"117->" => 13,
"118->" => 140,
"121->" => 5,
"122->" => 5000,
"123->" => 26,
"126->" => 1,
"127->" => 5,
"128->" => 6,
"129->" => 40,
"130->" => 810,
"131->" => 13,
"133->" => 360,
"134->" => 12,
"135->" => 12,
"136->" => 12,
"139->" => 1,
"140->" => 7,
"141->" => 17,
"142->" => 15,
"143->" => 4,
"155->" => 9,
"159->" => 3,
"168->" => 5,
"169->" => 7,
"236->" => 3,
"256->" => 28,
"257->" => 5,
"258->" => 5,
"259->" => 22,
"260->" => 2,
"262->" => 2,
"263->" => 5,
"264->" => 50,
"265->" => 20,
"266->" => 20,
"280->" => 1,
"281->" => 6,
"282->" => 12,
"287->" => 2,
"288->" => 2,
"295->" => 1,
"296->" => 10,
"297->" => 6,
"318->" => 2,
"319->" => 2,
"320->" => 5,
"321->" => 42,
"323->" => 4,
"324->" => 12,
"325->" => 90,
"326->" => 100,
"327->" => 150,
"328->" => 150,
"329->" => 10,
"330->" => 180,
"331->" => 2,
"332->" => 2,
"333->" => 10,
"334->" => 3,
"335->" => 115,
"336->" => 3,
"337->" => 2,
"338->" => 12,
"339->" => 6,
"340->" => 21,
"341->" => 7,
"342->" => 116,
"343->" => 158,
"344->" => 10,
"345->" => 122,
"346->" => 17,
"347->" => 82,
"348->" => 4,
"349->" => 2,
"350->" => 5,
"351->" => 2,
"352->" => 6,
"353->" => 2,
"354->" => 366,
"355->" => 36,
"356->" => 20,
"357->" => 1,
"359->" => 60,
"360->" => 3,
"361->" => 3,
"362->" => 3,
"363->" => 2,
"364->" => 5,
"365->" => 2,
"366->" => 5,
"367->" => 5,
"368->" => 10,
"369->" => 10,
"370->" => 10,
"371->" => 2,
"372->" => 5,
"374->" => 8,
"375->" => 5,
"376->" => 10,
"377->" => 5,
"378->" => 12,
"379->" => 13,
"380->" => 210,
"381->" => 10,
"382->" => 5,
"385->" => 4,
"386->" => 26,
"388->" => 40,
"389->" => 35,
"390->" => 7,
"391->" => 5,
"392->" => 2,
"393->" => 5,
"394->" => 8,
"395->" => 170,
"396->" => 25,
"398->" => 22,
"399->" => 500,
"400->" => 24,
"406->" => 2,
"410->" => 232,
"444->" => 17500,
"445->" => 1000,
"450->" => 25000,
"349->" => 10,
"409->" => 10,
"460->" => 10,
"461->" => 10,
"466->" => 10
    ];
	private static $instance = null;
	
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	public static function getInstance(): ShopGUI{
        return self::$instance;
    }

    public function onEnable() :void{
		InvLibManager::register($this);
		$this->items = [
		    "lumber" => [
		        "Oak Log" => [
		            "ITEM" => $this->getDataItem(17, 0, 10)->setCustomName("Oak Log"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Spruce Log" => [
		            "ITEM" => $this->getDataItem(17, 1, 10)->setCustomName("Spruce Log"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Birch Log" => [
		            "ITEM" => $this->getDataItem(17, 2, 10)->setCustomName("Birch Log"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Jungle Log" => [
		            "ITEM" => $this->getDataItem(17, 3, 10)->setCustomName("Jungle Log"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Acacia Log" => [
		            "ITEM" => $this->getDataItem(17, 4, 10)->setCustomName("Acacia Log"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Dark Oak Log" => [
		            "ITEM" => $this->getDataItem(17, 5, 10)->setCustomName("Dark Oak Log"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
			],
		    "cooker" => [
		        "Steak" => [
		            "ITEM" => $this->getDataItem(364, 0, 5)->setCustomName("Steak"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
			    "Cooked Chicken" => [
		            "ITEM" => $this->getDataItem(366, 0, 5)->setCustomName("Cooked Chicken"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45
	            ],
				"Cooked Porkchop" => [
		            "ITEM" => $this->getDataItem(320, 0, 5)->setCustomName("Cooked Porkchop"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 35
	            ],
				"Cooked Rabbit" => [
		            "ITEM" => $this->getDataItem(412, 0, 5)->setCustomName("Cooked Rabbit"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 35
	            ],
				"Cooked Salmon" => [
		            "ITEM" => $this->getDataItem(350, 0, 5)->setCustomName("Cooked Salmon"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 25
	            ],
				"Cookie" => [
		            "ITEM" => $this->getDataItem(357, 0, 5)->setCustomName("Cookie"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 20
	            ],
				/*TextFormat::YELLOW.TextFormat::BLUE.TextFormat::RED.TextFormat::GOLD."Quả Thiên Nhiên" =>[
				    "ITEM" => $this->getDataItem(466, 0, 1)->setCustomName(TextFormat::YELLOW.TextFormat::BLUE.TextFormat::RED.TextFormat::GOLD."Quả Thiên Nhiên")->setLore([TextFormat::AQUA."Quả này sẽ giúp bạn không đói trong 5 tiếng,", TextFormat::AQUA."chỉ có thể ăn tại thế giới survival"]),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100000
				],*/
	        ],
			"angler" => [
		        "Fishing Rod" => [
		            "ITEM" => $this->getDataItem(346, 0, 1)->setCustomName("Fishing Rod"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 10			
	            ],
				"Pufferfish" => [
		            "ITEM" => $this->getDataItem(349, 0, 5)->setCustomName("Pufferfish"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 25
	            ],
				"Pufferfish" => [
		            "ITEM" => $this->getDataItem(349, 0, 5)->setCustomName("Pufferfish"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 25
	            ]
			],
			"weaponsmith" => [
		        "Diamond Sword" => [
		            "ITEM" => $this->getDataItem(276, 0, 1)->setCustomName("Diamond Sword"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Iron Sword" => [
		            "ITEM" => $this->getDataItem(267, 0, 1)->setCustomName("Iron Sword"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 40			
	            ],
				"Golden Sword" => [
		            "ITEM" => $this->getDataItem(283, 0, 1)->setCustomName("Golden Sword"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],
				"Stone Sword" => [
		            "ITEM" => $this->getDataItem(272, 0, 1)->setCustomName("Stone Sword"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 30			
	            ],
				"Wooden Sword" => [
		            "ITEM" => $this->getDataItem(268, 0, 1)->setCustomName("Wooden Sword"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 20			
	            ],
				"Bow" => [
		            "ITEM" => $this->getDataItem(261, 0, 1)->setCustomName("Bow"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],
				"Arrow" => [
		            "ITEM" => $this->getDataItem(262, 0, 15)->setCustomName("Arrow"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 10			
	            ]
			],
			"armorsmith" => [
		        "Diamond Helmet" => [
		            "ITEM" => $this->getDataItem(310, 0, 1)->setCustomName("Diamond Helmet"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Diamond Chestplate" => [
		            "ITEM" => $this->getDataItem(311, 0, 1)->setCustomName("Diamond Chestplate"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Diamond Leggings" => [
		            "ITEM" => $this->getDataItem(312, 0, 1)->setCustomName("Diamond Leggings"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Diamond Boots" => [
		            "ITEM" => $this->getDataItem(313, 0, 1)->setCustomName("Diamond Boots"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],		
				"Iron Helmet" => [
		            "ITEM" => $this->getDataItem(306, 0, 1)->setCustomName("Iron Helmet"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],
				"Iron Chestplate" => [
		            "ITEM" => $this->getDataItem(307, 0, 1)->setCustomName("Iron Chestplate"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],
				"Iron Leggings" => [
		            "ITEM" => $this->getDataItem(308, 0, 1)->setCustomName("Iron Leggings"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],
				"Iron Boots" => [
		            "ITEM" => $this->getDataItem(309, 0, 1)->setCustomName("Iron Boots"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],			
				"Golden Helmet" => [
		            "ITEM" => $this->getDataItem(314, 0, 1)->setCustomName("Golden Helmet"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],
				"Golden Chestplate" => [
		            "ITEM" => $this->getDataItem(315, 0, 1)->setCustomName("Golden Chestplate"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],
				"Golden Leggings" => [
		            "ITEM" => $this->getDataItem(316, 0, 1)->setCustomName("Golden Leggings"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],
				"Golden Boots" => [
		            "ITEM" => $this->getDataItem(317, 0, 1)->setCustomName("Golden Boots"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 45			
	            ],		
				"Leather Cap" => [
		            "ITEM" => $this->getDataItem(298, 0, 1)->setCustomName("Leather Cap"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 35			
	            ],
				"Leather Tunic" => [
		            "ITEM" => $this->getDataItem(299, 0, 1)->setCustomName("Leather Tunic"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 35			
	            ],
				"Leather Pants" => [
		            "ITEM" => $this->getDataItem(300, 0, 1)->setCustomName("Leather Pants"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 35			
	            ],
				"Leather Boots" => [
		            "ITEM" => $this->getDataItem(301, 0, 1)->setCustomName("Leather Boots"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 35			
	            ],				
				"Chain Helmet" => [
		            "ITEM" => $this->getDataItem(302, 0, 1)->setCustomName("Chain Helmet"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 40			
	            ],
				"Chain Chestplate" => [
		            "ITEM" => $this->getDataItem(303, 0, 1)->setCustomName("Chain Chestplate"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 40			
	            ],
				"Chain Leggings" => [
		            "ITEM" => $this->getDataItem(304, 0, 1)->setCustomName("Chain Leggings"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 40			
	            ],
				"Chain Boots" => [
		            "ITEM" => $this->getDataItem(305, 0, 1)->setCustomName("Chain Boots"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 40			
	            ]
			],
			"miner" => [
			    TextFormat::AQUA."Advanced Crafting Table" => [
		            "ITEM" => $this->getDataItem(247, 0, 1)->setCustomName(TextFormat::AQUA."Advanced Crafting Table"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 200			
	            ],
		        "Diamond Pickaxe" => [
		            "ITEM" => $this->getDataItem(278, 0, 1)->setCustomName("Diamond Pickaxe"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Iron Pickaxe" => [
		            "ITEM" => $this->getDataItem(257, 0, 1)->setCustomName("Iron Pickaxe"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 40			
	            ],
				"Golden Pickaxe" => [
		            "ITEM" => $this->getDataItem(285, 0, 1)->setCustomName("Golden Pickaxe"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 30			
	            ],
				"Stone Pickaxe" => [
		            "ITEM" => $this->getDataItem(274, 0, 1)->setCustomName("Stone Pickaxe"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 20			
	            ],
				"Wooden Pickaxe" => [
		            "ITEM" => $this->getDataItem(270, 0, 1)->setCustomName("Wooden Pickaxe"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 10			
	            ],
				"Gold Ingot" => [
		            "ITEM" => $this->getDataItem(266, 0, 2)->setCustomName("Gold Ingot"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Coal" => [
		            "ITEM" => $this->getDataItem(263, 0, 2)->setCustomName("Coal"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 20		
	            ],
				"Lava Bucket" => [
		            "ITEM" => $this->getDataItem(325, 8, 1)->setCustomName("Lava Bucket"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 500		
	            ],
				"Water Bucket" => [
		            "ITEM" => $this->getDataItem(325, 10, 1)->setCustomName("Water Bucket"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 500		
	            ]
			],
			"farmer" => [
		        "Seeds" => [
		            "ITEM" => $this->getDataItem(295, 0, 5)->setCustomName("Seeds"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Carrot" => [
		            "ITEM" => $this->getDataItem(391, 0, 5)->setCustomName("Carrot"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Potato" => [
		            "ITEM" => $this->getDataItem(392, 0, 5)->setCustomName("Potato"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50		
	            ],
				"Pumpkin Seeds" => [
		            "ITEM" => $this->getDataItem(361, 0, 5)->setCustomName("Pumpkin Seeds"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50		
	            ],
				"Melon Seeds" => [
		            "ITEM" => $this->getDataItem(362, 0, 5)->setCustomName("Melon Seeds"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50		
	            ],
				"Cocoa Beans" => [
		            "ITEM" => $this->getDataItem(351, 3, 5)->setCustomName("Cocoa Beans"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50	
	            ],
				"Sugar Canes" => [
		            "ITEM" => $this->getDataItem(338, 0, 5)->setCustomName("Sugar Canes"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50	
	            ],
				"Nether Wart" => [
		            "ITEM" => $this->getDataItem(372, 0, 5)->setCustomName("Nether Wart"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50	
	            ]			
			],
			"builder" => [
		        "White Wool" => [
		            "ITEM" => $this->getDataItem(35, 0, 10)->setCustomName("White Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Orange Wool" => [
		            "ITEM" => $this->getDataItem(35, 1, 10)->setCustomName("Orange Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Magenta Wool" => [
		            "ITEM" => $this->getDataItem(35, 2, 10)->setCustomName("Magenta Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Light Blue Wool" => [
		            "ITEM" => $this->getDataItem(35, 3, 10)->setCustomName("Light Blue Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Yellow Wool" => [
		            "ITEM" => $this->getDataItem(35, 4, 10)->setCustomName("Yellow Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Lime Wool" => [
		            "ITEM" => $this->getDataItem(35, 5, 10)->setCustomName("Lime Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Pink Wool" => [
		            "ITEM" => $this->getDataItem(35, 6, 10)->setCustomName("Pink Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Gray Wool" => [
		            "ITEM" => $this->getDataItem(35, 7, 10)->setCustomName("Gray Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Light Gray Wool" => [
		            "ITEM" => $this->getDataItem(35, 8, 10)->setCustomName("Light Gray Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Cyan Wool" => [
		            "ITEM" => $this->getDataItem(35, 9, 10)->setCustomName("Cyan Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Purple Wool" => [
		            "ITEM" => $this->getDataItem(35, 10, 10)->setCustomName("Purple Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Blue Wool" => [
		            "ITEM" => $this->getDataItem(35, 11, 10)->setCustomName("Blue Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Brown Wool" => [
		            "ITEM" => $this->getDataItem(35, 12, 10)->setCustomName("Brown Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Green Wool" => [
		            "ITEM" => $this->getDataItem(35, 13, 10)->setCustomName("Green Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Red Wool" => [
		            "ITEM" => $this->getDataItem(35, 14, 10)->setCustomName("Red Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Black Wool" => [
		            "ITEM" => $this->getDataItem(35, 15, 10)->setCustomName("Black Wool"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Cobblestone" => [
		            "ITEM" => $this->getDataItem(4, 0, 10)->setCustomName("Cobblestone"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Bricks" => [
		            "ITEM" => $this->getDataItem(45, 0, 10)->setCustomName("Bricks"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Glowstone" => [
		            "ITEM" => $this->getDataItem(89, 0, 10)->setCustomName("Glowstone"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"End Stone" => [
		            "ITEM" => $this->getDataItem(121, 0, 10)->setCustomName("End Stone"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Netherrack" => [
		            "ITEM" => $this->getDataItem(87, 0, 10)->setCustomName("Netherrack"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Bookshelf" => [
		            "ITEM" => $this->getDataItem(47, 0, 1)->setCustomName("Bookshelf"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Clay Block" => [
		            "ITEM" => $this->getDataItem(82, 0, 10)->setCustomName("Clay Block"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Infested Chiseled Stone Bricks" => [
		            "ITEM" => $this->getDataItem(97, 5, 10)->setCustomName("Infested Chiseled Stone Bricks"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Dirt" => [
		            "ITEM" => $this->getDataItem(3, 0, 10)->setCustomName("Dirt"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ]
			],
			"redstone" => [
		        "Redstone Comparator" => [
		            "ITEM" => $this->getDataItem(404, 0, 3)->setCustomName("Redstone Comparator"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Redstone Repeater" => [
		            "ITEM" => $this->getDataItem(356, 0, 3)->setCustomName("Redstone Repeater"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Redstone Lamp" => [
		            "ITEM" => $this->getDataItem(123, 0, 3)->setCustomName("Redstone Lamp"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 60			
	            ],
				"Redstone Block" => [
		            "ITEM" => $this->getDataItem(152, 0, 1)->setCustomName("Redstone Block"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 60			
	            ],
				"Redstone Torch" => [
		            "ITEM" => $this->getDataItem(76, 0, 5)->setCustomName("Redstone Torch"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
				"Redstone Torch" => [
		            "ITEM" => $this->getDataItem(76, 0, 5)->setCustomName("Redstone Torch"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 50			
	            ],
		    ],
			"glass" => [
		        "White Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 0, 10)->setCustomName("White Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Orange Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 1, 10)->setCustomName("Orange Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Magenta Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 2, 10)->setCustomName("Magenta Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Light Blue Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 3, 10)->setCustomName("Light Blue Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Yellow Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 4, 10)->setCustomName("Yellow Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Lime Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 5, 10)->setCustomName("Lime Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Pink Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 6, 10)->setCustomName("Pink Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Gray Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 7, 10)->setCustomName("Gray Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Light Gray Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 8, 10)->setCustomName("Light Gray Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Cyan Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 9, 10)->setCustomName("Cyan Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Purple Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 10, 10)->setCustomName("Purple Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Blue Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 11, 10)->setCustomName("Blue Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Brown Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 12, 10)->setCustomName("Brown Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Green Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 13, 10)->setCustomName("Green Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Red Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 14, 10)->setCustomName("Red Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Black Stained Glass" => [
		            "ITEM" => $this->getDataItem(241, 15, 10)->setCustomName("Black Stained Glass"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],	
				"White Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 0, 10)->setCustomName("White Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Orange Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 1, 10)->setCustomName("Orange Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Magenta Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 2, 10)->setCustomName("Magenta Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Light Blue Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 3, 10)->setCustomName("Light Blue Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Yellow Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 4, 10)->setCustomName("Yellow Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Lime Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 5, 10)->setCustomName("Lime Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Pink Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 6, 10)->setCustomName("Pink Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Gray Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 7, 10)->setCustomName("Gray Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Light Gray Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 8, 10)->setCustomName("Light Gray Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Cyan Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 9, 10)->setCustomName("Cyan Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Purple Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 10, 10)->setCustomName("Purple Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
				"Blue Stained Glass Pane" => [
		            "ITEM" => $this->getDataItem(160, 11, 10)->setCustomName("Blue Stained Glass Pane"),
			        "MIXED_NEEDLE" => false,
			        "COST" => 100			
	            ],
			],
		];
		$this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if($command->getName() == "shopguiz"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED."This is command for in-game!");
				return false;
			}
			if(!isset($args[0])){
				$sender->sendMessage(TextFormat::GREEN."-----------");
				foreach($this->items as $name => $data){
					$sender->sendMessage(TextFormat::GREEN.$name);					
				}
				$sender->sendMessage(TextFormat::GREEN."-----------");
				$sender->sendMessage(TextFormat::GREEN."Usage: /shopguiz <category:string>");
				return false;
			}
			$this->openShop($sender, $args[0]);
			return true;
        }
		if($command->getName() == "adminshop"){
			if($this->statusShop == true){
				$sender->sendMessage(TextFormat::GREEN."> Shop enable!");
				$this->statusShop = false;
			}else{
				$sender->sendMessage(TextFormat::RED."> Shop disable!");
				$this->statusShop = true;
			}
			return true;
        }
		return false;
	}
	
	public function getDataItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		$class = new ItemFactory();
		return $class->get($id, $meta, $count, $tags);
	}
	
	public function openShop(Player $player, string $category){
		
		if(!isset($this->items[$category])){
			$player->sendMessage(TextFormat::RED."Mục này có vẻ không tồn tại hoặc chưa được mở!");
			return;
		}
		
		$this->dataPlayers[$player->getName()] = $this->items[$category];
		
		$menu = InvLibManager::create(LibInvType::DOUBLE_CHEST(), $player->getPosition(), 'Shop'); 	
		
		$null = $this->getDataItem(26, 0, 1);
		$null->setCustomName("-");    
		
		for($a = 0; $a <= 8; $a++){
			for($b = 45; $b <= 53; $b++){
				$menu->setItem($a, $null);
				if($b != 49){
				    $menu->setItem($b, $null);
				}
			}
		}		
		
		$slots = [9, 18, 27, 36, 17, 26, 35, 44];
		foreach($slots as $slot){
			$menu->setItem($slot, $null);
		}
		
	    foreach($this->dataPlayers[$player->getName()] as $dataItems){					    
			$base = $dataItems["ITEM"];
			$item = $this->getDataItem($base->getId(), $base->getMeta(), $base->getCount());
			$item->setCustomName($base->getCustomName());
			$newLore = $base->getLore();
			
			foreach($base->getLore() as $case => $lore){
				$newLore[$case] = $lore;
			}
			
			$newLore[] = TextFormat::BLUE."";			
			$newLore[] = TextFormat::GRAY."Cost";
			if($dataItems["MIXED_NEEDLE"] != false){
			    foreach($dataItems["MIXED_NEEDLE"] as $name => $need){
				    $newLore[] = TextFormat::WHITE.$name.TextFormat::GRAY." x".explode(",", $need)[2];
				}
			}
			
			if($dataItems["COST"] != false){
				$newLore[] = TextFormat::GOLD.$dataItems["COST"]." coins";
			}
			
			$newLore[] = TextFormat::GOLD."";			
			$newLore[] = TextFormat::YELLOW."Click to purchase!";
			
			$item->setLore($newLore);
			$menu->addItem($item);
		}
		$nullN = $this->getDataItem(95, 0, 1);
		$nullN->setCustomName("-"); 
		for($i = 0; $i <= 53; $i++){
			if($menu->getItem($i)->getId() == 0){
			    $menu->setItem($i, $nullN);
			}
		}
		if(isset($this->historyPlayers[$player->getName()])){
			$cost = 0;
			$base = null;
			$itemSold = count($this->historyPlayers[$player->getName()]);			
			if($itemSold == 1){
			    foreach($this->historyPlayers[$player->getName()] as $case => $array){
				    $base = $this->historyPlayers[$player->getName()][$case][0];
				    $cost = $this->historyPlayers[$player->getName()][$case][1];
				}
			}
			if($itemSold > 1){
				$base = $this->historyPlayers[$player->getName()][$itemSold - 1][0];
				$cost = $this->historyPlayers[$player->getName()][$itemSold - 1][1];
			}
			if($itemSold < 1){
				$base = $this->getDataItem(260, 0, 1);
		        $base->setCustomName("-");
			    $base->setLore([self::DONT_SELL_ANYTHING]);	
			}
			$item = $this->getDataItem($base->getId(), $base->getMeta(), $base->getCount());
			$item->setCustomName($base->getCustomName());
			$newLore = $base->getLore();			
			foreach($base->getLore() as $case => $lore){
				$newLore[$case] = $lore;
			}			
			$newLore[] = TextFormat::BLUE."";			
			$newLore[] = TextFormat::GRAY."Cost";
			$newLore[] = TextFormat::RED."";
            $newLore[] = TextFormat::GOLD.$cost." Coins";			
			$item->setLore($newLore);
			$menu->setItem(45, $item);			
		}else{
			$guideA = $this->getDataItem(260, 0, 1);
		    $guideA->setCustomName("-");
			$guideA->setLore([self::DONT_SELL_ANYTHING]);
		    $menu->setItem(45, $guideA);		
		}
		$menu->setItem(49, $this->getDataItem(0, 0, 0));
		
		$sell = $this->getDataItem(341, 0, 1);
		$sell->setCustomName("sell"); 
		$menu->setItem(53, $sell);
		
        $menu->send($player);		
		$menu->setListener(function(InvLibAction $action) use ($menu): void{
			$itemClicked = $action->getSourceItem();
			$player = $action->getPlayer();
			$checkItemNeedSell = $menu->getItem(49)->getId();
			$checkDataItemSell = $checkItemNeedSell."->".$menu->getItem(49)->getCustomName();
			if(isset($this->historyPlayers[$player->getName()])){
				foreach($this->historyPlayers[$player->getName()] as $case => [$item, $cost]){
					if($itemClicked->getId() == $menu->getItem(45)->getId() and $itemClicked->getCustomName() == $item->getCustomName()){
						$api = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
						$money = $api->myMoney($player->getName());
				    	if($money >= $cost){
							$api->reduceMoney($player, $cost);
							$player->getInventory()->addItem($item);							
							$player->getWorld()->addSound($player->getLocation()->asVector3(), new XpCollectSound(), [$player]);
							unset($this->historyPlayers[$player->getName()][$case]);							
							sort($this->historyPlayers[$player->getName()]);
							if(isset($this->historyPlayers[$player->getName()])){								
				                $cost = 0;
			                    $base = null;
			                    $itemSold = count($this->historyPlayers[$player->getName()]);			
			                    if($itemSold == 1){
			                        foreach($this->historyPlayers[$player->getName()] as $case => $array){
				                        $base = $this->historyPlayers[$player->getName()][$case][0];
				                        $cost = $this->historyPlayers[$player->getName()][$case][1];
				                    }
			                    }
			                    if($itemSold > 1){
				                    $base = $this->historyPlayers[$player->getName()][$itemSold - 1][0];
				                    $cost = $this->historyPlayers[$player->getName()][$itemSold - 1][1];
			                    }
			                    if($itemSold < 1){
				                    $base = $this->getDataItem(260, 0, 1);
		                            $base->setCustomName("-");
			                        $base->setLore([self::DONT_SELL_ANYTHING]);	
			                    }
			                    $item = $this->getDataItem($base->getId(), $base->getMeta(), $base->getCount());
			                    $item->setCustomName($base->getCustomName());
			                    $newLore = $base->getLore();			
			                    foreach($base->getLore() as $case => $lore){
				                    $newLore[$case] = $lore;
			                    }			
			                    $newLore[] = TextFormat::BLUE."";			
			                    $newLore[] = TextFormat::GRAY."Cost";
			                    $newLore[] = TextFormat::RED."";
                                $newLore[] = TextFormat::GOLD.$cost." Coins";			
			                    $item->setLore($newLore);
			                    $menu->setItem(45, $item);			
						    }else{
			                    $guideA = $this->getDataItem(260, 0, 1);
		                        $guideA->setCustomName("-");
			                    $guideA->setLore([self::DONT_SELL_ANYTHING]);
		                        $menu->setItem(45, $guideA);		
						    }
						}else{
							$player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
							$player->sendMessage(self::DONT_ENOUGH_COINS);
						}
						$action->setCancelled();
					}
				}
			}
			switch($itemClicked->getCustomName()){
                case "-":				   
				    $player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
                    $action->setCancelled();
				break;	
                case "sell":
				    if(isset($this->itemCanSell[$checkDataItemSell])){
				        if(!isset($this->historyPlayers[$player->getName()])){
							$this->historyPlayers[$player->getName()] = [];
						}
						if(count($this->historyPlayers[$player->getName()]) < 1){
						    $this->historyPlayers[$player->getName()][0] = [$menu->getItem(49), $this->itemCanSell[$checkDataItemSell]*$menu->getItem(49)->getCount()];
				        }else{
							$this->historyPlayers[$player->getName()][count($this->historyPlayers[$player->getName()])] = [$menu->getItem(49), $this->itemCanSell[$checkDataItemSell]*$menu->getItem(49)->getCount()];
						}
						$api = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
						$cost = $this->itemCanSell[$checkDataItemSell]*$menu->getItem(49)->getCount();
				        
						$add = 1;
						$pure = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
                        $userGroup = $pure->getUserDataMgr()->getGroup($player);
						if($userGroup == "Vip++"){
							$add = 50/100;
						}
						$api->addMoney($player, $cost + ($cost * $add));
				        $player->sendMessage(TextFormat::BOLD.TextFormat::GREEN."Vật phẩm đã bị bán!".TextFormat::GOLD." +$cost coins");
						$player->getWorld()->addSound($player->getLocation()->asVector3(), new XpCollectSound(), [$player]);
				        if(isset($this->historyPlayers[$player->getName()])){
				            $cost = 0;
			                $base = null;
			                $itemSold = count($this->historyPlayers[$player->getName()]);			
			                if($itemSold == 1){
			                    foreach($this->historyPlayers[$player->getName()] as $case => $array){
				                    $base = $this->historyPlayers[$player->getName()][$case][0];
				                    $cost = $this->historyPlayers[$player->getName()][$case][1];
				                }
			                }
			                if($itemSold > 1){
				                $base = $this->historyPlayers[$player->getName()][$itemSold - 1][0];
				                $cost = $this->historyPlayers[$player->getName()][$itemSold - 1][1];
			                }
			                if($itemSold < 1){
				                $base = $this->getDataItem(260, 0, 1);
		                        $base->setCustomName("-");
			                    $base->setLore([self::DONT_SELL_ANYTHING]);	
			                }
			                $item = $this->getDataItem($base->getId(), $base->getMeta(), $base->getCount());
			                $item->setCustomName($base->getCustomName());
			                $newLore = $base->getLore();			
			                foreach($base->getLore() as $case => $lore){
				                $newLore[$case] = $lore;
			                }			
			                $newLore[] = TextFormat::BLUE."";			
			                $newLore[] = TextFormat::GRAY."Cost";
			                $newLore[] = TextFormat::RED."";
                            $newLore[] = TextFormat::GOLD.$cost." Coins";			
			                $item->setLore($newLore);
			                $menu->setItem(45, $item);			
						}else{
			                $guideA = $this->getDataItem(260, 0, 1);
		                    $guideA->setCustomName("-");
			                $guideA->setLore([self::DONT_SELL_ANYTHING]);
		                    $menu->setItem(45, $guideA);		
						}
		                $menu->setItem(49, $this->getDataItem(0, 0, 0));
					}else{
						$player->getInventory()->addItem($menu->getItem(49));
						$menu->setItem(49, $this->getDataItem(0, 0, 0));
						$player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
					}
					$action->setCancelled();
                break;				
			} 					
			foreach($this->dataPlayers[$player->getName()] as $dataItems){
			    if($itemClicked->getCustomName() == $dataItems["ITEM"]->getCustomName()){
					if(!isset($this->dataPlayers[$player->getName()][$itemClicked->getCustomName()])){
						$player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
						$player->sendMessage(TextFormat::RED."Something didn't go OK! ".TextFormat::GRAY."BACKEND_ERROR");
						$player->removeCurrentWindow();
					}
					$itemNeed = true;
					$costCoins = true;
					if($dataItems["MIXED_NEEDLE"] != false){
			            $count = 0;
					    $itemNeedRemove = [];
					    foreach($player->getInventory()->getContents() as $case => $checkInventory){
						    foreach($this->dataPlayers[$player->getName()][$itemClicked->getCustomName()]["MIXED_NEEDLE"] as $name => $need){
								if($checkInventory->getId() == (int)explode(",", $need)[0] 
							        and $checkInventory->getMeta() == (int)explode(",", $need)[1]
								    and $checkInventory->getCount() >= (int)explode(",", $need)[2]							
							    ){
									if(explode(",", $need)[3] != "false"){
									    if($itemClicked->getCustomName() == $name){
										    $count++;
									        $itemNeedRemove[] = [
								                "ID" => explode(",", $need)[0], 
									            "META" => explode(",", $need)[1],
									            "COUNT" => explode(",", $need)[2],
									            "NAME" => explode(",", $need)[3]
								            ];	
										}										
									}else{
										$count++;
									    $itemNeedRemove[] = [
								            "ID" => explode(",", $need)[0], 
									        "META" => explode(",", $need)[1],
									        "COUNT" => explode(",", $need)[2],
									        "NAME" => explode(",", $need)[3]
								        ];	
									}										
								}								
							}
						} 							
					    if($count >= count($this->dataPlayers[$player->getName()][$itemClicked->getCustomName()]["MIXED_NEEDLE"])){
							foreach($itemNeedRemove as $case => $item){
							    foreach($player->getInventory()->getContents() as $case => $checkInventory){
							        if($item["NAME"] != "false" and $item["NAME"] == $checkInventory->getCustomName()){
								        $checkInventory->setCount($checkInventory->getCount() - $item["COUNT"]);
                                        $player->getInventory()->setItem($case, $checkInventory);
							        }
									if($item["NAME"] != "false"){
								        $player->getInventory()->removeItem($this->getDataItem($item["ID"], $item["META"], $item["COUNT"])); 
								    }
							    }
						    }						    
					    }else{
							$player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
						    $player->sendMessage(self::DONT_ENOUGH_ITEM);		
                            $itemNeed = false;							
					    }
					}
			        if($dataItems["COST"] != false){
		                $api = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
						$money = $api->myMoney($player->getName());
						if($money >= $dataItems["COST"]){
							if($itemNeed == true){
							    $api->reduceMoney($player, $dataItems["COST"]);
							}
						}else{
							$player->getWorld()->addSound($player->getLocation()->asVector3(), new EndermanTeleportSound(), [$player]);
						    $player->sendMessage(self::DONT_ENOUGH_COINS);
							$costCoins = false;							
						}
					}
					if($itemNeed == true and $costCoins == true){
						$player->getInventory()->addItem($dataItems["ITEM"]);
						$player->getWorld()->addSound($player->getLocation()->asVector3(), new XpCollectSound(), [$player]);
					}
					$action->setCancelled();
				}					
			}
		});
		$menu->setCloseListener(function(Player $player) use ($menu) : void{
			if($menu->getItem(49)->getId() !== 0){
				$player->getInventory()->addItem($menu->getItem(49));
			}			
		});
	}
}