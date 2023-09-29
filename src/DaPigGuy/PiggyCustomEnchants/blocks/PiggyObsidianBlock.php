<?php

namespace DaPigGuy\PiggyCustomEnchants\blocks;

use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class PiggyObsidianBlock extends Opaque
{
    private int $age = 0;

    public function onScheduledUpdate(): void
    {
        if (mt_rand(0, 3) === 0 || $this->countNeighbors() < 4) {
            $this->slightlyMelt(true);
        } else {
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), mt_rand(20, 40));
        }
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        $world = $this->position->getWorld();
        if (($t = $world->getTile($this->position)) !== null) {
            $t->onBlockDestroyed();
        }
        $world->setBlock($this->position, VanillaBlocks::LAVA());
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
            if ($block instanceof PiggyObsidianBlock) {
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
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), mt_rand(20, 40));
        } else {
            $this->getPosition()->getWorld()->useBreakOn($this->getPosition());
            if ($meltNeighbors) {
                foreach ($this->getAllSides() as $block) {
                    if ($block instanceof PiggyObsidianBlock) {
                        $block->slightlyMelt(false);
                    }
                }
            }
        }
    }
}