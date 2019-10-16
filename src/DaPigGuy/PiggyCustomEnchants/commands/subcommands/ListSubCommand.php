<?php


namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;


use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class ListSubCommand
 * @package DaPigGuy\PiggyCustomEnchants\commands\subcommands
 */
class ListSubCommand extends BaseSubCommand
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
            $this->sendTypesForm($sender);
            return;
        }
        $sender->sendMessage($this->getCustomEnchantList());
    }

    /**
     * @return CustomEnchant[]
     */
    public function getEnchantmentsByType(): array
    {
        $enchantmentsByType = [];
        foreach (CustomEnchantManager::getEnchantments() as $enchantment) {
            if (!isset($enchantmentsByType[$enchantment->getItemType()])) $enchantmentsByType[$enchantment->getItemType()] = [];
            $enchantmentsByType[$enchantment->getItemType()][] = $enchantment;
        }
        return array_map(function (array $typeEnchants) {
            uasort($typeEnchants, function (CustomEnchant $a, CustomEnchant $b) {
                return strcmp($a->getName(), $b->getName());
            });
            return $typeEnchants;
        }, $enchantmentsByType);
    }

    /**
     * @return string
     */
    public function getCustomEnchantList(): string
    {
        $enchantmentsByType = $this->getEnchantmentsByType();
        $listString = "";
        foreach (Utils::TYPE_NAMES as $type => $name) {
            if (isset($enchantmentsByType[$type])) {
                $listString .= "\n" . TextFormat::GREEN . TextFormat::BOLD . Utils::TYPE_NAMES[$type] . "\n" . TextFormat::RESET;
                $listString .= implode(", ", array_map(function (CustomEnchant $enchant) {
                    return $enchant->getName();
                }, $enchantmentsByType[$type]));
            }
        }
        return $listString;
    }

    /**
     * @param Player $player
     */
    public function sendTypesForm(Player $player): void
    {
        $enchantmentsByType = $this->getEnchantmentsByType();
        $form = new SimpleForm(function (Player $player, ?int $data) use ($enchantmentsByType): void {
            if ($data !== null) {
                if ($data === count($enchantmentsByType)) {
                    $player->getServer()->dispatchCommand($player, "ce");
                    return;
                }
                $type = array_keys($enchantmentsByType)[$data];
                $this->sendEnchantsForm($player, $type);
            }
        });
        $form->setTitle(TextFormat::GREEN . "Custom Enchants List");
        foreach ($enchantmentsByType as $type => $enchantments) {
            $form->addButton(Utils::TYPE_NAMES[$type]);
        }
        $form->addButton("Back");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param int $type
     */
    public function sendEnchantsForm(Player $player, int $type): void
    {
        $enchantmentsByType = $this->getEnchantmentsByType();
        $enchantForm = new SimpleForm(function (Player $player, ?int $data) use ($type, $enchantmentsByType): void {
            if ($data !== null) {
                if ($data === count($enchantmentsByType[$type])) {
                    $player->getServer()->dispatchCommand($player, "ce list");
                    return;
                }
                $infoForm = new SimpleForm(function (Player $player, ?int $data) use ($type): void {
                    if ($data !== null) $this->sendEnchantsForm($player, $type);
                });
                /** @var CustomEnchant $selectedEnchantment */
                $selectedEnchantment = array_values($enchantmentsByType[$type])[$data];
                $infoForm->setTitle(TextFormat::GREEN . $selectedEnchantment->getName() . " Enchantment");
                $infoForm->setContent(TextFormat::GREEN . $selectedEnchantment->getName() . "\n" . TextFormat::RESET . "ID: " . $selectedEnchantment->getId() . "\nDescription: " . $this->plugin->getEnchantmentDescription($selectedEnchantment) . "\nType: " . Utils::TYPE_NAMES[$type] . "\nRarity: " . Utils::RARITY_NAMES[$selectedEnchantment->getRarity()] . "\nMax Level: " . $selectedEnchantment->getMaxLevel());
                $infoForm->addButton("Back");
                $player->sendForm($infoForm);
            }
        });
        $enchantForm->setTitle(TextFormat::GREEN . Utils::TYPE_NAMES[$type] . " Enchants");
        /** @var CustomEnchant $enchantment */
        foreach ($enchantmentsByType[$type] as $enchantment) {
            $enchantForm->addButton($enchantment->getName());
        }
        $enchantForm->addButton("Back");
        $player->sendForm($enchantForm);
    }

    public function prepare(): void
    {
        $this->setPermission("piggycustomenchants.command.ce.list");
    }
}