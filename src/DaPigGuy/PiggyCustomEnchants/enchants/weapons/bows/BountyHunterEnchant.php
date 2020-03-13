<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;

class BountyHunterEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Bounty Hunter";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["cooldown" => 30, "base" => 7, "multiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $bountyDrop = $this->getBounty();
            $player->getInventory()->addItem(ItemFactory::get($bountyDrop, 0, mt_rand(1, $this->extraData["base"] + $level * $this->extraData["multiplier"])));
            $this->setCooldown($player, $this->extraData["cooldown"]);
        }
    }

    public function getBounty(): int
    {
        $random = mt_rand(0, 75);
        $currentChance = 2.5;
        if ($random < $currentChance) {
            return ItemIds::EMERALD;
        }
        $currentChance += 5;
        if ($random < $currentChance) {
            return ItemIds::DIAMOND;
        }
        $currentChance += 15;
        if ($random < $currentChance) {
            return ItemIds::GOLD_INGOT;
        }
        $currentChance += 27.5;
        if ($random < $currentChance) {
            return ItemIds::IRON_INGOT;
        }
        return ItemIds::COAL;
    }
}