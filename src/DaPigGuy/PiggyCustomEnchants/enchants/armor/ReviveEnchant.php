<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\FlameParticle;

class ReviveEnchant extends ReactiveEnchantment
{
    public string $name = "Revive";

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getReagent(): array
    {
        return [EntityDamageEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["nauseaDuration" => 600, "slownessDuration" => 600];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageEvent) {
            if ($event->getFinalDamage() >= $player->getHealth()) {
                $level > 1 ? $item->addEnchantment(new EnchantmentInstance(CustomEnchantManager::getEnchantment(CustomEnchantIds::REVIVE), $level - 1)) : $item->removeEnchantment(CustomEnchantManager::getEnchantment(CustomEnchantIds::REVIVE));
                $player->getArmorInventory()->setItem($slot, $item);

                $player->getEffects()->clear();
                $player->setHealth($player->getMaxHealth());
                $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                $player->getXpManager()->setXpAndProgress(0, 0.0);

                $effect = new EffectInstance(VanillaEffects::NAUSEA(), $this->extraData["nauseaDuration"], 0, false);
                $player->getEffects()->add($effect);
                $effect = new EffectInstance(VanillaEffects::SLOWNESS(), $this->extraData["slownessDuration"], 0, false);
                $player->getEffects()->add($effect);

                for ($i = $player->getPosition()->y; $i <= 256; $i += 0.25) {
                    $player->getWorld()->addParticle($player->getPosition()->add(0, $i - $player->getPosition()->y, 0), new FlameParticle());
                }
                $player->sendTip(TextFormat::GREEN . "You were revived.");

                foreach ($event->getModifiers() as $modifier => $damage) {
                    $event->setModifier(0, $modifier);
                }
                $event->setBaseDamage(0);
            }
        }
    }
}