<?php

namespace DaPigGuy\PiggyCustomEnchants\blocks;

use pocketmine\block\Block;
use pocketmine\block\Obsidian;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class PiggyObsidian
 * @package DaPigGuy\PiggyCustomEnchants\blocks
 */
class PiggyObsidian extends Obsidian
{
    /** @var int */
    private $age = 0;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->isMagmaWalker() ? "Magmawalker Obsidian" : "Obsidian";
    }

    /**
     * @return bool
     */
    public function isMagmaWalker(): bool
    {
        return $this->getDamage() === 15;
    }

    /**
     * @return bool
     */
    public function ticksRandomly(): bool
    {
        return true;
    }

    public function onRandomTick(): void
    {
        $this->onScheduledUpdate();
    }

    public function onNearbyBlockChange(): void
    {
        $this->onScheduledUpdate();
    }

    public function onScheduledUpdate(): void
    {
        if ($this->isMagmaWalker()) {
            $count = 0;
            for ($x = -1; $x <= 1; $x++) {
                for ($z = -1; $z <= 1; $z++) {
                    $pos = $this->add($x, 0, $z);
                    if (!$this->equals($pos)) {
                        $block = $this->getLevel()->getBlock($pos);
                        if ($block->getId() === $this->getId() && $block->isMagmaWalker()) {
                            $count++;
                        }
                    }
                }
            }
            if (mt_rand(0, 100) <= 33.33 || $count < 4) {
                $this->age++;
            }
            if ($this->age >= 4) {
                $this->getLevel()->useBreakOn($this);
            }
            $this->getLevel()->scheduleDelayedBlockUpdate($this, mt_rand(1, 2) * 20);
        }
    }

    /**
     * @param Item $item
     * @param Player|null $player
     * @return bool
     */
    public function onBreak(Item $item, Player $player = null): bool
    {
        return $this->getLevel()->setBlock($this, Block::get($this->isMagmaWalker() ? Block::LAVA : Block::AIR), true);
    }

    /**
     * @param Item $item
     * @return array
     */
    public function getDrops(Item $item): array
    {
        return $this->isMagmaWalker() ? [] : parent::getDrops($item);
    }
}