<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class HeadhunterEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Headhunter";

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["additionalMultiplier" => 0.1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            if ($event->getChild()->getPosition()->y > $event->getEntity()->getPosition()->y + $event->getEntity()->getEyeHeight()) {
                $event->setModifier($this->extraData["additionalMultiplier"] * $level, CustomEnchantIds::HEADHUNTER);
            }
        }
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOW;
    }
}