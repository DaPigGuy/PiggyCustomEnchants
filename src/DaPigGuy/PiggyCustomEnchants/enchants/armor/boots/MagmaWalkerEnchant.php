<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\boots;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class MagmaWalkerEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Magma Walker";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;
    /** @var int */
    public $maxLevel = 2;

    /** @var int */
    public $usageType = CustomEnchant::TYPE_BOOTS;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOOTS;

    public function getReagent(): array
    {
        return [PlayerMoveEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["baseRadius" => 2, "radiusMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerMoveEvent) {
            if (!in_array($player->getWorld()->getBlock($player->getPosition())->getId(), [BlockLegacyIds::STILL_LAVA, BlockLegacyIds::LAVA, BlockLegacyIds::FLOWING_LAVA])) {
                $radius = $level * $this->extraData["radiusMultiplier"] + $this->extraData["baseRadius"];
                for ($x = -$radius; $x <= $radius; $x++) {
                    for ($z = -$radius; $z <= $radius; $z++) {
                        $b = $player->getWorld()->getBlock($player->getPosition()->add($x, -1, $z));
                        if (in_array($b->getId(), [BlockLegacyIds::STILL_LAVA, BlockLegacyIds::LAVA, BlockLegacyIds::FLOWING_LAVA])) {
                            if (!in_array($b->getPos()->getWorld()->getBlock($b->getPos()->add(0, 1))->getId(), [BlockLegacyIds::STILL_LAVA, BlockLegacyIds::LAVA, BlockLegacyIds::FLOWING_LAVA])) {
                                if ($b->getId() !== BlockLegacyIds::FLOWING_LAVA || $b->getMeta() === 0) {
                                    $player->getWorld()->setBlock($b->getPos(), BlockFactory::get(BlockLegacyIds::OBSIDIAN, 15));
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}