<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use DaPigGuy\PiggyCustomEnchants\utils\PiggyExplosion;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ExplosiveEnchant extends RecursiveEnchant
{
    public string $name = "Explosive";
    public int $rarity = Rarity::UNCOMMON;

    public int $itemType = CustomEnchant::ITEM_TYPE_TOOLS;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["sizeMultiplier" => 5, "entityDamage" => true];
    }

    public function safeReact(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $explosion = new PiggyExplosion($event->getBlock()->getPosition(), $level * $this->extraData["sizeMultiplier"], $player, $this->extraData["entityDamage"]);
            $explosion->explodeA();
            $explosion->explodeB();
        }
    }

    public function getPriority(): int
    {
        return 4;
    }
}