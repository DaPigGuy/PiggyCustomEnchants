<?php

namespace PiggyCustomEnchants\Commands;

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
        $this->setUsage("/customenchant <enchant> <level> [player]");
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
        if (count($args) < 2) {
            $sender->sendMessage("/customenchant <enchant> <level> [player]");
            return false;
        }
        $target = $sender;
        if (isset($args[2])) {
            $target = $this->getPlugin()->getServer()->getPlayer($args[2]);
        }
        if (!$target instanceof Player) {
            if($target instanceof ConsoleCommandSender){
                $sender->sendMessage("§cPlease provide a player.");
                return false;
            }
            $sender->sendMessage("§cInvalid player.");
            return false;
        }
        $this->getPlugin()->addEnchantment($target->getInventory()->getItemInHand(), $args[0], $args[1], $target);
    }
}