<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\utils\PiggyExplosion;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ExplosiveEnchant extends BlockBreakingEnchant
{
    /** @var string */
    public $name = "Explosive";

    public function getDefaultExtraData(): array
    {
        return ["sizeMultiplier" => 5];
    }

    public function breakBlocks(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $explosion = new PiggyExplosion($event->getBlock()->getPos(), $level * $this->extraData["sizeMultiplier"], $player);
            $explosion->explodeA();
            $explosion->explodeB();
        }
    }

    public function getPriority(): int
    {
        return 4;
    }
}