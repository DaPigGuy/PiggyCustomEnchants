<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

class HallucinationEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Hallucination";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_MYTHIC;

    /** @var array */
    public static $hallucinating;

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if ($entity instanceof Player && !isset(self::$hallucinating[$entity->getName()])) {
                $originalPosition = $entity->getPosition();
                self::$hallucinating[$entity->getName()] = true;
                $this->plugin->getScheduler()->scheduleRepeatingTask(($task = new ClosureTask(function () use ($entity, $originalPosition): void {
                    for ($x = $originalPosition->x - 1; $x <= $originalPosition->x + 1; $x++) {
                        for ($y = $originalPosition->y - 1; $y <= $originalPosition->y + 2; $y++) {
                            for ($z = $originalPosition->z - 1; $z <= $originalPosition->z + 1; $z++) {
                                $position = new Position($x, $y, $z, $originalPosition->getLevel());
                                $block = Block::get(Block::BEDROCK, 0, $position);
                                if ($position->equals($originalPosition)) $block = Block::get(Block::LAVA, 0, $position);
                                if ($position->equals($originalPosition->add(0, 1))) {
                                    $block = Block::get(Block::WALL_SIGN, 0, $position);
                                    $nbtWriter = new NetworkLittleEndianNBTStream();
                                    /** @var string $nbt */
                                    $nbt = $nbtWriter->write(new CompoundTag("", [
                                        new StringTag("id", Tile::SIGN),
                                        new StringTag("Text1", TextFormat::RED . "You seem to be"),
                                        new StringTag("Text2", TextFormat::RED . "hallucinating..."),
                                        new StringTag("Text3", ""),
                                        new StringTag("Text4", ""),
                                        new IntTag("x", (int)$position->x),
                                        new IntTag("y", (int)$position->y),
                                        new IntTag("z", (int)$position->z)
                                    ]));
                                    $pk = new BlockActorDataPacket();
                                    $pk->x = (int)$position->x;
                                    $pk->y = (int)$position->y;
                                    $pk->z = (int)$position->z;
                                    $pk->namedtag = $nbt;
                                    $entity->sendDataPacket($pk);
                                }
                                $position->getLevel()->sendBlocks([$entity], [$block]);
                            }
                        }
                    }
                })), 1);
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($originalPosition, $entity, $task): void {
                    $task->getHandler()->cancel();
                    for ($y = -1; $y <= 3; $y++) {
                        $startBlock = $originalPosition->getLevel()->getBlock($originalPosition->add(0, $y));
                        $originalPosition->getLevel()->sendBlocks([$entity], array_merge([$startBlock], $startBlock->getHorizontalSides(), [
                            $startBlock->getSide(Vector3::SIDE_NORTH)->getSide(Vector3::SIDE_EAST),
                            $startBlock->getSide(Vector3::SIDE_NORTH)->getSide(Vector3::SIDE_WEST),
                            $startBlock->getSide(Vector3::SIDE_SOUTH)->getSide(Vector3::SIDE_EAST),
                            $startBlock->getSide(Vector3::SIDE_SOUTH)->getSide(Vector3::SIDE_WEST)
                        ]));
                    }
                    unset(self::$hallucinating[$entity->getName()]);
                }), 20 * 60);
            }
        }
    }
}