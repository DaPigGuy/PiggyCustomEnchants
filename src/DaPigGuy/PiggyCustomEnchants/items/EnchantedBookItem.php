<?php

namespace DaPigGuy\PiggyCustomEnchants\items;

use pocketmine\item\Item;

class EnchantedBookItem extends Item
{
  public function getMaxStackSize(): int
  {
      return 1;
  }
}