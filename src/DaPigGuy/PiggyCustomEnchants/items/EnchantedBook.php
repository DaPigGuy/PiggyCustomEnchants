<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\items;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\utils\CloningRegistryTrait;

/**
 * @method static Item ENCHANTED_BOOK()
 */
final class EnchantedBook
{
    use CloningRegistryTrait;

    private function __construct()
    {
        //NOOP
    }

    protected static function register(string $name, Item $item): void
    {
        self::_registryRegister($name, $item);
    }

    /**
     * @return Item[]
     * @phpstan-return array<string, Item>
     */
    public static function getAll(): array
    {
        /** @var Item[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup(): void
    {
        self::register("enchanted_book", new Item(new ItemIdentifier(ItemTypeIds::newId()), "Enchanted Book"));
    }
}