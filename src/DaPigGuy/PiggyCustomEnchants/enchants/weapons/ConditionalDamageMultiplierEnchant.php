<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use ReflectionException;

/**
 * Class ConditionalDamageMultiplierEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class ConditionalDamageMultiplierEnchant extends ReactiveEnchantment
{
    /** @var callable */
    private $condition;

    /**
     * ConditionalDamageMultiplierEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param string $name
     * @param int $rarity
     * @param callable $condition
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, callable $condition, int $rarity = self::RARITY_RARE)
    {
        $this->name = $name;
        $this->condition = $condition;
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
            if (($this->condition)($event)) {
                $event->setModifier(0.1 * $level, $this->getId());
            }
        }
    }
}