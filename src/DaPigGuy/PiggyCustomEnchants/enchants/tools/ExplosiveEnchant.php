<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
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
class ExplosiveEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Explosive";

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
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
        if ($event instanceof BlockBreakEvent) {
            $this->setCooldown($player, 2147483647);

            $explosion = new PiggyExplosion($event->getBlock(), $level * 5, $player);
            $explosion->explodeA();
            $explosion->explodeB();

            $this->setCooldown($player, -$this->getCooldown($player));
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_TOOLS;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 4;
    }
}