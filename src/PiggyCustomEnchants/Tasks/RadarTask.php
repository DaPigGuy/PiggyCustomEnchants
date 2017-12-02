<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

/**
 * Class RadarTask
 * @package PiggyCustomEnchants
 */
class RadarTask extends PluginTask
{
    private $plugin;
    private $radars;

    /**
     * RadarTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    /**
     * @param $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $radar = false;
            $hasradar = false;
            foreach ($player->getInventory()->getContents() as $item) {
                $enchantment = $this->plugin->getEnchantment($item, CustomEnchants::RADAR);
                if ($enchantment !== null) {
                    $distance = [];
                    $hasradar = true;
                    foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
                        if ($player !== $p) {
                            $d = $player->distance($p);
                            if ($d <= $enchantment->getLevel() * 50) {
                                $distance[$p->getLowerCaseName()] = $d;
                            }
                        }
                    }
                    if (count($distance) > 0) {
                        $minimum = min($distance);
                        $key = array_search($minimum, $distance);
                        if ($key !== false) {
                            $detected = $this->plugin->getServer()->getPlayerExact($key);
                            if ($detected instanceof Player) {
                                $pk = new SetSpawnPositionPacket();
                                $pk->x = (int) $detected->x;
                                $pk->y = (int) $detected->y;
                                $pk->z = (int) $detected->z;
                                $pk->spawnForced = true;
                                $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
                                $player->dataPacket($pk);
                                $radar = true;
                                $this->radars[$player->getLowerCaseName()] = true;
                                $player->sendTip(TextFormat::GREEN . "Nearest player " . $minimum . " blocks away.");
                                break;
                            }
                        }
                    }
                }
            }
            if (!$radar) {
                if (isset($this->radars[$player->getLowerCaseName()])) {
                    $pk = new SetSpawnPositionPacket();
                    $pk->x = (int) $player->x;
                    $pk->y = (int) $player->y;
                    $pk->z = (int) $player->z;
                    $pk->spawnForced = true;
                    $pk->spawnType = SetSpawnPositionPacket::TYPE_WORLD_SPAWN;
                    $player->dataPacket($pk);
                    unset($this->radars[$player->getLowerCaseName()]);
                }
                if ($hasradar) {
                    $player->sendTip(TextFormat::RED . "No players found.");
                }
            }
        }
    }
}