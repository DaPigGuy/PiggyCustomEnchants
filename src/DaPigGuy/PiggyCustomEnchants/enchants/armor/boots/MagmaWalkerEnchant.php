<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\boots;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Block;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class MagmaWalkerEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\boots
 */
class MagmaWalkerEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Magma Walker";
    /** @var int */
    public $maxLevel = 2;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [PlayerMoveEvent::class];
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
        if ($event instanceof PlayerMoveEvent) {
            if (!in_array($player->getLevel()->getBlock($player)->getId(), [Block::STILL_LAVA, Block::LAVA, Block::FLOWING_LAVA])) {
                $radius = $level + 2;
                for ($x = -$radius; $x <= $radius; $x++) {
                    for ($z = -$radius; $z <= $radius; $z++) {
                        $b = $player->getLevel()->getBlock($player->add($x, -1, $z));
                        if (in_array($b->getId(), [Block::STILL_LAVA, Block::LAVA, Block::FLOWING_LAVA])) {
                            if (!in_array($b->getLevel()->getBlock($b->add(0, 1))->getId(), [Block::STILL_LAVA, Block::LAVA, Block::FLOWING_LAVA])) {
                                if ($b->getId() !== Block::FLOWING_LAVA || $b->getDamage() === 0) {
                                    $player->getLevel()->setBlock($b, Block::get(Block::OBSIDIAN, 15));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_BOOTS;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOOTS;
    }
}