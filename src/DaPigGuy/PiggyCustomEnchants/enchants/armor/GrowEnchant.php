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
 * Class GrowEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class GrowEnchant extends ToggleableEnchantment
{
    use ReactiveTrait;
    use TickingTrait;

    /** @var string */
    public $name = "Grow";

    /** @var array */
    public $grew;
    /** @var array */
    public $growPower;

    /**
     * @return Event[]
     */
    public function getReagent(): array
    {
        return [PlayerToggleSneakEvent::class];
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
                        if (isset($this->grew[$player->getName()])) {
                            unset($this->grew[$player->getName()]);
                            $player->setScale(1);
                            $player->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
                        } else {
                            $this->grew[$player->getName()] = $player;
                            if (!isset($this->growPower[$player->getName()])) $this->growPower[$player->getName()] = 60 * 20;
                            $player->setScale($player->getScale() + 0.3 + (($this->stack[$player->getName()] / 4) * 0.05));
                            $player->sendTip(TextFormat::GREEN . "You have grown. Sneak again to shrink back to normal size.");
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
        if (isset($this->grew[$player->getName()])) {
            $this->growPower[$player->getName()]--;
            if ($this->equippedArmorStack[$player->getName()] < 4 || $this->growPower[$player->getName()] <= 0) {
                unset($this->grew[$player->getName()]);
                if ($this->growPower[$player->getName()] <= 0) {
                    $this->setCooldown($player, 75);
                    $this->growPower[$player->getName()] = 60 * 20;
                }
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
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