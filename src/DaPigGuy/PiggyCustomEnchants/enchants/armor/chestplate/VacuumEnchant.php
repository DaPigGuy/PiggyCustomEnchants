<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use pocketmine\entity\object\ItemEntity;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class VacuumEnchant extends TickingEnchantment
{
    /** @var string */
    public $name = "Vacuum";
    /** @var int */
    public $maxLevel = 3;
    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 3];
    }
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        foreach ($player->getLevel()->getEntities() as $entity) {
            if ($entity instanceof ItemEntity) {
                $distance = $player->distance($entity);
                if ($distance <= $this->extraData["radiusMultiplier"] * $level) {
                    $entity->setMotion($player->subtract($entity)->divide(3 * $level)->multiply($level));
                }
            }
        }
    }
    public function getUsageType(): int
    {
        return self::TYPE_CHESTPLATE;
    }
    public function getItemType(): int
    {
        return self::ITEM_TYPE_CHESTPLATE;
    }
}