<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

/**
 * Class RadarTask
 * @package PiggyCustomEnchants
 */
class RadarTask extends Task
{
    /** @var Main */
    private $plugin;
    /** @var array */
    private $radars;

    /**
     * RadarTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $radar = false;
            foreach ($player->getInventory()->getContents() as $item) {
                $enchantment = $item->getEnchantment(CustomEnchantsIds::RADAR);
                if ($enchantment !== null) {
                    $detected = $this->plugin->findNearestEntity($player, $enchantment->getLevel() * 50, Player::class, $player);
                    if (!is_null($detected)) {
                        $pk = new SetSpawnPositionPacket();
                        $pk->x = (int)$detected->x;
                        $pk->y = (int)$detected->y;
                        $pk->z = (int)$detected->z;
                        $pk->spawnForced = true;
                        $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
                        $player->dataPacket($pk);
                        $radar = true;
                        $this->radars[$player->getLowerCaseName()] = true;
                        if ($item->equalsExact($player->getInventory()->getItemInHand())) {
                            $player->sendTip(TextFormat::GREEN . "Nearest player " . round($player->distance($detected), 1) . " blocks away.");
                        }
                        break;
                    } else {
                        if ($item->equalsExact($player->getInventory()->getItemInHand())) {
                            $player->sendTip(TextFormat::RED . "No players found.");
                        }
                    }
                }
            }
            if (!$radar) {
                if (isset($this->radars[$player->getLowerCaseName()])) {
                    $pk = new SetSpawnPositionPacket();
                    $pk->x = (int)$player->getLevel()->getSafeSpawn()->x;
                    $pk->y = (int)$player->getLevel()->getSafeSpawn()->y;
                    $pk->z = (int)$player->getLevel()->getSafeSpawn()->z;
                    $pk->spawnForced = true;
                    $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
                    $player->dataPacket($pk);
                    unset($this->radars[$player->getLowerCaseName()]);
                }
            }
        }
    }
}