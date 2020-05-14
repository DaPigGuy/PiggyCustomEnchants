<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class LacedWeaponEnchant extends ReactiveEnchantment
{
    /** @var array */
    private $effectIds;
    /** @var array */
    private $baseDuration;
    /** @var array */
    private $baseAmplifier;
    /** @var int[] */
    private $durationMultiplier;
    /** @var int[] */
    private $amplifierMultiplier;

    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, int $rarity = self::RARITY_RARE, array $effectIds = [Effect::POISON], array $durationMultiplier = [60], array $amplifierMultiplier = [1], array $baseDuration = [0], array $baseAmplifier = [0])
    {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->effectIds = $effectIds;
        $this->durationMultiplier = $durationMultiplier;
        $this->amplifierMultiplier = $amplifierMultiplier;
        $this->baseDuration = $baseDuration;
        $this->baseAmplifier = $baseAmplifier;
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
                foreach ($this->effectIds as $key => $effectId) {
                    $entity->addEffect(new EffectInstance(Effect::getEffect($effectId), ($this->extraData["baseDuration"][$key] ?? 0) + ($this->extraData["durationMultiplier"][$key] ?? 60) * $level, ($this->extraData["baseAmplifier"][$key] ?? 0) + ($this->extraData["amplifierMultiplier"][$key] ?? 1) * $level));
                }
            }
        }
    }
}