# PiggyCustomEnchants
You saw that server had custom enchants? Now yours does too!

# Notice
We do not support Tesseract or any spoons.

# For Developers

To add a custom enchantment to the item player is holding:
```
$item = $player->getInventory->getItemInHand(); //$player as in the player
$ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
$ce->addEnchantment($item, "Porkified", 3, $player, $sender, $slot, $check) //You don't need to add $sender, $slot, or $check, but if you want to override checks, you can set it to true and you can set $slot and $sender to null.
```

To add a custom enchantment to an item in a certain slot:
```
$item = $player->getInventory->getItem($slot); //$player as in the player and $slot as in the slot
$ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
$ce->addEnchantment($item, "Porkified", 3, $player, $sender, $slot, $check) //If you don't have a sender, you can replace it with null. You don't need to add $check but if you want to override checks, you can set it to true.
```

