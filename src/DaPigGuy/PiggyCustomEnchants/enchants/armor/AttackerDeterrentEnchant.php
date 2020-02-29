<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use ReflectionException;

/**
 * Class AttackerDeterrentEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class AttackerDeterrentEnchant extends ReactiveEnchantment
{
    /** @var Effect[] */
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
     * @param Effect[] $effects
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
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["durationMultipliers" => $this->durationMultiplier, "amplifierMultipliers" => $this->amplifierMultiplier];
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
                    $damager->getEffects()->add(new EffectInstance($effect, $this->extraData["durationMultipliers"][$key] * $level, $this->$this->extraData["amplifierMultipliers"][$key] * $level));
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