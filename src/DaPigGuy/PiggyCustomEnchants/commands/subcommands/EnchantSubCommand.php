<?php


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
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class EnchantSubCommand
 * @package DaPigGuy\PiggyCustomEnchants\commands\subcommands
 */
class EnchantSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchants */
    private $plugin;

    /**
     * EnchantSubCommand constructor.
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
        if ($sender instanceof Player && $this->plugin->areFormsEnabled() && !isset($args["enchantment"])) {
            $this->onRunForm($sender, $aliasUsed, $args);
            return;
        }

        if ((!$sender instanceof Player && empty($args["player"])) || !isset($args["enchantment"])) {
            $sender->sendMessage("Usage: /ce enchant <enchantment> <level> <player>");
            return;
        }
        $args["level"] = empty($args["level"]) ? 1 : $args["level"];
        if (!is_numeric($args["level"])) {
            $sender->sendMessage(TextFormat::RED . "Enchantment level must be numeric");
            return;
        }
        $target = empty($args["player"]) ? $sender : $this->plugin->getServer()->getPlayer($args["player"]);
        if (!$target instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Invalid player.");
            return;
        }
        $enchant = CustomEnchantManager::getEnchantmentByName($args["enchantment"]);
        if ($enchant === null) {
            $sender->sendMessage(TextFormat::RED . "Invalid enchantment.");
            return;
        };
        $item = $target->getInventory()->getItemInHand();
        if (!$sender->hasPermission("piggycustomenchants.overridecheck")) {
            if (!Utils::itemMatchesItemType($item, $enchant->getItemType())) {
                $sender->sendMessage(TextFormat::RED . TextFormat::RED . "The item is not compatible with this enchant.");
                return;
            }
            if ($args["level"] > $enchant->getMaxLevel()) {
                $sender->sendMessage(TextFormat::RED . TextFormat::RED . "The max level is " . $enchant->getMaxLevel() . ".");
                return;
            }
            if ($item->getCount() > 1) {
                $sender->sendMessage(TextFormat::RED . TextFormat::RED . "You can only enchant one item at a time.");
                return;
            }
            if (!Utils::checkEnchantIncompatibilities($item, $enchant)) {
                $sender->sendMessage(TextFormat::RED . TextFormat::RED . "This enchant is not compatible with another enchant.");
                return;
            }
        }
        $item->addEnchantment(new EnchantmentInstance($enchant, $args["level"]));
        $sender->sendMessage(TextFormat::GREEN . "Item successfully enchanted.");
        $target->getInventory()->setItemInHand($item);
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
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
                    $target = $this->plugin->getServer()->getPlayer($data[2]);
                    if (!$target instanceof Player) {
                        Utils::errorForm($player, TextFormat::RED . "Invalid player.");
                        return;
                    }
                    $item = $target->getInventory()->getItemInHand();
                    if (!$player->hasPermission("piggycustomenchants.overridecheck")) {
                        if (!Utils::itemMatchesItemType($item, $enchant->getItemType())) {
                            Utils::errorForm($player, TextFormat::RED . TextFormat::RED . "The item is not compatible with this enchant.");
                            return;
                        }
                        if ($data[1] > $enchant->getMaxLevel()) {
                            Utils::errorForm($player, TextFormat::RED . TextFormat::RED . "The max level is " . $enchant->getMaxLevel() . ".");
                            return;
                        }
                        if (($enchantmentInstance = $item->getEnchantment($enchant->getId())) !== null && $enchantmentInstance->getLevel() > $data[1]) {
                            Utils::errorForm($player, TextFormat::RED . TextFormat::RED . "The enchant has already been applied with a higher level on the item.");
                            return;
                        }
                        if ($item->getCount() > 1) {
                            Utils::errorForm($player, TextFormat::RED . TextFormat::RED . "You can only enchant one item at a time.");
                            return;
                        }
                        if (!Utils::checkEnchantIncompatibilities($item, $enchant)) {
                            Utils::errorForm($player, TextFormat::RED . TextFormat::RED . "This enchant is not compatible with another enchant.");
                            return;
                        }
                    }
                    $item->addEnchantment(new EnchantmentInstance($enchant, $data[1]));
                    $player->sendMessage(TextFormat::GREEN . "Item successfully enchanted.");
                    $target->getInventory()->setItemInHand($item);
                }
            });
            $form->setTitle(TextFormat::GREEN . "Apply Custom Enchantment");
            $form->addInput("Enchantment");
            $form->addInput("Level", "", 1);
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