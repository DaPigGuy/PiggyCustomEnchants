<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class ReactiveEnchantment
 * @package DaPigGuy\PiggyCustomEnchants\enchants
 */
class ReactiveEnchantment extends CustomEnchant
{
    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [];
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
    public function onReaction(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        $perWorldDisabledEnchants = CustomEnchantManager::getPlugin()->getConfig()->get("per-world-disabled-enchants");
        if (isset($perWorldDisabledEnchants[$player->getLevel()->getFolderName()]) && in_array(strtolower($this->name), $perWorldDisabledEnchants[$player->getLevel()->getFolderName()])) return;
        if ($this->getCooldown($player) > 0) return;
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getEntity() === $player && $event->getDamager() !== $player && $this->shouldReactToDamage()) return;
            if ($event->getEntity() !== $player && $this->shouldReactToDamaged()) return;
        }
        if (mt_rand(0, 100) <= $this->getChance($level)) $this->react($player, $item, $inventory, $slot, $event, $level, $stack);
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

    }

    /**
     * @param int $level
     * @return int
     */
    public function getChance(int $level): int
    {
        return 100;
    }

    /**
     * @return bool
     */
    public function shouldReactToDamage(): bool
    {
        return $this->getItemType() === CustomEnchant::ITEM_TYPE_WEAPON || $this->getItemType() === CustomEnchant::ITEM_TYPE_BOW;
    }

    /**
     * @return bool
     */
    public function shouldReactToDamaged(): bool
    {
        return $this->getUsageType() === CustomEnchant::TYPE_ARMOR_INVENTORY;
    }
}