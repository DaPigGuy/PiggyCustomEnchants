<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class SoulboundEnchant extends ReactiveEnchantment
{
    public string $name = "Soulbound";
    public int $rarity = Rarity::MYTHIC;

    public int $usageType = CustomEnchant::TYPE_ANY_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_GLOBAL;

    public function getReagent(): array
    {
        return [PlayerDeathEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerDeathEvent) {
            $drops = $event->getDrops();
            foreach ($drops as $key => $drop) {
                if ($drop->equalsExact($item)) {
                    unset($drops[$key]);
                    break;
                }
            }
            $event->setDrops($drops);
            $level > 1 ? $item->addEnchantment(new EnchantmentInstance(CustomEnchantManager::getEnchantment(CustomEnchantIds::SOULBOUND), $level - 1)) : $item->removeEnchantment(CustomEnchantManager::getEnchantment(CustomEnchantIds::SOULBOUND));
            $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($inventory, $slot, $item): void {
                $inventory->setItem($slot, $item);
            }), 1);
        }
    }
}