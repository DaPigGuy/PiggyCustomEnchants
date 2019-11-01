<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
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
 * Class AttackerDeterrentEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class AttackerDeterrentEnchant extends ReactiveEnchantment
{
    /** @var array */
    private $effects;
    /** @var array */
    private $durationMultiplier;
    /** @var array */
    private $amplifierMultiplier;

    /**
     * CustomEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param string $name
     * @param array $effects
     * @param array $durationMultiplier
     * @param array $amplifierMultiplier
     * @param int $rarity
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, array $effects, array $durationMultiplier, array $amplifierMultiplier, int $rarity = self::RARITY_RARE)
    {
        $this->name = $name;
        $this->effects = $effects;
        $this->durationMultiplier = $durationMultiplier;
        $this->amplifierMultiplier = $amplifierMultiplier;
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
            $damager = $event->getDamager();
            if ($damager instanceof Living) {
                foreach ($this->effects as $key => $effect) {
                    $damager->addEffect(new EffectInstance(Effect::getEffect($effect), $this->durationMultiplier[$key] * $level, $this->amplifierMultiplier[$key] * $level));
                }
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