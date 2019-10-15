<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\particle\FlameParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

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
                $level > 1 ? $item->addEnchantment($item->getEnchantment(CustomEnchantIds::REVIVE)->setLevel($level - 1)) : $item->removeEnchantment(CustomEnchantIds::REVIVE);
                if (count($item->getEnchantments()) === 0) $item->removeNamedTagEntry(Item::TAG_ENCH);
                $player->getArmorInventory()->setItem($slot, $item);

                $player->removeAllEffects();
                $player->setHealth($player->getMaxHealth());
                $player->setFood($player->getMaxFood());
                $player->setXpLevel(0);
                $player->setXpProgress(0);

                $effect = new EffectInstance(Effect::getEffect(Effect::NAUSEA), 600, 0, false);
                $player->addEffect($effect);
                $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 600, 0, false);
                $player->addEffect($effect);

                for ($i = $player->y; $i <= 256; $i += 0.25) {
                    $player->getLevel()->addParticle(new FlameParticle(new Vector3($player->x, $i, $player->z)));
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