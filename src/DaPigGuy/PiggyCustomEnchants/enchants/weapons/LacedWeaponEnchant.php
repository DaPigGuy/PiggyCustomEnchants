<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class LacedWeaponEnchant extends ReactiveEnchantment
{
    /**
     * @param Effect[]|null $effects
     * @param int[] $durationMultiplier
     * @param int[] $amplifierMultiplier
     * @param int[] $baseDuration
     * @param int[] $baseAmplifier
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, int $rarity = Rarity::RARE, private ?array $effects = null, private array $durationMultiplier = [60], private array $amplifierMultiplier = [1], private array $baseDuration = [0], private array $baseAmplifier = [0])
    {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->effects = $effects ?? [VanillaEffects::POISON()];
        parent::__construct($plugin, $id);
    }

    public function getDefaultExtraData(): array
    {
        return ["durationMultiplier" => $this->durationMultiplier, "amplifierMultiplier" => $this->amplifierMultiplier, "baseDuration" => $this->baseDuration, "baseAmplifier" => $this->baseAmplifier];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if ($entity instanceof Living) {
                foreach ($this->effects as $key => $effect) {
                    $entity->getEffects()->add(new EffectInstance($effect, ($this->extraData["baseDuration"][$key] ?? 0) + ($this->extraData["durationMultiplier"][$key] ?? 60) * $level, ($this->extraData["baseAmplifier"][$key] ?? 0) + ($this->extraData["amplifierMultiplier"][$key] ?? 1) * $level));
                }
            }
        }
    }
}