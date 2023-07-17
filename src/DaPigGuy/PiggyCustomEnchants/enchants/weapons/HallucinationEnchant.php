<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\tile\Sign;
use pocketmine\block\tile\Tile;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class HallucinationEnchant extends ReactiveEnchantment
{
    public string $name = "Hallucination";
    public int $rarity = Rarity::MYTHIC;

    public ?NetworkNbtSerializer $nbtWriter = null;

    /** @var bool[] */
    public static array $hallucinating;

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
                                if ($position->equals($originalPosition->add(0, 1, 0))) {
                                    $block = VanillaBlocks::OAK_WALL_SIGN();
                                    if ($this->nbtWriter === null) $this->nbtWriter = new NetworkNbtSerializer();
                                    $packets[] = BlockActorDataPacket::create(BlockPosition::fromVector3($position->floor()), new CacheableNbt(
                                        CompoundTag::create()->
                                        setString(Tile::TAG_ID, "Sign")->
                                        setInt(Tile::TAG_X, $position->getFloorX())->
                                        setInt(Tile::TAG_Y, $position->getFloorY())->
                                        setInt(Tile::TAG_Z, $position->getFloorZ())->
                                        setString(Sign::TAG_TEXT_BLOB, implode("\n", [
                                                TextFormat::RED . "You seem to be",
                                                TextFormat::RED . "hallucinating..."
                                            ])
                                        )));
                                }
                                $blockTranslator = TypeConverter::getInstance()->getBlockTranslator();
                                $packets[] = UpdateBlockPacket::create(BlockPosition::fromVector3($position->floor()), $blockTranslator->internalIdToNetworkId($block->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL);
                            }
                        }
                    }
                    NetworkBroadcastUtils::broadcastPackets([$entity], $packets);
                })), 1);
                $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($originalPosition, $entity, $task): void {
                    $task->getHandler()->cancel();
                    $blocks = [];
                    for ($x = -1; $x <= 1; $x++) {
                        for ($y = -1; $y <= 3; $y++) {
                            for ($z = -1; $z <= 1; $z++) {
                                $blocks[] = $originalPosition->round()->add($x, $y, $z);
                            }
                        }
                    }
                    NetworkBroadcastUtils::broadcastPackets([$entity], $entity->getWorld()->createBlockUpdatePackets($blocks));
                    unset(self::$hallucinating[$entity->getName()]);
                }), 20 * 60);
            }
        }
    }
}