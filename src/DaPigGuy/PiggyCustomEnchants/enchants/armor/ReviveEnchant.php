<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\FlameParticle;

/**
 * Class ReviveEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class ReviveEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Revive";

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
            if ($event->getFinalDamage() >= $player->getHealth()) {
                $level > 1 ? $item->addEnchantment(new EnchantmentInstance(Enchantment::get(CustomEnchantIds::REVIVE), $level - 1)) : $item->removeEnchantment(Enchantment::get(CustomEnchantIds::REVIVE));
                $player->getArmorInventory()->setItem($slot, $item);

                $player->getEffects()->clear();
                $player->setHealth($player->getMaxHealth());
                $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                $player->getXpManager()->setXpAndProgress(0, 0.0);

                $effect = new EffectInstance(VanillaEffects::NAUSEA(), 600, 0, false);
                $player->getEffects()->add($effect);
                $effect = new EffectInstance(VanillaEffects::SLOWNESS(), 600, 0, false);
                $player->getEffects()->add($effect);

                for ($i = $player->getPosition(); $i <= 256; $i += 0.25) {
                    $player->getWorld()->addParticle($player->getPosition()->add(0, $i - $player->getPosition()->y), new FlameParticle());
                }
                $player->sendTip(TextFormat::GREEN . "You were revived.");

                foreach ($event->getModifiers() as $modifier => $damage) {
                    $event->setModifier(0, $modifier);
                }
                $event->setBaseDamage(0);
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