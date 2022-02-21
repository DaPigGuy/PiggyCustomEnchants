<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class EnchantSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchants */
    protected $plugin;

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player && $this->plugin->areFormsEnabled() && !isset($args["enchantment"])) {
            $this->onRunForm($sender, $aliasUsed, $args);
            return;
        }

        if ((!$sender instanceof Player && empty($args["player"])) || !isset($args["enchantment"])) {
            $sender->sendMessage("Usage: /ce enchant <enchantment> <level> <player>");
            return;
        }
        $args["level"] = empty($args["level"]) ? 1 : $args["level"];
        if (!is_int($args["level"])) {
            $sender->sendMessage(TextFormat::RED . "Enchantment level must be an integer");
            return;
        }
        $target = empty($args["player"]) ? $sender : $this->plugin->getServer()->getPlayerByPrefix($args["player"]);
        if (!$target instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Invalid player.");
            return;
        }
        $enchant = CustomEnchantManager::getEnchantmentByName($args["enchantment"]);
        if ($enchant === null) {
            $sender->sendMessage(TextFormat::RED . "Invalid enchantment.");
            return;
        }
        $item = $target->getInventory()->getItemInHand();
        if (!$sender->hasPermission("piggycustomenchants.overridecheck")) {
            if (!Utils::itemMatchesItemType($item, $enchant->getItemType())) {
                $sender->sendMessage(TextFormat::RED . "The item is not compatible with this enchant.");
                return;
            }
            if ($args["level"] > $enchant->getMaxLevel()) {
                $sender->sendMessage(TextFormat::RED . "The max level is " . $enchant->getMaxLevel() . ".");
                return;
            }
            if ($item->getCount() > 1) {
                $sender->sendMessage(TextFormat::RED . "You can only enchant one item at a time.");
                return;
            }
            if (!Utils::checkEnchantIncompatibilities($item, $enchant)) {
                $sender->sendMessage(TextFormat::RED . "This enchant is not compatible with another enchant.");
                return;
            }
        }
        $item->addEnchantment(new EnchantmentInstance($enchant, $args["level"]));
        $sender->sendMessage(TextFormat::GREEN . "Item successfully enchanted.");
        $target->getInventory()->setItemInHand($item);
    }

    public function onRunForm(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $form = new CustomForm(function (Player $player, ?array $data): void {
                if ($data !== null) {
                    $enchant = is_numeric($data[0]) ? CustomEnchantManager::getEnchantment((int)$data[0]) : CustomEnchantManager::getEnchantmentByName($data[0]);
                    if ($enchant == null) {
                        Utils::errorForm($player, TextFormat::RED . "Invalid enchantment.");
                        return;
                    }
                    $target = $this->plugin->getServer()->getPlayerByPrefix($data[2]);
                    if (!$target instanceof Player) {
                        Utils::errorForm($player, TextFormat::RED . "Invalid player.");
                        return;
                    }
                    $item = $target->getInventory()->getItemInHand();
                    if (!$player->hasPermission("piggycustomenchants.overridecheck")) {
                        if (!Utils::itemMatchesItemType($item, $enchant->getItemType())) {
                            Utils::errorForm($player, TextFormat::RED . "The item is not compatible with this enchant.");
                            return;
                        }
                        if ($data[1] > $enchant->getMaxLevel()) {
                            Utils::errorForm($player, TextFormat::RED . "The max level is " . $enchant->getMaxLevel() . ".");
                            return;
                        }
                        if (($enchantmentInstance = $item->getEnchantment($enchant)) !== null && $enchantmentInstance->getLevel() > $data[1]) {
                            Utils::errorForm($player, TextFormat::RED . "The enchant has already been applied with a higher level on the item.");
                            return;
                        }
                        if ($item->getCount() > 1) {
                            Utils::errorForm($player, TextFormat::RED . "You can only enchant one item at a time.");
                            return;
                        }
                        if (!Utils::checkEnchantIncompatibilities($item, $enchant)) {
                            Utils::errorForm($player, TextFormat::RED . "This enchant is not compatible with another enchant.");
                            return;
                        }
                    }
                    $item->addEnchantment(new EnchantmentInstance($enchant, (int)$data[1]));
                    $player->sendMessage(TextFormat::GREEN . "Item successfully enchanted.");
                    $target->getInventory()->setItemInHand($item);
                }
            });
            $form->setTitle(TextFormat::GREEN . "Apply Custom Enchantment");
            $form->addInput("Enchantment");
            $form->addInput("Level", "", "1");
            $form->addInput("Player", "", $sender->getName());
            $sender->sendForm($form);
        }
    }

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission("piggycustomenchants.command.ce.enchant");
        $this->registerArgument(0, new RawStringArgument("enchantment", true));
        $this->registerArgument(1, new IntegerArgument("level", true));
        $this->registerArgument(2, new RawStringArgument("player", true));
    }
}