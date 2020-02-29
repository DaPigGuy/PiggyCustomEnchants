<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\Sword;
use pocketmine\Player;

/**
 * Class ArmoredEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class ArmoredEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Armored";

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["absorbedDamageMultiplier" => 0.2];
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param Event $event
     * @param int $level
     * @param int $stack
     */
    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof Player) {
                if ($damager->getInventory()->getItemInHand() instanceof Sword) {
                    $event->setModifier(-($event->getFinalDamage() * $this->extraData["absorbedDamageMultiplier"] * $level), CustomEnchantIds::ARMORED);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }
}