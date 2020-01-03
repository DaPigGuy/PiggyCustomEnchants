<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\boots;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class JetpackEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\boots
 */
class JetpackEnchant extends ReactiveEnchantment
{
    use TickingTrait;
    use ToggleTrait;

    /** @var string */
    public $name = "Jetpack";
    /** @var int */
    public $maxLevel = 3;

    /** @var Player[] */
    public $activeJetpacks = [];

    /** @var array */
    public $powerRemaining;
    /** @var array */
    public $lastActivated;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [PlayerToggleSneakEvent::class, EntityDamageEvent::class];
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
        if ($event instanceof EntityDamageEvent && $event->getCause() === EntityDamageEvent::CAUSE_FALL && $this->hasActiveJetpack($player)) $event->setCancelled();
        if ($event instanceof PlayerToggleSneakEvent) {
            if ($event->isSneaking()) {
                if ($this->hasActiveJetpack($player)) {
                    if (!$player->isOnGround() && $player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantIds::PARACHUTE) === null && !$player->getAllowFlight()) {
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

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if ($this->hasActiveJetpack($player)) {
            $player->setMotion($player->getDirectionVector()->multiply($level));
            $player->resetFallDistance();
            $player->getLevel()->addParticle(new GenericParticle($player, Particle::TYPE_CAMPFIRE_SMOKE));

            $time = ceil($this->powerRemaining[$player->getName()] / 10);
            $player->sendTip(($time > 10 ? TextFormat::GREEN : ($time > 5 ? TextFormat::YELLOW : TextFormat::RED)) . "Power: " . str_repeat("|", (int)$time));
            if ($time <= 2) $player->sendTip(TextFormat::RED . "Jetpack low on power.");
            if ($player->getServer()->getTick() % 20 === 0) {
                $this->powerRemaining[$player->getName()]--;
                if ($this->powerRemaining[$player->getName()] <= 0) {
                    $this->powerActiveJetpack($player, false);
                    return;
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
     * @param bool $toggle
     */
    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle)
    {
        if (!$toggle && $this->hasActiveJetpack($player)) $this->powerActiveJetpack($player, false);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function hasActiveJetpack(Player $player): bool
    {
        return isset($this->activeJetpacks[$player->getName()]);
    }

    /**
     * @param Player $player
     * @param bool $power
     */
    public function powerActiveJetpack(Player $player, bool $power = true): void
    {
        if ($power) {
            $this->activeJetpacks[$player->getName()] = $player;
            if (!isset($this->powerRemaining[$player->getName()])) {
                $this->powerRemaining[$player->getName()] = 300;
            } else {
                $this->powerRemaining[$player->getName()] += (time() - $this->lastActivated[$player->getName()]) / 1.5;
                if ($this->powerRemaining[$player->getName()] > 300) $this->powerRemaining[$player->getName()] = 300;
            }
        } else {
            unset($this->activeJetpacks[$player->getName()]);
            $this->lastActivated[$player->getName()] = time();
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_BOOTS;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOOTS;
    }
}