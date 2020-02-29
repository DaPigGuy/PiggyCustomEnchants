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

/**
 * Class ShrinkEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class ShrinkEnchant extends ToggleableEnchantment
{
    use ReactiveTrait;
    use TickingTrait;

    /** @var string */
    public $name = "Shrink";
    /** @var int */
    public $maxLevel = 2;

    /** @var array */
    public $shrunk;
    /** @var array */
    public $shrinkPower;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [PlayerToggleSneakEvent::class];
    }

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["cooldown" => 75, "power" => 60 * 20, "base" => 0.7, "multiplier" => 0.0125];
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
        if ($event instanceof PlayerToggleSneakEvent) {
            if ($this->equippedArmorStack[$player->getName()] === 4) {
                if ($event->isSneaking()) {
                    if ($stack - $level === 0) {
                        if (isset($this->shrunk[$player->getName()])) {
                            unset($this->shrunk[$player->getName()]);
                            $player->setScale(1);
                            $player->sendTip(TextFormat::RED . "You have grown back to normal size.");
                        } else {
                            $this->shrunk[$player->getName()] = $player;
                            if (!isset($this->shrinkPower[$player->getName()])) $this->shrinkPower[$player->getName()] = $this->extraData["power"];
                            $player->setScale($player->getScale() - $this->extraData["base"] - ($this->stack[$player->getName()] * $this->extraData["multiplier"]));
                            $player->sendTip(TextFormat::GREEN . "You have shrunk. Sneak again to grow back to normal size.");
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if (isset($this->shrunk[$player->getName()])) {
            $this->shrinkPower[$player->getName()]--;
            if ($this->equippedArmorStack[$player->getName()] < 4 || $this->shrinkPower[$player->getName()] <= 0) {
                unset($this->shrunk[$player->getName()]);
                if ($this->shrinkPower[$player->getName()] <= 0) {
                    $this->setCooldown($player, $this->extraData["cooldown"]);
                    $this->shrinkPower[$player->getName()] = $this->extraData["power"];
                }
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "You have grown back to normal size.");
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