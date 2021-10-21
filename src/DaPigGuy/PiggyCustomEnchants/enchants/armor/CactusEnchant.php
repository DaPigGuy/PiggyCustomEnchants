<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class CactusEnchant extends TickingEnchantment
{
    public string $name = "Cactus";
    public int $maxLevel = 1;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getTickingInterval(): int
    {
        return 10;
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy(1, 0, 1), $player) as $entity) {
            if ($entity instanceof Living && !AllyChecks::isAlly($player, $entity)) {
                $ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_CONTACT, 1);
                $entity->attack($ev);
            }
        }
    }
}