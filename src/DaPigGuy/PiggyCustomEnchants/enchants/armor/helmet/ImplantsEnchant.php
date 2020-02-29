<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Water;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

/**
 * Class ImplantsEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet
 */
class ImplantsEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Implants";

    /** @var ClosureTask[] */
    public static $tasks;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [PlayerMoveEvent::class];
    }

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["foodReplenishAmountMultiplier" => 1, "airTicksReplenishAmountMultiplier" => 40, "airReplenishInterval" => 60];
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
        if ($event instanceof PlayerMoveEvent) {
            if ($player->getHungerManager()->getFood() < 20) {
                $player->getHungerManager()->setFood($player->getHungerManager()->getFood() + $level * $this->extraData["foodReplenishAmountMultiplier"] > $player->getHungerManager()->getMaxFood() ? $player->getHungerManager()->getMaxFood() : $player->getHungerManager()->getFood() + $level * $this->extraData["foodReplenishAmountMultiplier"]);
            }
            if ($player->getAirSupplyTicks() < $player->getMaxAirSupplyTicks() && !isset(self::$tasks[$player->getName()])) {
                self::$tasks[$player->getName()] = new ClosureTask(function () use ($player): void {
                    if ($player->isOnline() && $player->isAlive() && ($enchantment = $player->getArmorInventory()->getHelmet()->getEnchantment(Enchantment::get(CustomEnchantIds::IMPLANTS))) !== null) {
                        if (!$player->getWorld()->getBlock($player->getNextPosition()->add(0, 1)) instanceof Water ||
                            $player->getAirSupplyTicks() >= $player->getMaxAirSupplyTicks()) {
                            self::$tasks[$player->getName()]->getHandler()->cancel();
                            unset(self::$tasks[$player->getName()]);
                            return;
                        }
                        $player->setAirSupplyTicks($player->getAirSupplyTicks() + ($enchantment->getLevel() * $this->extraData["airTicksReplenishAmountMultiplier"]) > $player->getMaxAirSupplyTicks() ? $player->getMaxAirSupplyTicks() : $player->getAirSupplyTicks() + ($enchantment->getLevel() * $this->extraData["airTicksReplenishAmountMultiplier"]));
                    } else {
                        self::$tasks[$player->getName()]->getHandler()->cancel();
                        unset(self::$tasks[$player->getName()]);
                    }
                });
                $this->plugin->getScheduler()->scheduleDelayedRepeatingTask(self::$tasks[$player->getName()], 20, $this->extraData["airReplenishInterval"]);
            }
            $this->setCooldown($player, 1);
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_HELMET;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_HELMET;
    }
}