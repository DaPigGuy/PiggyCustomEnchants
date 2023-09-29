<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\items;

use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\Item;
use pocketmine\utils\CloningRegistryTrait;

/**
 * @method static PiggyEnchantedBookItem ENCHANTED_BOOK()
 */
final class CustomItemsRegistry
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
        $itemFactory = CustomiesItemFactory::getInstance();
        self::register("enchanted_book", $itemFactory->get("piggyce:enchanted_book"));
    }
}