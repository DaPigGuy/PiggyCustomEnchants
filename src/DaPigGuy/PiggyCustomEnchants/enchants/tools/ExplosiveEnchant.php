<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\utils\PiggyExplosion;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class ExplosiveEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools
 */
class ExplosiveEnchant extends BlockBreakingEnchant
{
    /** @var string */
    public $name = "Explosive";

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["sizeMultiplier" => 5];
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
    public function breakBlocks(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $explosion = new PiggyExplosion($event->getBlock(), $level * $this->extraData["sizeMultiplier"], $player);
            $explosion->explodeA();
            $explosion->explodeB();
        }
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 4;
    }
}