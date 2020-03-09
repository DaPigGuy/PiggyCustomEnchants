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

class SpiderEnchant extends ToggleableEnchantment
{
    use TickingTrait;

    /** @var string */
    public $name = "Spider";
    /** @var int */
    public $maxLevel = 1;

    /** @var int */
    public $usageType = CustomEnchant::TYPE_CHESTPLATE;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_CHESTPLATE;


    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $player->setCanClimbWalls($this->canClimb($player));
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if (!$toggle) {
            $player->setCanClimbWalls(false);
        }
    }

    public function canClimb(Player $player): bool
    {
        /** @var Block $block */
        foreach (array_merge($player->getLevel()->getBlock($player->add(0, (count($player->getLevel()->getBlock($player)->getCollisionBoxes()) > 0 ? ceil($player->y) - $player->y + 0.01 : 0)))->getHorizontalSides(), $player->getLevel()->getBlock($player->add(0, 1))->getHorizontalSides()) as $block) {
            if ($block->isSolid()) {
                return true;
            }
        }
        return false;
    }
}