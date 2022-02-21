<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\player\Player;

class DrillerEnchant extends RecursiveEnchant
{
    public string $name = "Driller";
    public int $rarity = Rarity::UNCOMMON;

    public int $itemType = CustomEnchant::ITEM_TYPE_TOOLS;

    /** @var int[] */
    public static array $lastBreakFace = [];

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["distanceMultiplier" => 1];
    }

    public function safeReact(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $breakFace = self::$lastBreakFace[$player->getName()];
            for ($i = 0; $i <= $level * $this->extraData["distanceMultiplier"]; $i++) {
                $block = $event->getBlock()->getSide(Facing::opposite($breakFace), $i);
                $faceLeft = Facing::rotate($breakFace, Facing::axis($breakFace) !== Axis::Y ? Axis::Y : Axis::X, true);
                $faceUp = Facing::rotate($breakFace, Facing::axis($breakFace) !== Axis::Z ? Axis::Z : Axis::X, true);
                foreach ([
                             $block->getSide($faceLeft), //Center Left
                             $block->getSide(Facing::opposite($faceLeft)), //Center Right
                             $block->getSide($faceUp), //Center Top
                             $block->getSide(Facing::opposite($faceUp)), //Center Bottom
                             $block->getSide($faceUp)->getSide($faceLeft), //Top Left
                             $block->getSide($faceUp)->getSide(Facing::opposite($faceLeft)), //Top Right
                             $block->getSide(Facing::opposite($faceUp))->getSide($faceLeft), //Bottom Left
                             $block->getSide(Facing::opposite($faceUp))->getSide(Facing::opposite($faceLeft)) //Bottom Right
                         ] as $b) {
                    $player->getWorld()->useBreakOn($b->getPosition(), $item, $player, true);
                }
                if (!$block->getPosition()->equals($event->getBlock()->getPosition())) {
                    $player->getWorld()->useBreakOn($block->getPosition(), $item, $player, true);
                }
            }
        }
    }
}