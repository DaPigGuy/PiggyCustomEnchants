<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ReactiveTrait;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class GrowEnchant extends ToggleableEnchantment
{
    use ReactiveTrait;
    use TickingTrait;

    /** @var string */
    public $name = "Grow";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;
    /** @var int */
    public $cooldownDuration = 75;

    /** @var int */
    public $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    /** @var array */
    public $grew;
    /** @var array */
    public $growPower;

    public function getReagent(): array
    {
        return [PlayerToggleSneakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["power" => 60 * 20, "base" => 0.3, "multiplier" => 0.0125];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerToggleSneakEvent) {
            if ($this->equippedArmorStack[$player->getName()] === 4) {
                if ($event->isSneaking()) {
                    if ($stack - $level === 0) {
                        if (isset($this->grew[$player->getName()])) {
                            unset($this->grew[$player->getName()]);
                            $player->setScale(1);
                            $player->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
                        } else {
                            $this->grew[$player->getName()] = $player;
                            if (!isset($this->growPower[$player->getName()])) $this->growPower[$player->getName()] = $this->extraData["power"];
                            $player->setScale($player->getScale() + $this->extraData["base"] + ($this->stack[$player->getName()] * $this->extraData["multiplier"]));
                            $player->sendTip(TextFormat::GREEN . "You have grown. Sneak again to shrink back to normal size.");
                        }
                    }
                }
            }
        }
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if (isset($this->grew[$player->getName()])) {
            $this->growPower[$player->getName()]--;
            if ($this->equippedArmorStack[$player->getName()] < 4 || $this->growPower[$player->getName()] <= 0) {
                unset($this->grew[$player->getName()]);
                if ($this->growPower[$player->getName()] <= 0) {
                    $this->growPower[$player->getName()] = $this->extraData["power"];
                }
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
            }
        }
    }
}