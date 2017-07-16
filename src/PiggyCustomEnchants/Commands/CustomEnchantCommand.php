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
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool
    {
        if (!$this->testPermission($sender)) {
            return true;
        }
        if (count($args) < 1) {
            $sender->sendMessage("/customenchant <enchant|list>");
            return false;
        }
        switch ($args[0]) {
            case "list":
                $sorted = $this->getPlugin()->sortEnchants();
                $list = "";
                foreach ($sorted as $type => $enchants) {
                    $list .= "\n" . TextFormat::GREEN . TextFormat::BOLD . $type . "\n" . TextFormat::RESET;
                    $list .= implode(", ", $enchants);
                }
                $sender->sendMessage($list);
                break;
            case "enchant":
                if (count($args) < 2) {
                    $sender->sendMessage("/customenchant enchant <enchant> [level] [player]");
                    return false;
                }
                $target = $sender;
                if (!isset($args[2])) {
                    $args[2] = 1;
                }
                if (isset($args[3])) {
                    $target = $this->getPlugin()->getServer()->getPlayer($args[3]);
                }
                if (!$target instanceof Player) {
                    if ($target instanceof ConsoleCommandSender) {
                        $sender->sendMessage("§cPlease provide a player.");
                        return false;
                    }
                    $sender->sendMessage("§cInvalid player.");
                    return false;
                }
                $this->getPlugin()->addEnchantment($target->getInventory()->getItemInHand(), $args[1], $args[2], $target, $sender, null, $sender->hasPermission("piggycustomenchants.overridecheck") ? false : true);
                break;
            default:
                $sender->sendMessage("/customenchant <enchant|list>");
                break;
        }
        return true;
    }
}