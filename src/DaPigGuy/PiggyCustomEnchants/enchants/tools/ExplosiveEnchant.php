<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\tools\BlockBreakingTrait;
use DaPigGuy\PiggyCustomEnchants\utils\PiggyExplosion;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class ExplosiveEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools
 */
class ExplosiveEnchant extends ReactiveEnchantment
{
    use BlockBreakingTrait;

    /** @var string */
    public $name = "Explosive";

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
            $this->setCooldown($player, 2147483647);

            $explosion = new PiggyExplosion($event->getBlock()->getPos(), $level * 5, $player);
            $explosion->explodeA();
            $explosion->explodeB();

            $this->setCooldown($player, -$this->getCooldown($player));
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