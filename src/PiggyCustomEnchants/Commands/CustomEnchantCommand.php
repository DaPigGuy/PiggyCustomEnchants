<?php

namespace PiggyCustomEnchants\Commands;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

/**
 * Class CustomEnchantCommand
 * @package PiggyCustomEnchants\Commands
 */
class CustomEnchantCommand extends PluginCommand
{
    /**
     * CustomEnchantCommand constructor.
     * @param string $name
     * @param Main $plugin
     */
    public function __construct($name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription("Enchant with custom enchants");
        $this->setUsage("/customenchant <enchant|list>");
        $this->setAliases(["ce"]);
        $this->setPermission("piggycustomenchants.command.ce");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if (!$this->testPermission($sender)) {
            return true;
        }
        if (count($args) < 1) {
            $sender->sendMessage("/customenchant <enchant|list>");
            return false;
        }
        switch($args[0]){
            case "list":
                $enchants = array();
                foreach (CustomEnchants::$enchantments as $id => $enchant){
                   array_push($enchants, $enchant->getName());
                }
                $sender->sendMessage(implode(", ", $enchants));
                break;
            case "enchant":
                if (count($args) < 3) {
                    $sender->sendMessage("/customenchant enchant <enchant> <level> [player]");
                }
                $target = $sender;
                if (isset($args[3])) {
                    $target = $this->getPlugin()->getServer()->getPlayer($args[3]);
                }
                if (!$target instanceof Player) {
                    if($target instanceof ConsoleCommandSender){
                        $sender->sendMessage("§cPlease provide a player.");
                        return false;
                    }
                    $sender->sendMessage("§cInvalid player.");
                    return false;
                }
                $this->getPlugin()->addEnchantment($target->getInventory()->getItemInHand(), $args[1], $args[2], $target);
                break;
        }
    }
}