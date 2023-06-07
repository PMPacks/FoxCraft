<?php

namespace ItsRealNise\EnchantedUI;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\item\{
    Item,
    Tool,
    Armor,
    enchantment\Enchantment,
    enchantment\EnchantmentInstance
};
use pocketmine\utils\Config;
use ItsRealNise\EnchantedUI\libs\jojoe77777\FormAPI\{
    CustomForm,
    SimpleForm
};
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\plugin\PluginBase;
use onebone\economyapi\EconomyAPI;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use ItsRealNise\EnchantedUI\Commands\ShopCommand;

/**
 * Class Main
 * @package ItsRealNise\EnchantUI
 */
class Main extends PluginBase{

    /** @var Config $shop */
    public $shop;

    public $piggyCE;
    public $eco;

    public function onEnable(): void{
        if (is_null($this->getServer()->getPluginManager()->getPlugin("EconomyAPI"))) {
            $this->getLogger()->error("in order to use EnchantUI you need to install EconomyAPI.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        @mkdir($this->getDataFolder());
        $this->shop = new Config($this->getDataFolder() . "Shop.yml", Config::YAML);
        $this->UpdateConfig();
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("enchantui", new ShopCommand($this));
        $this->piggyCE = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    }

    public function UpdateConfig(): void{
        if(is_null($this->shop->getNested('version'))){
            file_put_contents($this->getDataFolder() . "Shop.yml",$this->getResource("Shop.yml"));
            $this->shop->reload();
            $this->getLogger()->notice("plugin config has been updated");
            return;
        }
        if($this->shop->getNested('version') != '0.5'){
            $shop = $this->shop->getAll();
            $shop['version'] = '0.5';
            $shop['enchanting-table'] = true;
            $shop['messages']['incompatible-enchantment'] = '';
            foreach($shop['shop'] as $list => $data){
                $data['incompatible-enchantments'] = array();
                $shop['shop'][$list] = $data;
            }
            $this->shop->setAll($shop);
            $this->shop->save();
            $this->shop->reload();
            $this->getLogger()->notice("Plugin config has been updated");
            return;
        }
    }

    /**
     * @param Player $player
     */
    public function listForm(Player $player): void{
        $form = new SimpleForm(function (Player $player, $data = null){
            if ($data === null){
                $this->sendNote($player , $this->shop->getNested('messages.thanks'));
                return;
            }
            $this->buyForm($player, $data);
        });
		$reduce = 1;
	    $pure = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
        $userGroup = $pure->getUserDataMgr()->getGroup($player);    					
        if($userGroup == "Vip++"){
			$reduce = 50/100;
		}
        foreach($this->shop->getNested('shop') as $name){
            $var = array(
                "NAME" => $name['name'],
                "PRICE" => $name['price'] * $reduce
            );
            $form->addButton($this->replace($this->shop->getNested('Button'), $var));
        }
        $form->setTitle($this->shop->getNested('Title'));
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param int $id
     */
    public function buyForm(Player $player,int $id): void{
        $array = $this->shop->getNested('shop');
        $form = new CustomForm(function (Player $player, $data = null) use ($array, $id){
            if($data === null) {
                return false;
            }
            $reduce = 1;
			$pure = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
            $userGroup = $pure->getUserDataMgr()->getGroup($player);    					
            if($userGroup == "Vip++"){
				$reduce = 50/100;
			}
			$var = array(
                "NAME" => $array[$id]['name'],
                "PRICE" => ($array[$id]['price'] * $data[1]) * $reduce,
                "LEVEL" => $data[1],
                "MONEY" => $this->eco->myMoney($player),
                "INCOMPATIBLE" => $incompatible = $this->isCompatible($player, $array[$id]['incompatible-enchantments'])
            );
            if ($data == null){
                $this->listForm($player);
                return;
            }
            if($player->getInventory()->getItemInHand()->getId() == 0 or $player->getInventory()->getItemInHand()->getCount() >= 2){
                $this->sendNote($player ,$this->shop->getNested('messages.hold-item'), $var);
                return;
            }
            if(!is_null($incompatible)){
                $this->sendNote($player , $this->shop->getNested('messages.incompatible-enchantment'), $var);
                return;
            }
            if($data[1] > $array[$id]['max-level'] or $data[1] < 1){
                return;
            }			
            if($this->eco->myMoney($player) > $c = (int)(($array[$id]['price'] * $data[1]) * $reduce)){
                $this->eco->reduceMoney($player, $c);
                $add = 0;
		        $pure = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
                $userGroup = $pure->getUserDataMgr()->getGroup($player);    					
                if($userGroup == "Vip++"){
		            $add = 15;
		        }
				if(rand(1, 100 + $data[1]) <= 50 + $add){
					$this->enchantItem($player, $data[1], $array[$id]['enchantment']);
					$player->sendTitle("§l§bEnchant thành công!");
				}else{
					$player->sendTitle("§l§cEnchant không thành công!");
				}					
                //$this->sendNote($player ,$this->shop->getNested('messages.paid-success'), $var);
            }else{
                $this->sendNote($player , $this->shop->getNested('messages.not-enough-money'), $var);
            }
        }
        );
        $reduce = 1;
		$pure = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
        $userGroup = $pure->getUserDataMgr()->getGroup($player);    					
        if($userGroup == "Vip++"){
		    $reduce = 50/100;
		}
		$form->addLabel($this->replace($this->shop->getNested('messages.label'),["PRICE" => (int)$array[$id]['price'] * $reduce]));
        $form->setTitle($this->shop->getNested('Title'));
        $form->addSlider($this->shop->getNested('slider-title'), 1, $array[$id]['max-level'], 1, -1);
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param null|mixed|string $msg
     */
    public function sendNote(Player $player, $msg, array $var = []): void{
        if(!is_null($msg)) $player->sendMessage($this->replace($msg, $var));
    }

    /**
     * @param Player $player
     * @param int $level
     * @param int|String $enchantment
     */
    public function enchantItem(Player $player, int $level, $enchantment): void{
        $item = $player->getInventory()->getItemInHand();
        if(is_string($enchantment)){
            $ench = EnchantmentIdMap::getInstance()->fromId((string) $enchantment);
            if($this->piggyCE !== null && $ench === null){
                $ench = CustomEnchants::getEnchantmentByName((string) $enchantment);
            }
            if($this->piggyCE !== null && $ench instanceof CustomEnchants){
                $this->piggyCE->addEnchantment($item, $ench->getName(), (int) $level);
            }else{
                $item->addEnchantment(new EnchantmentInstance($ench, (int) $level));
            }
        }
        if(is_int($enchantment)){
            $ench = EnchantmentIdMap::getInstance()->fromId($enchantment);
            $item->addEnchantment(new EnchantmentInstance($ench, (int) $level));
        }
        $player->getInventory()->setItemInHand($item);
    }

    /**
     * @param Player $player
     * @param array $array
     *
     * @return int|mixed|null
     */
    public function isCompatible(Player $player,array $array){
        $item = $player->getInventory()->getItemInHand();
        //TODO: the ability to use strings
        foreach($array as $enchantment){
            if($item->hasEnchantment($enchantment)){
                $id = $enchantment;
                return $id;
            }
        }
    }

    /**
     * @param string $message
     * @param array $keys
     *
     * @return string
     */
    public function replace(string $message, array $keys): string{
        foreach($keys as $word => $value){
            $message = str_replace("{".$word."}", $value, $message);
        }
        return $message;
    }
}
