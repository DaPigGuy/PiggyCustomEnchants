<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class BountyHunterEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Bounty Hunter";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;
    /** @var int */
    public $cooldownDuration = 30;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

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
            $player->getInventory()->addItem(Item::get($bountyDrop, 0, mt_rand(1, $this->extraData["base"] + $level * $this->extraData["multiplier"])));
        }
    }

    public function getBounty(): int
    {
        $random = mt_rand(0, 75);
        $currentChance = 2.5;
        if ($random < $currentChance) {
            return Item::EMERALD;
        }
        $currentChance += 5;
        if ($random < $currentChance) {
            return Item::DIAMOND;
        }
        $currentChance += 15;
        if ($random < $currentChance) {
            return Item::GOLD_INGOT;
        }
        $currentChance += 27.5;
        if ($random < $currentChance) {
            return Item::IRON_INGOT;
        }
        return Item::COAL;
    }
}