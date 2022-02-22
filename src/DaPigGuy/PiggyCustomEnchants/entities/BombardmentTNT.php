<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\nbt\tag\CompoundTag;

class BombardmentTNT extends FallingBlock
{
    private int $enchantmentLevel;

    public function __construct(Location $location, ?CompoundTag $nbt = null, int $enchantmentLevel = 1)
    {
        parent::__construct($location, VanillaBlocks::TNT(), $nbt);
        $this->enchantmentLevel = $enchantmentLevel;
    }

    public function getEnchantmentLevel(): int
    {
        return $this->enchantmentLevel;
    }

    public function saveNBT(): CompoundTag
    {
        return parent::saveNBT()->setInt("Level", $this->enchantmentLevel);
    }
}