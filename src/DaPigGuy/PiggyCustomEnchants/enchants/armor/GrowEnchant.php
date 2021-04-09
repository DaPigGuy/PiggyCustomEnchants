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
    use ReactiveTrait {
        onReaction as protected originalOnReaction;
    }
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
    /** @var array */
    public $shiftCache;

    public function getReagent(): array
    {
        return [PlayerToggleSneakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["power" => 60 * 20, "base" => 0.3, "multiplier" => 0.0125];
    }

    public function onReaction(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerToggleSneakEvent) {
            $playerName = $player->getName();
            if ($event->isSneaking()) {
                if (!isset($this->shiftCache[$playerName])) {
                    $this->originalOnReaction($player, $item, $inventory, $slot, $event, $level, $stack);
                    if (isset($this->grew[$playerName])) $this->setCooldown($player, 0);
                    $this->shiftCache[$playerName] = true;
                } else {
                    $player->sendTip(TextFormat::RED . "Grow is still in cooldown: " . $this->getCooldown($player) . "s");
                }
            } elseif (isset($this->shiftCache[$playerName])) {
                unset($this->shiftCache[$playerName]);
            }
        }
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerToggleSneakEvent) {
            $playerName = $player->getName();
            if ($this->equippedArmorStack[$playerName] === 4) {
                if ($event->isSneaking()) {
                    if ($stack - $level === 0) {
                        if (isset($this->grew[$playerName])) {
                            unset($this->grew[$playerName]);
                            $player->setScale(1);
                            $player->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
                        } else {
                            $this->grew[$playerName] = $player;
                            if (!isset($this->growPower[$playerName])) $this->growPower[$playerName] = $this->extraData["power"];
                            $player->setScale($player->getScale() + $this->extraData["base"] + ($this->stack[$playerName] * $this->extraData["multiplier"]));
                            $player->sendTip(TextFormat::GREEN . "You have grown. Sneak again to shrink back to normal size.");
                        }
                    }
                }
            }
        }
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $playerName = $player->getName();
        if (isset($this->grew[$playerName])) {
            $this->growPower[$playerName]--;
            $player->sendTip(TextFormat::GREEN . "Grow power remaining: " . $this->growPower[$playerName]);
            if ($this->equippedArmorStack[$playerName] < 4 || $this->growPower[$playerName] <= 0) {
                unset($this->grew[$playerName]);
                $this->setCooldown($player, $this->getCooldownDuration());
                if ($this->growPower[$playerName] <= 0) $this->growPower[$playerName] = $this->extraData["power"];
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
            }
        }
    }
}