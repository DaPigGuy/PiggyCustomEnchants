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

class AttackerDeterrentEnchant extends ReactiveEnchantment
{
    /** @var Effect[] */
    private $effects;
    /** @var array */
    private $durationMultiplier;
    /** @var array */
    private $amplifierMultiplier;

    /**
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

    public function getDefaultExtraData(): array
    {
        return ["durationMultipliers" => $this->durationMultiplier, "amplifierMultipliers" => $this->amplifierMultiplier];
    }

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

    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }
}