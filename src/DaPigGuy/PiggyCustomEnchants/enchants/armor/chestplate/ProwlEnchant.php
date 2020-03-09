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

class ProwlEnchant extends ToggleableEnchantment
{
    use TickingTrait;

    /** @var string */
    public $name = "Prowl";
    /** @var int */
    public $maxLevel = 1;

    /** @var int */
    public $usageType = CustomEnchant::TYPE_CHESTPLATE;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_CHESTPLATE;

    /** @var array */
    public $prowled;

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
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
}