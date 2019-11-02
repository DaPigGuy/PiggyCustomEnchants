<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use pocketmine\block\Block;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class SpiderEnchant
 */
class SpiderEnchant extends ToggleableEnchantment
{
    use TickingTrait;

    /** @var string */
    public $name = "Spider";
    /** @var int */
    public $maxLevel = 1;

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $player->setCanClimbWalls($this->canClimb($player));
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     * @param bool $toggle
     */
    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle)
    {
        if (!$toggle) {
            $player->setCanClimbWalls(false);
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_CHESTPLATE;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_CHESTPLATE;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function canClimb(Player $player): bool
    {
        /** @var Block $block */
        foreach (array_merge($player->getLevel()->getBlock($player)->getHorizontalSides(), $player->getLevel()->getBlock($player->add(0, 1))->getHorizontalSides()) as $block) {
            if ($block->isSolid()) {
                return true;
            }
        }
        return false;
    }
}