<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use pocketmine\entity\object\FallingBlock;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

/**
 * Class BombardmentTNT
 * @package DaPigGuy\PiggyCustomEnchants\entities
 */
class BombardmentTNT extends FallingBlock
{
    /** @var int */
    private $enchantmentLevel;

    /**
     * BombardmentTNT constructor.
     * @param World $world
     * @param CompoundTag $nbt
     */
    public function __construct(World $world, CompoundTag $nbt)
    {
        parent::__construct($world, $nbt);
        $this->enchantmentLevel = $nbt->getInt("Level");
    }

    /**
     * @return int
     */
    public function getEnchantmentLevel(): int
    {
        return $this->enchantmentLevel;
    }

    /**
     * @return CompoundTag
     */
    public function saveNBT(): CompoundTag
    {
        return parent::saveNBT()->setInt("Level", $this->enchantmentLevel);
    }
}