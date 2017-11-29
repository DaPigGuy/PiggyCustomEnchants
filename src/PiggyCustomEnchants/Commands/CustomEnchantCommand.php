<?php

namespace PiggyCustomEnchants\Commands;

use PiggyCustomEnchants\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

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
        $this->setUsage("/customenchant <about|enchant|help|info|list>");
        $this->setAliases(["ce"]);
        $this->setPermission("piggycustomenchants.command.ce");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /customenchant <about|enchant|help|info|list>");
            return false;
        }
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            switch ($args[0]) {
                case "about":
                    if(!$sender->hasPermission("piggycustomenchants.command.ce.about")){
                        $sender->sendMessage(TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "PiggyCustomEnchants v" . $this->getPlugin()->getDescription()->getVersion() . " is a custom enchants plugin made by DaPigGuy (IGN: MCPEPIG) & Aericio.\nYou can find it at https://github.com/DaPigGuy/PiggyCustomEnchants.");
                    break;
                case "enchant":
                    if(!$sender->hasPermission("piggycustomenchants.command.ce.enchant")){
                        $sender->sendMessage(TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    if (count($args) < 2) {
                        $sender->sendMessage(TextFormat::RED . "Usage: /customenchant enchant <enchant> [level] [player]");
                        return false;
                    }
                    $target = $sender;
                    if (!isset($args[2])) {
                        $args[2] = 1;
                    }
                    if(!is_numeric($args[2])){
                        $args[2] = 1;
                        $sender->sendMessage(TextFormat::RED . "Level must be numerical. Setting level to 1.");
                    }
                    if (isset($args[3])) {
                        $target = $this->getPlugin()->getServer()->getPlayer($args[3]);
                    }
                    if (!$target instanceof Player) {
                        if ($target instanceof ConsoleCommandSender) {
                            $sender->sendMessage(TextFormat::RED . "Please provide a player.");
                            return false;
                        }
                        $sender->sendMessage(TextFormat::RED . "Invalid player.");
                        return false;
                    }
                    $target->getInventory()->setItemInHand($plugin->addEnchantment($target->getInventory()->getItemInHand(), $args[1], $args[2], $sender->hasPermission("piggycustomenchants.overridecheck") ? false : true, $sender));
                    break;
                case "help":
                    if(!$sender->hasPermission("piggycustomenchants.command.ce.help")){
                        $sender->sendMessage(TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "---PiggyCE Help---\n" . TextFormat::RESET . "/ce about: Information about this plugin\n/ce enchant: Enchant an item\n/ce help: Show the help page\n/ce list: List of enchants\n/ce info: Info About An Enchant");
                    break;
                case "list":
                    if(!$sender->hasPermission("piggycustomenchants.command.ce.list")){
                        $sender->sendMessage(TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    $sorted = $plugin->sortEnchants();
                    $list = "";
                    foreach ($sorted as $type => $enchants) {
                        $list .= "\n" . TextFormat::GREEN . TextFormat::BOLD . $type . "\n" . TextFormat::RESET;
                        $list .= implode(", ", $enchants);
                    }
                    $sender->sendMessage($list);
                    break;
                case "info":
                    if(!$sender->hasPermission("piggycustomenchants.command.ce.info")){
                        $sender->sendMessage(TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    if (count($args) < 2) {
                        $sender->sendMessage(TextFormat::RED . "Usage: /customenchant info <enchant>");
                        return false;
                    }
                    switch($args[1]){
                   	    case "antiknockback":
                            $sender->sendMessage(TextFormat::GREEN . "AntiKnockBack\n" . TextFormat::WHITE . "Description: Reduces knockback by 25% per armor piece\nType: Armor\nMax Level: 1\nRarity: Rare");
                        break;
                    } 
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . "Usage: /customenchant <about|enchant|help|info|list>");
                    break;
            }
            return true;
        }
        return false;
    }
}
