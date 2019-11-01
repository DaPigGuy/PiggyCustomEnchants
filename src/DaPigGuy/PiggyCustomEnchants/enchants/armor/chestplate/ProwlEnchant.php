<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class ProwlEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate
 */
class ProwlEnchant extends ToggleableEnchantment
{
    use TickingTrait;

    /** @var string */
    public $name = "Prowl";
    /** @var int */
    public $maxLevel = 1;

    /** @var array */
    public $prowled;

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
        if (!$toggle && isset($this->prowled[$player->getName()])) {
            foreach ($player->getServer()->getOnlinePlayers() as $p) {
                $p->showPlayer($player);
            }
            $player->removeEffect(Effect::SLOWNESS);
            if (!$player->hasEffect(Effect::INVISIBILITY)) {
                $player->setGenericFlag(Entity::DATA_FLAG_INVISIBLE, false);
            }
            unset($this->prowled[$player->getName()]);
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
        if ($player->isSneaking()) {
            foreach ($player->getServer()->getOnlinePlayers() as $p) {
                $p->hidePlayer($player);
            }
            $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 2147483647, 0, false);
            $player->setGenericFlag(Entity::DATA_FLAG_INVISIBLE, true);
            $player->addEffect($effect);
            $this->prowled[$player->getName()] = true;
        } else {
            if (isset($this->prowled[$player->getName()])) {
                foreach ($player->getServer()->getOnlinePlayers() as $p) {
                    $p->showPlayer($player);
                }
                $player->removeEffect(Effect::SLOWNESS);
                if (!$player->hasEffect(Effect::INVISIBILITY)) {
                    $player->setGenericFlag(Entity::DATA_FLAG_INVISIBLE, false);
                }
                unset($this->prowled[$player->getName()]);
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