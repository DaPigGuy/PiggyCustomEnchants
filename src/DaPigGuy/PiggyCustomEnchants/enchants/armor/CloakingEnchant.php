<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class CloakingEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class CloakingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Cloaking";

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["cooldown" => 10, "durationMultiplier" => 60];
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
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), $this->extraData["durationMultiplier"] * $level, 0, false));
            $player->sendMessage(TextFormat::DARK_GRAY . "You have become invisible!");
            $this->setCooldown($player, $this->extraData["cooldown"]);
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