<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;
use ReflectionException;

/**
 * Class MeditationEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet
 */
class MeditationEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Meditation";
    /** @var int */
    public $maxLevel = 2;

    /** @var TaskHandler */
    private $taskHandler;

    /** @var Player[] */
    public static $meditating = [];
    /** @var array */
    public static $meditationTick;

    /**
     * CustomEnchant constructor.
     * @param Main $plugin
     * @param int $id
     * @param int $rarity
     * @throws ReflectionException
     */
    public function __construct(Main $plugin, int $id, int $rarity = self::RARITY_RARE)
    {
        parent::__construct($plugin, $id, $rarity);
        $this->taskHandler = $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
            foreach (self::$meditating as $meditating) {
                $enchantment = $meditating->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantIds::MEDITATION);
                if ($enchantment !== null) {
                    self::$meditationTick[$meditating->getName()]++;
                    $time = self::$meditationTick[$meditating->getName()] / 40;
                    $meditating->sendTip(TextFormat::DARK_GREEN . "Meditating...\n " . TextFormat::GREEN . str_repeat("▌", $time) . TextFormat::GRAY . str_repeat("▌", (20 * 20 / 40) - $time));
                    if (self::$meditationTick[$meditating->getName()] >= 20 * 20) {
                        self::$meditationTick[$meditating->getName()] = 0;
                        $event = new EntityRegainHealthEvent($meditating, $enchantment->getLevel(), EntityRegainHealthEvent::CAUSE_MAGIC);
                        if (!$event->isCancelled()) {
                            $meditating->heal($event);
                        }
                        $meditating->setFood($meditating->getFood() + $enchantment->getLevel() > 20 ? 20 : $meditating->getFood() + $enchantment->getLevel());
                    }
                } else {
                    unset(self::$meditating[$meditating->getName()]);
                    unset(self::$meditationTick[$meditating->getName()]);
                }
            }
        }), 1);
    }

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [PlayerMoveEvent::class];
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
            self::$meditating[$player->getName()] = $player;
            self::$meditationTick[$player->getName()] = 0;
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

    public function unregister(): void
    {
        $this->taskHandler->cancel();
    }
}