<?php

namespace DaPigGuy\PiggyCustomEnchants\blocks;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class PiggyObsidian
 * @package DaPigGuy\PiggyCustomEnchants\blocks
 */
class PiggyObsidian extends Block
{
    /** @var int */
    private $age = 0;

    public function __construct()
    {
        parent::__construct(new BlockIdentifier(BlockLegacyIds::OBSIDIAN, 15), "Magmawalker Obsidian", BlockBreakInfo::instant());
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
        $count = 0;
        for ($x = -1; $x <= 1; $x++) {
            for ($z = -1; $z <= 1; $z++) {
                $pos = $this->getPos()->add($x, 0, $z);
                if (!$this->getPos()->equals($pos)) {
                    $block = $this->getPos()->getWorld()->getBlock($pos);
                    if ($block instanceof PiggyObsidian) {
                        $count++;
                    }
                }
            }
        }
        if (mt_rand(0, 100) <= 33.33 || $count < 4) {
            $this->age++;
        }
        if ($this->age >= 4) {
            $this->getPos()->getWorld()->useBreakOn($this->getPos());
        }
        $this->getPos()->getWorld()->scheduleDelayedBlockUpdate($this->getPos(), mt_rand(1, 2) * 20);
    }

    /**
     * @param Item $item
     * @param Player|null $player
     * @return bool
     */
    public function onBreak(Item $item, Player $player = null): bool
    {
        $this->getPos()->getWorld()->setBlock($this->getPos(), VanillaBlocks::LAVA());
        return true;
    }

    /**
     * @param Item $item
     * @return array
     */
    public function getDrops(Item $item): array
    {
        return [];
    }
}