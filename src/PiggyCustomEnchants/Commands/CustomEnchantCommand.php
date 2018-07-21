<?php

namespace PiggyCustomEnchants\Commands;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
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
        $this->setAliases(["ce", "customenchants", "customenchantments", "customenchant"]);
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
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $forms = $sender instanceof Player && $plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled();
            if (count($args) < 1) {
                if ($forms) {
                    $this->formMenu($sender);
                    return true;
                }
                $sender->sendMessage(TextFormat::RED . "Usage: /customenchant <about|enchant|help|info|list>");
                return false;
            }
            switch ($args[0]) {
                case "about":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.about")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    if ($forms) {
                        $this->aboutForm($sender);
                        return true;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "PiggyCustomEnchants v" . $this->getPlugin()->getDescription()->getVersion() . " is a custom enchants plugin made by DaPigGuy (IGN: MCPEPIG) & Aericio.\n" . TextFormat::GREEN . "You can find it at https://github.com/DaPigGuy/PiggyCustomEnchants.");
                    break;
                case "enchant":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.enchant")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    if (count($args) < 2) {
                        if ($forms) {
                            $this->enchantForm($sender);
                            return true;
                        }
                        $sender->sendMessage(TextFormat::RED . "Usage: /customenchant enchant <enchant> [level] [player]");
                        return false;
                    }
                    if ($forms) {
                        $this->checkEnchantForm($sender, [$args[1], isset($args[2]) ? $args[2] : 1, isset($args[3]) ? $args[3] : $sender->getName()]);
                        return true;
                    }
                    $this->enchant($sender, $args[1], isset($args[2]) ? $args[2] : 1, isset($args[3]) ? $args[3] : $sender->getName());
                    break;
                case "help":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.help")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    if ($forms) {
                        $this->helpForm($sender);
                        return true;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "---PiggyCE Help---\n" . TextFormat::RESET . "/ce about: Information about this plugin\n/ce enchant: Enchant an item\n/ce help: Show the help page\n/ce info: Get description of enchant\n/ce list: List of enchants");
                    break;
                case "info":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.info")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    if (count($args) < 2) {
                        if ($forms) {
                            $this->infoForm($sender);
                            return true;
                        }
                        $sender->sendMessage(TextFormat::RED . "Usage: /customenchant info <enchant>");
                        return false;
                    }
                    if ($forms) {
                        $this->sendInfo($sender, $args[1]);
                        return true;
                    }
                    if ((is_numeric($args[1]) && ($enchant = CustomEnchants::getEnchantment($args[1])) !== null) || ($enchant = CustomEnchants::getEnchantmentByName($args[1])) !== null) {
                        $sender->sendMessage(TextFormat::GREEN . $enchant->getName() . "\n" . TextFormat::RESET . "ID: " . $enchant->getId() . "\nDescription: " . $plugin->getEnchantDescription($enchant) . "\nType: " . $plugin->getEnchantType($enchant) . "\nRarity: " . $plugin->getEnchantRarity($enchant) . "\nMax Level: " . $plugin->getEnchantMaxLevel($enchant));
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Invalid enchantment.");
                    }
                    break;
                case "list":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.list")) {
                        $this->error($sender, TextFormat::RED . "You do not have permission to do this.");
                        return false;
                    }
                    if ($forms) {
                        $this->listForm($sender);
                        return true;
                    }
                    $sender->sendMessage($this->list());
                    break;
                default:
                    if ($forms) {
                        $this->formMenu($sender);
                        return true;
                    }
                    $sender->sendMessage(TextFormat::RED . "Usage: /customenchant <about|enchant|help|info|list>");
                    break;
            }
            return true;
        }
        return false;
    }

    /**
     * @param Player $player
     */
    public function formMenu(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, ?int $data) {
                    if (!is_null($data)) {
                        switch ($data) {
                            case 0:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.about")) {
                                    $this->errorForm($player, TextFormat::RED . "You do not have permission to do this.");
                                    return false;
                                }
                                $this->aboutForm($player);
                                break;
                            case 1:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.enchant")) {
                                    $this->errorForm($player, TextFormat::RED . "You do not have permission to do this.");
                                    return false;
                                }
                                $this->enchantForm($player);
                                break;
                            case 2:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.help")) {
                                    $this->errorForm($player, TextFormat::RED . "You do not have permission to do this.");
                                    return false;
                                }
                                $this->helpForm($player);
                                break;
                            case 3:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.info")) {
                                    $this->errorForm($player, TextFormat::RED . "You do not have permission to do this.");
                                    return false;
                                }
                                $this->infoForm($player);
                                break;
                            case 4:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.list")) {
                                    $this->errorForm($player, TextFormat::RED . "You do not have permission to do this.");
                                    return false;
                                }
                                $this->listForm($player);
                                break;
                        }
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "Custom Enchants Menu");
                $form->addButton("About");
                $form->addButton("Enchant");
                $form->addButton("Help");
                $form->addButton("Info");
                $form->addButton("List");
                $form->sendToPlayer($player);
            }
        }
    }

    /**
     * @param CommandSender $sender
     * @param $error
     * @return bool
     */
    public function error(CommandSender $sender, $error)
    {
        if ($sender instanceof Player) {
            if ($this->errorForm($sender, $error)) {
                return true;
            }
        }
        $sender->sendMessage($error);
        return true;
    }

    /**
     * @param Player $player
     * @param $error
     * @return bool
     */
    public function errorForm(Player $player, $error)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, ?int $data) {
                    if (!is_null($data)) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::RED . "Error");
                $form->setContent($error);
                $form->addButton(TextFormat::BOLD . "Back");
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param Player $player
     */
    public function aboutForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, ?int $data) {
                    if (!is_null($data)) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "About");
                $form->setContent(TextFormat::GREEN . "PiggyCustomEnchants v" . $this->getPlugin()->getDescription()->getVersion() . " is a custom enchants plugin made by DaPigGuy (IGN: MCPEPIG) & Aericio. You can find it at https://github.com/DaPigGuy/PiggyCustomEnchants.");
                $form->addButton(TextFormat::BOLD . "Back");
                $form->sendToPlayer($player);
            }
        }
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function enchantForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createCustomForm(function (Player $player, ?array $data) {
                    if (!is_null($data)) {
                        if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                            $this->checkEnchantForm($player, $data);
                            return true;
                        }
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "Enchant");
                $form->addInput(TextFormat::GREEN . "Enchantment");
                $form->addInput(TextFormat::GREEN . "Level", "", 1);
                $form->addInput(TextFormat::GREEN . "Player", "", $player->getName());
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param Player $player
     * @param $data
     * @return bool
     */
    public function checkEnchantForm(Player $player, $data)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $enchant = null;
                if (is_numeric($data[0])) {
                    $enchant = CustomEnchants::getEnchantment((int)$data[0]);
                } else {
                    $enchant = CustomEnchants::getEnchantmentByName($data[0]);
                }
                if ($enchant == null) {
                    $this->errorForm($player, TextFormat::RED . "Invalid enchantment.");
                    return false;
                }
                $target = $this->getPlugin()->getServer()->getPlayer($data[2]);
                if (!$target instanceof Player) {
                    $this->errorForm($player, TextFormat::RED . "Invalid player.");
                    return false;
                }
                if (!$player->hasPermission("piggycustomenchants.overridecheck")) {
                    $result = $plugin->canBeEnchanted($target->getInventory()->getItemInHand(), $enchant, $data[1]);
                    if ($result !== true) {
                        switch ($result) {
                            case Main::NOT_COMPATIBLE:
                                $this->errorForm($player, TextFormat::RED . "The item is not compatible with this enchant.");
                                break;
                            case Main::NOT_COMPATIBLE_WITH_OTHER_ENCHANT:
                                $this->errorForm($player, TextFormat::RED . "The enchant is not compatible with another enchant.");
                                break;
                            case Main::MAX_LEVEL:
                                $this->errorForm($player, TextFormat::RED . "The max level is " . $plugin->getEnchantMaxLevel($enchant) . ".");
                                break;
                            case Main::MORE_THAN_ONE:
                                $this->errorForm($player, TextFormat::RED . "You can only enchant one item at a time.");
                                break;
                        }
                        return false;
                    }
                }
                $this->enchant($player, $data[0], $data[1], $data[2]);
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param Player $player
     */
    public function helpForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, ?int $data) {
                    if (!is_null($data)) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "Help");
                $form->setContent(TextFormat::GREEN . "Tell me you're joking... Why do you even need a help menu when you have the UI? Wait... why is this even here?");
                $form->addButton(TextFormat::BOLD . "Back");
                $form->sendToPlayer($player);
            }
        }
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function infoForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createCustomForm(function (Player $player, ?array $data) {
                    if (!is_null($data)) {
                        if (isset($data[0])) {
                            $this->sendInfo($player, $data[0]);
                        }
                    }
                });
                $form->setTitle(TextFormat::GREEN . "Info");
                $form->addInput(TextFormat::GREEN . "Enchantment");
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param Player $player
     * @param $enchant
     * @return bool
     */
    public function sendInfo(Player $player, $enchant)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, ?int $data) {
                    if (!is_null($data)) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                if ((is_numeric($enchant) && ($enchant = CustomEnchants::getEnchantment($enchant)) !== null) || ($enchant = CustomEnchants::getEnchantmentByName($enchant)) !== null) {
                    $form->setTitle(TextFormat::GREEN . "Info");
                    $form->setContent(TextFormat::GREEN . $enchant->getName() . "\n" . TextFormat::RESET . "ID: " . $enchant->getId() . "\nDescription: " . $plugin->getEnchantDescription($enchant) . "\nType: " . $plugin->getEnchantType($enchant) . "\nRarity: " . $plugin->getEnchantRarity($enchant) . "\nMax Level: " . $plugin->getEnchantMaxLevel($enchant));
                } else {
                    $form->setTitle(TextFormat::RED . "Error");
                    $form->setContent(TextFormat::RED . "Invalid enchantment.");
                }
                $form->addButton(TextFormat::BOLD . "Back");
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function listForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, ?int $data) {
                    if (!is_null($data)) {
                        $sorted = $this->getPlugin()->sortEnchants();
                        foreach ($sorted as $type => $enchants) {
                            if (array_search($type, array_keys($sorted)) == $data) {
                                $this->sendList($player, $type);
                                return true;
                            }
                        }
                        $this->formMenu($player);
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "List");
                $sorted = $plugin->sortEnchants();
                foreach ($sorted as $type => $enchants) {
                    $form->addButton($type);
                }
                $form->addButton(TextFormat::BOLD . "Back");
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param Player $player
     * @param $type
     */
    public function sendList(Player $player, $type)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
            if ($plugin->formsEnabled && $formsapi instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (!is_null($data)) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                $form->setContent(TextFormat::GREEN . TextFormat::BOLD . $type . "\n" . TextFormat::RESET . implode(", ", $this->getPlugin()->sortEnchants()[$type]));
                $form->addButton(TextFormat::BOLD . "Back");
                $form->sendToPlayer($player);
            }
        }
    }

    /**
     * @param CommandSender $sender
     * @param $enchantment
     * @param $level
     * @param $target
     * @return bool
     */
    public function enchant(CommandSender $sender, $enchantment, $level, $target)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if (!is_numeric($level)) {
                $level = 1;
                $sender->sendMessage(TextFormat::RED . "Level must be numerical. Setting level to 1.");
            }
            $target == null ? $target = $sender : $target = $this->getPlugin()->getServer()->getPlayer($target);
            if (!$target instanceof Player) {
                if ($target instanceof ConsoleCommandSender) {
                    $sender->sendMessage(TextFormat::RED . "Please provide a player.");
                    return false;
                }
                $sender->sendMessage(TextFormat::RED . "Invalid player.");
                return false;
            }
            $target->getInventory()->setItemInHand($plugin->addEnchantment($target->getInventory()->getItemInHand(), $enchantment, $level, $sender->hasPermission("piggycustomenchants.overridecheck") ? false : true, $sender));
            return true;
        }
        return false;
    }


    /**
     * @return string
     */
    public function list()
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $sorted = $plugin->sortEnchants();
            $list = "";
            foreach ($sorted as $type => $enchants) {
                $list .= "\n" . TextFormat::GREEN . TextFormat::BOLD . $type . "\n" . TextFormat::RESET;
                $list .= implode(", ", $enchants);
            }
            return $list;
        }
        return "";
    }
}