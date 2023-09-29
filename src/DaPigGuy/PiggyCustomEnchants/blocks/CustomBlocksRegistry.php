<?php

namespace DaPigGuy\PiggyCustomEnchants\blocks;

use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\block\Block;
use pocketmine\utils\CloningRegistryTrait;

/**
 * @method static PiggyObsidianBlock OBSIDIAN()
 */
final class CustomBlocksRegistry
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
        $blockFactory = CustomiesBlockFactory::getInstance();
        self::register("obsidian", $blockFactory->get("piggyce:magmawalker_obsidian"));
    }
}
