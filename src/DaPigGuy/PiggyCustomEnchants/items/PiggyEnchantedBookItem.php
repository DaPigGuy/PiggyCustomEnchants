<?php

namespace DaPigGuy\PiggyCustomEnchants\items;

use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

final class PiggyEnchantedBookItem extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier, string $name = "Enchanted Book")
    {
        parent::__construct($identifier, $name);
        $this->initComponent("book_enchanted");
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }
}