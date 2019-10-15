<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class ParalyzeEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows
 */
class ParalyzeEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Paralyze";

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
            $entity = $event->getEntity();
            if ($entity instanceof Living) {
                if (!$entity->hasEffect(Effect::SLOWNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 60 + ($level - 1) * 20, 5 + $level - 1, false);
                    $entity->addEffect($effect);
                }
                if (!$entity->hasEffect(Effect::BLINDNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 60 + ($level - 1) * 20, 1, false);
                    $entity->addEffect($effect);
                }
                if (!$entity->hasEffect(Effect::WEAKNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::WEAKNESS), 60 + ($level - 1) * 20, 5 + $level - 1, false);
                    $entity->addEffect($effect);
                }
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