<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\boots;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Block;
use pocketmine\block\Lava;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class MagmaWalkerEnchant extends ReactiveEnchantment
{
    public string $name = "Magma Walker";
    public int $rarity = CustomEnchant::RARITY_UNCOMMON;
    public int $maxLevel = 2;

    public int $usageType = CustomEnchant::TYPE_BOOTS;
    public int $itemType = CustomEnchant::ITEM_TYPE_BOOTS;

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
            $world = $player->getLevelNonNull();
            if (!($world->getBlock($player) instanceof Lava)) {
                $radius = $level * $this->extraData["radiusMultiplier"] + $this->extraData["baseRadius"];
                for ($x = -$radius; $x <= $radius; $x++) {
                    for ($z = -$radius; $z <= $radius; $z++) {
                        $b = $world->getBlock($player->add($x, -1, $z));
                        if ($world->getBlock($b->add(0, 1))->getId() === Block::AIR) {
                            if ($b instanceof Lava && $b->getDamage() === 0) {
                                $world->setBlock($b, Block::get(Block::OBSIDIAN, 15));
                            }
                        }
                    }
                }
            }
        }
    }
}