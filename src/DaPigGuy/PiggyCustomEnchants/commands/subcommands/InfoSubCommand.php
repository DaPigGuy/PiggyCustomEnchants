<?php

namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class InfoSubCommand
 * @package DaPigGuy\PiggyCustomEnchants\commands\subcommands
 */
class InfoSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchants */
    private $plugin;

    /**
     * ListSubCommand constructor.
     * @param PiggyCustomEnchants $plugin
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(PiggyCustomEnchants $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player && $this->plugin->areFormsEnabled()) {
            if (isset($args["enchantment"])) {
                $enchantment = CustomEnchantManager::getEnchantmentByName($args["enchantment"]);
                if ($enchantment === null) {
                    Utils::errorForm($sender, TextFormat::RED . "Invalid enchantment.");
                    return;
                }
                $this->showInfo($sender, $enchantment);
                return;
            }
            $form = new CustomForm(function (Player $player, ?array $data) {
                if ($data !== null) {
                    $enchantment = CustomEnchantManager::getEnchantmentByName($data[0]);
                    if ($enchantment === null) {
                        Utils::errorForm($player, TextFormat::RED . "Invalid enchantment.");
                        return;
                    }
                    $this->showInfo($player, $enchantment);
                }
            });
            $form->setTitle(TextFormat::GREEN . "Custom Enchant Info");
            $form->addInput("Enchantment");
            $sender->sendForm($form);
            return;
        }
        if (!isset($args["enchantment"])) {
            $sender->sendMessage("/ce info <enchantment>");
            return;
        }
        $enchantment = CustomEnchantManager::getEnchantmentByName($args["enchantment"]);
        if ($enchantment === null) {
            $sender->sendMessage(TextFormat::RED . "Invalid enchantment.");
            return;
        }
        $sender->sendMessage(TextFormat::GREEN . $enchantment->getName() . "\n" . TextFormat::RESET . "ID: " . $enchantment->getId() . "\nDescription: " . $this->plugin->getEnchantmentDescription($enchantment) . "\nType: " . Utils::TYPE_NAMES[$enchantment->getItemType()] . "\nRarity: " . Utils::RARITY_NAMES[$enchantment->getRarity()] . "\nMax Level: " . $enchantment->getMaxLevel());
    }

    /**
     * @param Player $player
     * @param CustomEnchant $enchantment
     */
    public function showInfo(Player $player, CustomEnchant $enchantment): void
    {
        $infoForm = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data !== null) $this->plugin->getServer()->dispatchCommand($player, "ce");
        });
        $infoForm->setTitle(TextFormat::GREEN . $enchantment->getName() . " Enchantment");
        $infoForm->setContent(TextFormat::GREEN . $enchantment->getName() . "\n" . TextFormat::RESET . "ID: " . $enchantment->getId() . "\nDescription: " . $this->plugin->getEnchantmentDescription($enchantment) . "\nType: " . Utils::TYPE_NAMES[$enchantment->getItemType()] . "\nRarity: " . Utils::RARITY_NAMES[$enchantment->getRarity()] . "\nMax Level: " . $enchantment->getMaxLevel());
        $infoForm->addButton("Back");
        $player->sendForm($infoForm);
    }

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission("piggycustomenchants.command.ce.list");
        $this->registerArgument(0, new RawStringArgument("enchantment", true));
    }
}