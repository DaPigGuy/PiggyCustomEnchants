<?php

namespace DaPigGuy\PiggyCustomEnchants\blocks;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;

class PiggyObsidian extends Block
{
    /** @var int */
    private $age = 0;

    public function __construct()
    {
        parent::__construct(new BlockIdentifier(BlockLegacyIds::OBSIDIAN, 15), "Magmawalker Obsidian", BlockBreakInfo::instant());
    }

    public function onScheduledUpdate(): void
    {
        if (mt_rand(0, 3) === 0 || $this->countNeighbors() < 4) {
            $this->slightlyMelt(true);
        } else {
            $this->getPos()->getWorld()->scheduleDelayedBlockUpdate($this->getPos(), mt_rand(20, 40));
        }
    }

    public function onBreak(Item $item, Player $player = null): bool
    {
        $this->getPos()->getWorld()->setBlock($this->getPos(), VanillaBlocks::LAVA());
        return true;
    }

    public function getDrops(Item $item): array
    {
        return [];
    }

    public function countNeighbors(): int
    {
        $i = 0;
        foreach ($this->getAllSides() as $block) {
            if ($block instanceof PiggyObsidian) {
                $i++;
                if ($i >= 4) return $i;
            }
        }
        return $i;
    }

    public function slightlyMelt(bool $meltNeighbors): void
    {
        if ($this->age < 3) {
            $this->age++;
            $this->getPos()->getWorld()->scheduleDelayedBlockUpdate($this->getPos(), mt_rand(20, 40));
        } else {
            $this->getPos()->getWorld()->useBreakOn($this->getPos());
            if ($meltNeighbors) {
                foreach ($this->getAllSides() as $block) {
                    if ($block instanceof PiggyObsidian) {
                        $block->slightlyMelt(false);
                    }
                }
            }
        }
    }
}