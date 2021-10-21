<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CloakingEnchant extends ReactiveEnchantment
{
    public string $name = "Cloaking";
    public int $rarity = Rarity::UNCOMMON;
    public int $cooldownDuration = 10;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getDefaultExtraData(): array
    {
        return ["durationMultiplier" => 60];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), $this->extraData["durationMultiplier"] * $level, 0, false));
            $player->sendMessage(TextFormat::DARK_GRAY . "You have become invisible!");
        }
    }
}