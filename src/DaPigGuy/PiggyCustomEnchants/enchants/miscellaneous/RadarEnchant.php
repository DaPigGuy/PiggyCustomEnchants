<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RadarEnchant extends TickingEnchantment
{
    use ToggleTrait;

    /** @var string */
    public $name = "Radar";

    /** @var int */
    public $usageType = CustomEnchant::TYPE_INVENTORY;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_COMPASS;

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 50];
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $detected = $this->findNearestPlayer($player, $level * $this->extraData["radiusMultiplier"]);
        if (!is_null($detected)) {
            $pk = new SetSpawnPositionPacket();
            $pk->x = (int)$detected->x;
            $pk->y = (int)$detected->y;
            $pk->z = (int)$detected->z;
            $pk->spawnForced = true;
            $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
            $player->sendDataPacket($pk);
            if ($item->equalsExact($player->getInventory()->getItemInHand())) {
                $player->sendTip(TextFormat::GREEN . "Nearest player " . round($player->distance($detected), 1) . " blocks away.");
            }
        } else {
            if ($item->equalsExact($player->getInventory()->getItemInHand())) {
                $player->sendTip(TextFormat::RED . "No players found.");
                $pk = new SetSpawnPositionPacket();
                $pk->x = (int)$player->getLevel()->getSafeSpawn()->x;
                $pk->y = (int)$player->getLevel()->getSafeSpawn()->y;
                $pk->z = (int)$player->getLevel()->getSafeSpawn()->z;
                $pk->spawnForced = true;
                $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
                $player->sendDataPacket($pk);
            }
        }
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if (!$toggle && $player->isOnline()) {
            $pk = new SetSpawnPositionPacket();
            $pk->x = (int)$player->getLevel()->getSafeSpawn()->x;
            $pk->y = (int)$player->getLevel()->getSafeSpawn()->y;
            $pk->z = (int)$player->getLevel()->getSafeSpawn()->z;
            $pk->spawnForced = true;
            $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
            $player->sendDataPacket($pk);
        }
    }

    public function findNearestPlayer(Player $player, int $range): ?Player
    {
        $nearestPlayer = null;
        $nearestPlayerDistance = $range;
        foreach ($player->getLevel()->getPlayers() as $p) {
            $distance = $player->distance($p);
            if ($distance <= $range && $distance < $nearestPlayerDistance && $player !== $p && $p->isAlive() && !$p->isClosed() && !$p->isFlaggedForDespawn()) {
                $nearestPlayer = $p;
                $nearestPlayerDistance = $distance;
            }
        }
        return $nearestPlayer;
    }
}