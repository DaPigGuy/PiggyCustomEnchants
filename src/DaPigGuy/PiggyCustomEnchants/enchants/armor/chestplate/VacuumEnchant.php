<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use pocketmine\entity\object\ItemEntity;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class VacuumEnchant extends TickingEnchantment
{
    public string $name = "Vacuum";
    public int $maxLevel = 3;

    public int $usageType = CustomEnchant::TYPE_CHESTPLATE;
    public int $itemType = CustomEnchant::ITEM_TYPE_CHESTPLATE;

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 3];
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        foreach ($player->getWorld()->getEntities() as $entity) {
            if ($entity instanceof ItemEntity) {
                $distance = $player->getPosition()->distance($entity->getPosition());
                if ($distance <= $this->extraData["radiusMultiplier"] * $level) {
                    $entity->setMotion($player->getPosition()->subtractVector($entity->getPosition())->divide(3 * $level)->multiply($level));
                }
            }
        }
    }
}