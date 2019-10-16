<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\boots;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\particle\GenericParticle;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;
use ReflectionException;

/**
 * Class JetpackEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\boots
 */
class JetpackEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Jetpack";
    /** @var int */
    public $maxLevel = 3;

    /** @var TaskHandler */
    private $taskHandler;

    /** @var Player[] */
    public static $activeJetpacks = [];

    /** @var array */
    public static $powerRemaining;
    /** @var array */
    public static $lastActivated;

    /**
     * JetpackEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param int $rarity
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, int $rarity = self::RARITY_RARE)
    {
        parent::__construct($plugin, $id, $rarity);
        $this->taskHandler = $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick) use ($id): void {
            foreach (self::$activeJetpacks as $activeJetpack) {
                if (!$activeJetpack->isOnline()) {
                    $this->powerActiveJetpack($activeJetpack, false);
                    continue;
                }
                $enchantment = $activeJetpack->getArmorInventory()->getBoots()->getEnchantment($id);
                if ($enchantment === null) {
                    $this->powerActiveJetpack($activeJetpack, false);
                    continue;
                }
                $activeJetpack->setMotion($activeJetpack->getDirectionVector()->multiply($enchantment->getLevel()));
                $activeJetpack->resetFallDistance();
                $activeJetpack->getLevel()->addParticle(new GenericParticle($activeJetpack, 63));

                $time = ceil(self::$powerRemaining[$activeJetpack->getName()] / 10);
                $activeJetpack->sendTip(($time > 10 ? TextFormat::GREEN : ($time > 5 ? TextFormat::YELLOW : TextFormat::RED)) . "Power: " . str_repeat("|", (int)$time));
                if ($time <= 2) $activeJetpack->sendTip(TextFormat::RED . "Jetpack low on power.");
                if ($currentTick % 20 === 0) {
                    self::$powerRemaining[$activeJetpack->getName()]--;
                    if (self::$powerRemaining[$activeJetpack->getName()] <= 0) {
                        $this->powerActiveJetpack($activeJetpack, false);
                        continue;
                    }
                }

                Utils::setShouldTakeFallDamage($activeJetpack, false);
            }
        }), 1);
    }

    /**
     * @return array
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
            if ($event->isSneaking()) {
                if ($this->hasActiveJetpack($player)) {
                    if (!$player->isOnGround()) {
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
     * @return bool
     */
    public function hasActiveJetpack(Player $player): bool
    {
        return isset(self::$activeJetpacks[$player->getName()]);
    }

    /**
     * @param Player $player
     * @param bool $power
     */
    public function powerActiveJetpack(Player $player, bool $power = true): void
    {
        if ($power) {
            self::$activeJetpacks[$player->getName()] = $player;
            if (!isset(self::$powerRemaining[$player->getName()])) {
                self::$powerRemaining[$player->getName()] = 300;
            } else {
                self::$powerRemaining[$player->getName()] += (time() - self::$lastActivated[$player->getName()]) / 1.5;
                if (self::$powerRemaining[$player->getName()] > 300) self::$powerRemaining[$player->getName()] = 300;
            }
        } else {
            unset(self::$activeJetpacks[$player->getName()]);
            self::$lastActivated[$player->getName()] = time();
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

    public function unregister(): void
    {
        $this->taskHandler->cancel();
    }
}