<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class MissileEnchant extends ReactiveEnchantment
{
    public string $name = "Missile";

    public int $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [ProjectileHitBlockEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["multiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof ProjectileHitBlockEvent) {
            $projectile = $event->getEntity();
            for ($i = 0; $i <= $level * $this->extraData["multiplier"]; $i++) {
                $tnt = new PiggyTNT($projectile->getLocation(), null, $this->plugin->getConfig()->getNested("world-damage.missile", false));
                $tnt->setFuse(40);
                $tnt->setOwningEntity($player);
                $tnt->spawnToAll();
                $projectile->flagForDespawn();
            }
        }
    }
}