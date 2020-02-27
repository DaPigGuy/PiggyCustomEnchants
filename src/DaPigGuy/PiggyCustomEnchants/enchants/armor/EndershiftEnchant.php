<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class EndershiftEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class EndershiftEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Endershift";

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [EntityDamageEvent::class];
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
        if ($event instanceof EntityDamageEvent) {
            if ($player->getHealth() - $event->getFinalDamage() <= 4) {
                if (!$player->getEffects()->has(VanillaEffects::SPEED())) {
                    $effect = new EffectInstance(VanillaEffects::SPEED(), 200 * $level, $level + 3, false);
                    $player->getEffects()->add($effect);
                }
                if (!$player->getEffects()->has(VanillaEffects::ABSORPTION())) {
                    $effect = new EffectInstance(VanillaEffects::ABSORPTION(), 200 * $level, $level + 3, false);
                    $player->getEffects()->add($effect);
                }
                $player->sendMessage("You feel a rush of energy coming from your armor!");
                $this->setCooldown($player, 300);
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }
}