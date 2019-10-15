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
use pocketmine\Player;

/**
 * Class HeadhunterEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows
 */
class HeadhunterEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Headhunter";

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param Event $event
     * @param int $level
     * @param int $stack
     */
    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            if ($event->getChild()->y > $event->getEntity()->y + $event->getEntity()->getEyeHeight()) {
                $event->setModifier(0.1 * $level, CustomEnchantIds::HEADHUNTER);
            }
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOW;
    }
}