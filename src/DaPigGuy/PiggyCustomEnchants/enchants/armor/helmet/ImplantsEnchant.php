<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Water;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class ImplantsEnchant extends ReactiveEnchantment
{
    public string $name = "Implants";

    public int $usageType = CustomEnchant::TYPE_HELMET;
    public int $itemType = CustomEnchant::ITEM_TYPE_HELMET;

    /** @var ClosureTask[] */
    public static array $tasks;

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
            if ($player->getHungerManager()->getFood() < 20) {
                $player->getHungerManager()->setFood($player->getHungerManager()->getFood() + $level * $this->extraData["foodReplenishAmountMultiplier"] > $player->getHungerManager()->getMaxFood() ? $player->getHungerManager()->getMaxFood() : $player->getHungerManager()->getFood() + $level * $this->extraData["foodReplenishAmountMultiplier"]);
            }
            if ($player->getAirSupplyTicks() < $player->getMaxAirSupplyTicks() && !isset(self::$tasks[$player->getName()])) {
                self::$tasks[$player->getName()] = new ClosureTask(function () use ($player): void {
                    if ($player->isOnline() && $player->isAlive() && ($enchantment = $player->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantManager::getEnchantment(CustomEnchantIds::IMPLANTS))) !== null) {
                        if (!$player->getWorld()->getBlock($player->getPosition()->add(0, 1, 0)) instanceof Water ||
                            $player->getAirSupplyTicks() >= $player->getMaxAirSupplyTicks()) {
                            self::$tasks[$player->getName()]->getHandler()->cancel();
                            unset(self::$tasks[$player->getName()]);
                            return;
                        }
                        $player->setAirSupplyTicks(min($player->getAirSupplyTicks() + ($enchantment->getLevel() * $this->extraData["airTicksReplenishAmountMultiplier"]), $player->getMaxAirSupplyTicks()));
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