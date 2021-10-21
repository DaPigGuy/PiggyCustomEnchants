<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\boots;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use DaPigGuy\PiggyCustomEnchants\particles\JetpackParticle;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class JetpackEnchant extends ReactiveEnchantment
{
    use TickingTrait;
    use ToggleTrait;

    public string $name = "Jetpack";
    public int $maxLevel = 3;

    public int $usageType = CustomEnchant::TYPE_BOOTS;
    public int $itemType = CustomEnchant::ITEM_TYPE_BOOTS;

    /** @var Player[] */
    public $activeJetpacks = [];

    /** @var array */
    public $powerRemaining;
    /** @var array */
    public $lastActivated;

    public function getReagent(): array
    {
        return [PlayerToggleSneakEvent::class, EntityDamageEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["power" => 300, "rechargeAmount" => 0.66, "enableAmount" => 25, "drainMultiplier" => 1, "sprintDrainMultiplier" => 1.25, "speedMultiplier" => 1, "sprintSpeedMultiplier" => 1.25];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageEvent && $event->getCause() === EntityDamageEvent::CAUSE_FALL && $this->hasActiveJetpack($player)) $event->cancel();
        if ($event instanceof PlayerToggleSneakEvent) {
            if ($event->isSneaking()) {
                if ($this->hasActiveJetpack($player)) {
                    if (!$player->isOnGround() && $player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantManager::getEnchantment(CustomEnchantIds::PARACHUTE)) === null && !$player->getAllowFlight()) {
                        $player->sendPopup(TextFormat::RED . "It is unsafe to disable your jetpack while in the air.");
                    } else {
                        $this->powerActiveJetpack($player, false);
                    }
                } else {
                    $this->powerActiveJetpack($player);
                }
            }
        }
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if ($this->hasActiveJetpack($player)) {
            $player->setMotion($player->getDirectionVector()->multiply($level * ($player->isSprinting() ? $this->extraData["sprintSpeedMultiplier"] : $this->extraData["speedMultiplier"])));
            $player->resetFallDistance();
            $player->getWorld()->addParticle($player->getPosition(), new JetpackParticle());
            $time = ceil($this->powerRemaining[$player->getName()] / 10);
            if ($time > 2) $player->sendTip(($time > 10 ? TextFormat::GREEN : ($time > 5 ? TextFormat::YELLOW : TextFormat::RED)) . "Power: " . str_repeat("|", (int)$time));
            $lowTime = ceil($this->powerRemaining[$player->getName()] / 5);
            if ($time <= 2 && $lowTime > 0) $player->sendTip(TextFormat::RED . "Jetpack low on power: " . str_repeat("|", (int)$lowTime));
            if ($player->getServer()->getTick() % 20 === 0) {
                $this->powerRemaining[$player->getName()] -= ($player->isSprinting() ? $this->extraData["sprintDrainMultiplier"] : $this->extraData["drainMultiplier"]);
                if ($this->powerRemaining[$player->getName()] <= 0) {
                    $player->sendTip(TextFormat::RED . "Jetpack has run out of power.");
                    $this->powerActiveJetpack($player, false);
                }
            }
        }
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if (!$toggle && $this->hasActiveJetpack($player)) $this->powerActiveJetpack($player, false);
    }

    public function hasActiveJetpack(Player $player): bool
    {
        return isset($this->activeJetpacks[$player->getName()]);
    }

    public function powerActiveJetpack(Player $player, bool $power = true): void
    {
        if ($power) {
            if (!isset($this->powerRemaining[$player->getName()])) {
                $this->powerRemaining[$player->getName()] = $this->extraData["power"];
                $this->activeJetpacks[$player->getName()] = $player;
            } else {
                $this->powerRemaining[$player->getName()] += (time() - $this->lastActivated[$player->getName()]) * $this->extraData["rechargeAmount"];
                if ($this->powerRemaining[$player->getName()] > $this->extraData["power"]) $this->powerRemaining[$player->getName()] = $this->extraData["power"];
                if ($this->powerRemaining[$player->getName()] < $this->extraData["enableAmount"]) {
                    $player->sendTip(TextFormat::RED . "Jetpack needs to charge up to " . $this->extraData["enableAmount"] . " before it can be re-enabled. (" . round(abs($this->powerRemaining[$player->getName()]), 2) . " / " . $this->extraData["power"] . ")");
                    return;
                }
                $this->activeJetpacks[$player->getName()] = $player;
            }
        } else {
            unset($this->activeJetpacks[$player->getName()]);
            $this->lastActivated[$player->getName()] = time();
        }
    }
}