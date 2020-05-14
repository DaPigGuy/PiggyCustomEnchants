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

class AttackerDeterrentEnchant extends ReactiveEnchantment
{
    /** @var int */
    public $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    /** @var array */
    private $effects;
    /** @var array */
    private $durationMultiplier;
    /** @var array */
    private $amplifierMultiplier;

    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, array $effects, array $durationMultiplier, array $amplifierMultiplier, int $rarity = self::RARITY_RARE)
    {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->effects = $effects;
        $this->durationMultiplier = $durationMultiplier;
        $this->amplifierMultiplier = $amplifierMultiplier;
        parent::__construct($plugin, $id);
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
                    $damager->addEffect(new EffectInstance(Effect::getEffect($effect), $this->extraData["durationMultipliers"][$key] * $level, $this->extraData["amplifierMultipliers"][$key] * $level));
                }
            }
        }
    }
}