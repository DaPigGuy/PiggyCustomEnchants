<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use ReflectionException;

/**
 * Class LacedWeaponEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class LacedWeaponEnchant extends ReactiveEnchantment
{
    /** @var array */
    private $effects;
    /** @var array */
    private $baseDuration;
    /** @var array */
    private $baseAmplifier;
    /** @var int[] */
    private $durationMultiplier;
    /** @var int[] */
    private $amplifierMultiplier;

    /**
     * LacedWeaponEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param string $name
     * @param int $rarity
     * @param array|null $effects
     * @param array $durationMultiplier
     * @param array $amplifierMultiplier
     * @param array $baseDuration
     * @param array $baseAmplifier
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, int $rarity = self::RARITY_RARE, ?array $effects = null, array $durationMultiplier = [60], array $amplifierMultiplier = [1], array $baseDuration = [0], array $baseAmplifier = [0])
    {
        $this->name = $name;
        $this->effects = $effects ?? [VanillaEffects::POISON()];
        $this->durationMultiplier = $durationMultiplier;
        $this->amplifierMultiplier = $amplifierMultiplier;
        $this->baseDuration = $baseDuration;
        $this->baseAmplifier = $baseAmplifier;
        parent::__construct($plugin, $id, $rarity);
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
        if ($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if ($entity instanceof Living) {
                foreach ($this->effects as $key => $effect) {
                    $entity->getEffects()->add(new EffectInstance($effect, ($this->baseDuration[$key] ?? 0) + ($this->durationMultiplier[$key] ?? 60) * $level, ($this->baseAmplifier[$key] ?? 0) + ($this->amplifierMultiplier[$key] ?? 1) * $level));
                }
            }
        }
    }
}