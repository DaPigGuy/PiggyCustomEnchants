<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

/**
 * Class RadarEnchant
 */
class RadarEnchant extends TickingEnchantment
{
    use ToggleTrait;

    /** @var string */
    public $name = "Radar";

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 50];
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
        $detected = $this->findNearestPlayer($player, $level * $this->extraData["radiusMultiplier"]);
        if (!is_null($detected)) {
            $pk = new SetSpawnPositionPacket();
            $pk->x = (int)$detected->getPosition()->x;
            $pk->y = (int)$detected->getPosition()->y;
            $pk->z = (int)$detected->getPosition()->z;
            $pk->spawnForced = true;
            $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
            $player->getNetworkSession()->sendDataPacket($pk);
            if ($item->equalsExact($player->getInventory()->getItemInHand())) {
                $player->sendTip(TextFormat::GREEN . "Nearest player " . round($player->getPosition()->distance($detected->getPosition()), 1) . " blocks away.");
            }
        } else {
            if ($item->equalsExact($player->getInventory()->getItemInHand())) {
                $player->sendTip(TextFormat::RED . "No players found.");
                $pk = new SetSpawnPositionPacket();
                $pk->x = (int)$player->getWorld()->getSafeSpawn()->x;
                $pk->y = (int)$player->getWorld()->getSafeSpawn()->y;
                $pk->z = (int)$player->getWorld()->getSafeSpawn()->z;
                $pk->spawnForced = true;
                $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
                $player->getNetworkSession()->sendDataPacket($pk);
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
    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if (!$toggle && $player->isOnline()) {
            $pk = new SetSpawnPositionPacket();
            $pk->x = (int)$player->getWorld()->getSafeSpawn()->x;
            $pk->y = (int)$player->getWorld()->getSafeSpawn()->y;
            $pk->z = (int)$player->getWorld()->getSafeSpawn()->z;
            $pk->spawnForced = true;
            $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
            $player->getNetworkSession()->sendDataPacket($pk);
        }
    }

    /**
     * @param Player $player
     * @param int $range
     * @return Player|null
     */
    public function findNearestPlayer(Player $player, int $range): ?Player
    {
        $nearestPlayer = null;
        $nearestPlayerDistance = $range;
        foreach ($player->getWorld()->getPlayers() as $p) {
            $distance = $player->getPosition()->distance($p->getPosition());
            if ($distance <= $range && $distance < $nearestPlayerDistance && $player !== $p && $p->isAlive() && !$p->isClosed() && !$p->isFlaggedForDespawn()) {
                $nearestPlayer = $p;
                $nearestPlayerDistance = $distance;
            }
        }
        return $nearestPlayer;
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return self::TYPE_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return self::ITEM_TYPE_COMPASS;
    }
}