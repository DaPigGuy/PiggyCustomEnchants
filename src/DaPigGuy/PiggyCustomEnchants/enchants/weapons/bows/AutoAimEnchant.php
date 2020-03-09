<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\Living;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\Player;

class AutoAimEnchant extends TickingEnchantment
{
    /** @var string */
    public $name = "Auto Aim";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_MYTHIC;
    /** @var int */
    public $maxLevel = 1;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 50];
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if ($player->isSneaking() && $player->isOnGround()) {
            $target = $this->findNearestEntity($player, $level * $this->extraData["radiusMultiplier"]);
            if ($target !== null) {
                $position = $target->subtract($player);
                $yaw = atan2($position->z, $position->x) * 180 / M_PI - 90;
                $length = (new Vector2($position->x, $position->z))->length();
                if ((int)$length !== 0) {
                    $g = 0.006;
                    $tmp = 1 - $g * ($g * ($length * $length) + 2 * $position->y);
                    $pitch = 180 / M_PI * -(atan((1 - sqrt($tmp)) / ($g * $length)));
                    $player->setRotation($yaw, $pitch);
                    $player->sendPosition($player, $yaw, $pitch, MovePlayerPacket::MODE_TELEPORT);
                }
            }
        }
    }

    public function findNearestEntity(Player $player, int $range): ?Living
    {
        $nearestEntity = null;
        $nearestEntityDistance = $range;
        foreach ($player->getLevel()->getEntities() as $entity) {
            $distance = $player->distance($entity);
            if ($entity instanceof Living && $distance <= $range && $distance < $nearestEntityDistance && $player !== $entity && $entity->isAlive() && !$entity->isClosed() && !$entity->isFlaggedForDespawn() && !AllyChecks::isAlly($player, $entity)) {
                $nearestEntity = $entity;
                $nearestEntityDistance = $distance;
            }
        }
        return $nearestEntity;
    }
}