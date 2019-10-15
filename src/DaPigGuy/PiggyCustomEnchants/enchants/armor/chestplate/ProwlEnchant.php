<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

/**
 * Class ProwlEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate
 */
class ProwlEnchant extends ToggleableEnchantment
{
    /** @var string */
    public $name = "Prowl";
    /** @var int */
    public $maxLevel = 1;

    /** @var ClosureTask[] */
    public $tasks;

    /** @var array */
    public static $prowled;

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
        if ($toggle) {
            $this->tasks[$player->getName()] = new ClosureTask(function (int $currentTick) use ($player): void {
                if ($player->isSneaking()) {
                    foreach ($player->getServer()->getOnlinePlayers() as $p) {
                        $p->hidePlayer($player);
                    }
                    $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 2147483647, 0, false);
                    $player->setGenericFlag(Entity::DATA_FLAG_INVISIBLE, true);
                    $player->addEffect($effect);
                    self::$prowled[$player->getName()] = true;
                } else {
                    if (isset(self::$prowled[$player->getName()])) {
                        foreach ($player->getServer()->getOnlinePlayers() as $p) {
                            $p->showPlayer($player);
                        }
                        $player->removeEffect(Effect::SLOWNESS);
                        if (!$player->hasEffect(Effect::INVISIBILITY)) {
                            $player->setGenericFlag(Entity::DATA_FLAG_INVISIBLE, false);
                        }
                        unset(self::$prowled[$player->getName()]);
                    }
                }
            });
            CustomEnchantManager::getPlugin()->getScheduler()->scheduleRepeatingTask($this->tasks[$player->getName()], 1);
        } else {
            foreach ($player->getServer()->getOnlinePlayers() as $p) {
                $p->showPlayer($player);
            }
            $player->removeEffect(Effect::SLOWNESS);
            if (!$player->hasEffect(Effect::INVISIBILITY)) {
                $player->setGenericFlag(Entity::DATA_FLAG_INVISIBLE, false);
            }
            if (isset($this->tasks[$player->getName()])) {
                $this->tasks[$player->getName()]->getHandler()->cancel();
                unset($this->tasks[$player->getName()]);
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_CHESTPLATE;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_CHESTPLATE;
    }
}