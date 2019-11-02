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
use ReflectionException;

/**
 * Class LacedWeaponEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class LacedWeaponEnchant extends ReactiveEnchantment
{
    /** @var array */
    private $effectIds = [Effect::POISON];
    /** @var array */
    private $baseDuration = [0];
    /** @var array */
    private $baseAmplifier = [0];
    /** @var int[] */
    private $durationMultiplier = [60];
    /** @var int[] */
    private $amplifierMultiplier = [1];

    /**
     * LacedWeaponEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param string $name
     * @param int $rarity
     * @param array $effectIds
     * @param array $durationMultiplier
     * @param array $amplifierMultiplier
     * @param array $baseDuration
     * @param array $baseAmplifier
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, int $rarity = self::RARITY_RARE, array $effectIds = [Effect::POISON], array $durationMultiplier = [60], array $amplifierMultiplier = [1], array $baseDuration = [0], array $baseAmplifier = [0])
    {
        $this->name = $name;
        $this->effectIds = $effectIds;
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
                foreach ($this->effectIds as $key => $effectId) {
                    $entity->addEffect(new EffectInstance(Effect::getEffect($effectId), ($this->baseDuration[$key] ?? 0) + ($this->durationMultiplier[$key] ?? 60) * $level, ($this->baseAmplifier[$key] ?? 0) + ($this->amplifierMultiplier[$key] ?? 1) * $level));
                }
            }
        }
    }
}