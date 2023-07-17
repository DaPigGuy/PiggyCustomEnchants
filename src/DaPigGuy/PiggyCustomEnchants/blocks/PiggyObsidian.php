<?php

namespace DaPigGuy\PiggyCustomEnchants\blocks;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\utils\CloningRegistryTrait;

/**
 * @method static PiggyObsidianBlock PIGGY_OBSIDIAN()
 */
final class PiggyObsidian
{
    use CloningRegistryTrait;

    private function __construct()
    {
        //NOOP
    }

    protected static function register(string $name, Block $block): void
    {
        self::_registryRegister($name, $block);
    }

    /**
     * @return Block[]
     * @phpstan-return array<string, Block>
     */
    public static function getAll(): array
    {
        /** @var Block[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup(): void
    {
        self::register("obsidian", new PiggyObsidianBlock(new BlockIdentifier(BlockTypeIds::newId()), "Magmawalker Obsidian", new BlockTypeInfo(BlockBreakInfo::instant())));
    }
}
