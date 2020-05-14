<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Sign;
use pocketmine\block\tile\Tile;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\serializer\NetworkNbtSerializer;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class HallucinationEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Hallucination";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_MYTHIC;

    /** @var NetworkNbtSerializer */
    public $nbtWriter = null;

    /** @var array */
    public static $hallucinating;

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if ($entity instanceof Player && !isset(self::$hallucinating[$entity->getName()])) {
                $originalPosition = Position::fromObject($entity->getPosition()->round(), $entity->getWorld());
                self::$hallucinating[$entity->getName()] = true;
                $this->plugin->getScheduler()->scheduleRepeatingTask(($task = new ClosureTask(function () use ($entity, $originalPosition): void {
                    $packets = [];
                    for ($x = $originalPosition->x - 1; $x <= $originalPosition->x + 1; $x++) {
                        for ($y = $originalPosition->y - 1; $y <= $originalPosition->y + 2; $y++) {
                            for ($z = $originalPosition->z - 1; $z <= $originalPosition->z + 1; $z++) {
                                $position = new Position($x, $y, $z, $originalPosition->getWorld());
                                $block = VanillaBlocks::BEDROCK();
                                if ($position->equals($originalPosition)) $block = VanillaBlocks::LAVA();
                                if ($position->equals($originalPosition->add(0, 1))) {
                                    $block = BlockFactory::getInstance()->get(BlockLegacyIds::WALL_SIGN, 2);
                                    if ($this->nbtWriter === null) $this->nbtWriter = new NetworkNbtSerializer();
                                    $packets[] = BlockActorDataPacket::create((int)$position->x, (int)$position->y, (int)$position->z, new CacheableNbt(
                                        CompoundTag::create()->
                                        setString(Tile::TAG_ID, "Sign")->
                                        setInt(Tile::TAG_X, (int)$position->x)->
                                        setInt(Tile::TAG_Y, (int)$position->y)->
                                        setInt(Tile::TAG_Z, (int)$position->z)->
                                        setString(Sign::TAG_TEXT_BLOB, implode("\n", [
                                                TextFormat::RED . "You seem to be",
                                                TextFormat::RED . "hallucinating..."
                                            ])
                                        )));
                                }
                                $packets[] = UpdateBlockPacket::create((int)$position->x, (int)$position->y, (int)$position->z, $block->getRuntimeId());
                            }
                        }
                    }
                    $entity->getServer()->broadcastPackets([$entity], $packets);
                })), 1);
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($originalPosition, $entity, $task): void {
                    $task->getHandler()->cancel();
                    for ($x = -1; $x <= 1; $x++) {
                        for ($y = -1; $y <= 3; $y++) {
                            for ($z = -1; $z <= 1; $z++) {
                                $originalPosition->getWorld()->sendBlocks([$entity], [$originalPosition->round()->add($x, $y, $z)]);
                            }
                        }
                    }
                    unset(self::$hallucinating[$entity->getName()]);
                }), 20 * 60);
            }
        }
    }
}