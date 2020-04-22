<?php

namespace SeeItem;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\item\Item;
use jojoe77777\FormAPI\SimpleForm;
use muqsit\invmenu\InvMenu;

class SeeItem extends PluginBase implements Listener{

	public $itemedPlayers = [];
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onDisable() {
		$this->itemedPlayers = [];
	}
	
    public function items($target, $sender){
		$name = $target->getName();
	    $menu = InvMenu::create(InvMenu::TYPE_HOPPER);
		$menu->readonly();
        $menu->setName("§r§e".$name."'s Item");
	    $inventory = $menu->getInventory();
	    $inventory->setItem(0, Item::get(289, 8, 1)->setCustomName("§r§l§bItem")->setLore(["§r§d-->"]));
        $inventory->setItem(1, Item::get(348, 0, 1)->setCustomName("§r§l§bItem")->setLore(["§r§d-->"]));
		$inventory->setItem(2, $this->item[$target->getName()]);
        $inventory->setItem(3, Item::get(348, 0, 1)->setCustomName("§r§l§bItem")->setLore(["§r§d<--"]));
	    $inventory->setItem(4, Item::get(289, 8, 1)->setCustomName("§r§l§bItem")->setLore(["§r§d<--"]));
        $menu->send($sender);
	}
	
	public function setItemTag($player, $value = true, $item) {
		if($player instanceof Player) $player = $player->getName();
		if($value) {
			$this->itemedPlayers[$player] = $item;
		} else {
			unset($this->itemedPlayers[$player]);
		}
	}
	
	public function onCommandPreProcess(PlayerCommandPreprocessEvent $event) {
		$player = $event->getPlayer();
		$name = $player->getName();
		$message = $event->getMessage();
		$item = $player->getInventory()->getItemInHand();
		$this->item[] = $name;
		if(strpos($message, "[item]") !== false){
			$this->item[$name] = $item;
			$event->setMessage(str_replace("[item]", "§r§b | §7§r".$item->getName()." §7(x".$item->getCount().")§r§b |§2 ", $message));
			#$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuserperm ".$player->getName()." itemed.action");
			$this->setItemTag($player, true, $item);
		}
	}
	
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        $name = $sender->getName();
		if($cmd->getName() === "seeitem"){
			$form = new SimpleForm(function (Player $player, $data){
		        $result = $data;
		        if($result === null){
			        return true;
			    }
			    switch($result){
				    case 0:
					if(count($this->itemedPlayers) === 0){
						$player->sendMessage("§cNo players have anything [item]ed.");
					}else{
				        if($data !== null){
				       		/** @var Player $target */
					        $target = $this->getServer()->getPlayerExact($result);
                            $this->items($target, $player);
                        }
					}
				    break;
			    }
		    });
		    $form->setTitle("§ePlayer Held Items");
		    $form->setContent("View any player's held item if they've typed [item] in chat.");
			if(count($this->itemedPlayers) === 0){
				$form->addButton("§cNo players have anything [item]ed.");
			}
		    foreach($this->itemedPlayers as $name => $item){
			    $form->addButton($name, -1, "", $name);
		    }
			$form->sendToPlayer($sender);
			return true;
		}
		return true;
	}
}