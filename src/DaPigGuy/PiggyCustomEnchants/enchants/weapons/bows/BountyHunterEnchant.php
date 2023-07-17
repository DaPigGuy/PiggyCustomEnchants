<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class BountyHunterEnchant extends ReactiveEnchantment
{
    public string $name = "Bounty Hunter";
    public int $rarity = Rarity::UNCOMMON;
    public int $cooldownDuration = 30;

    public int $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["base" => 7, "multiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $bountyDrop = $this->getBounty();
            $bountyDrop->setCount(mt_rand(1, $this->extraData["base"] + $level * $this->extraData["multiplier"]));
            $player->getInventory()->addItem($bountyDrop);
        }
    }

    public function getBounty(): Item
    {
        $random = mt_rand(0, 75);
        $currentChance = 2.5;
        if ($random < $currentChance) {
            return VanillaItems::EMERALD();
        }
        $currentChance += 5;
        if ($random < $currentChance) {
            return VanillaItems::DIAMOND();
        }
        $currentChance += 15;
        if ($random < $currentChance) {
            return VanillaItems::GOLD_INGOT();
        }
        $currentChance += 27.5;
        if ($random < $currentChance) {
            return VanillaItems::IRON_INGOT();
        }
        return VanillaItems::COAL();
    }
}