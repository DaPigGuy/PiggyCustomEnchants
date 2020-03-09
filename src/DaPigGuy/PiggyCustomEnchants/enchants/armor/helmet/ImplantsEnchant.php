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
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

class ImplantsEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Implants";

    /** @var int */
    public $usageType = CustomEnchant::TYPE_HELMET;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_HELMET;

    /** @var ClosureTask[] */
    public static $tasks;

    public function getReagent(): array
    {
        return [PlayerMoveEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["foodReplenishAmountMultiplier" => 1, "airTicksReplenishAmountMultiplier" => 40, "airReplenishInterval" => 60];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerMoveEvent) {
            if ($player->getFood() < 20) {
                $player->setFood($player->getFood() + $level * $this->extraData["foodReplenishAmountMultiplier"] > $player->getMaxFood() ? $player->getMaxFood() : $player->getFood() + $level * $this->extraData["foodReplenishAmountMultiplier"]);
            }
            if ($player->getAirSupplyTicks() < $player->getMaxAirSupplyTicks() && !isset(self::$tasks[$player->getName()])) {
                self::$tasks[$player->getName()] = new ClosureTask(function () use ($player): void {
                    if ($player->isOnline() && $player->isAlive() && ($enchantment = $player->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantIds::IMPLANTS)) !== null) {
                        if (!$player->getLevel()->getBlock($player->add(0, 1)) instanceof Water ||
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
}