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
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ShrinkEnchant extends ToggleableEnchantment
{
    use ReactiveTrait {
        onReaction as protected originalOnReaction;
    }
    use TickingTrait;

    public string $name = "Shrink";
    public int $rarity = Rarity::UNCOMMON;
    public int $maxLevel = 2;
    public int $cooldownDuration = 75;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    /** @var Player[] */
    public array $shrunk;
    /** @var int[] */
    public array $shrinkPower;
    /** @var bool[] */
    public array $shiftCache;

    /**
     * @return class-string[]
     */
    public function getReagent(): array
    {
        return [PlayerToggleSneakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["power" => 60 * 20, "base" => 0.7, "multiplier" => 0.0125];
    }

    public function onReaction(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerToggleSneakEvent) {
            $playerName = $player->getName();
            if ($event->isSneaking()) {
                if (!isset($this->shiftCache[$playerName])) {
                    $this->originalOnReaction($player, $item, $inventory, $slot, $event, $level, $stack);
                    if (isset($this->shrunk[$playerName])) $this->setCooldown($player, 0);
                    $this->shiftCache[$playerName] = true;
                } else {
                    $player->sendTip(TextFormat::RED . "Shrink is still in cooldown: " . $this->getCooldown($player) . "s");
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
            if ($this->getArmorStack($player) === 4) {
                if ($event->isSneaking()) {
                    if ($stack - $level === 0) {
                        if (isset($this->shrunk[$playerName])) {
                            unset($this->shrunk[$playerName]);
                            $player->setScale(1);
                            $player->sendTip(TextFormat::RED . "You have grown back to normal size.");
                        } else {
                            $this->shrunk[$playerName] = $player;
                            if (!isset($this->shrinkPower[$playerName])) $this->shrinkPower[$playerName] = $this->extraData["power"];
                            $player->setScale($player->getScale() - $this->extraData["base"] - ($this->getStack($player) * $this->extraData["multiplier"]));
                            $player->sendTip(TextFormat::GREEN . "You have shrunk. Sneak again to grow back to normal size.");
                        }
                    }
                }
            }
        }
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $playerName = $player->getName();
        if (isset($this->shrunk[$playerName])) {
            $this->shrinkPower[$playerName]--;
            $player->sendTip(TextFormat::GREEN . "Shrink power remaining: " . $this->shrinkPower[$playerName]);
            if ($this->getArmorStack($player) < 4 || $this->shrinkPower[$playerName] <= 0) {
                unset($this->shrunk[$playerName]);
                $this->setCooldown($player, $this->getCooldownDuration());
                if ($this->shrinkPower[$playerName] <= 0) $this->shrinkPower[$playerName] = $this->extraData["power"];
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "You have grown back to normal size.");
            }
        }
    }
}