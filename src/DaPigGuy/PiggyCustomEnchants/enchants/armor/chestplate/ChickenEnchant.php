<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class ChickenEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate
 */
class ChickenEnchant extends TickingEnchantment
{
    /** @var string */
    public $name = "Chicken";

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if (mt_rand(0, 100) <= 5 * $level) {
            $drops = $this->plugin->getConfig()->getNested("chicken.drops", []);
            if (!is_array($drops)) {
                $drops = [$drops];
            }
            $drop = array_rand($drops);
            $drop = explode(":", $drops[$drop]);
            $item = count($drop) < 3 ? Item::get(Item::GOLD_INGOT, 0, 1) : Item::get((int)$drop[0], (int)$drop[1], (int)$drop[2]);
            $vowels = ["a", "e", "i", "o", "u"];
            $player->getLevel()->dropItem($player, $item, $player->getDirectionVector()->multiply(-0.4));
            $player->sendTip(TextFormat::GREEN . "You have laid a" . (in_array(strtolower($item->getName()[0]), $vowels) ? "n " : " ") . $item->getName() . "...");
        } else {
            $player->getLevel()->dropItem($player, Item::get(Item::EGG, 0, 1), $player->getDirectionVector()->multiply(-0.4));
            $player->sendTip(TextFormat::GREEN . "You have laid an egg.");
        }
    }

    /**
     * @return int
     */
    public function getTickingInterval(): int
    {
        return 1200 * 5;
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_CHESTPLATE;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_CHESTPLATE;
    }
}