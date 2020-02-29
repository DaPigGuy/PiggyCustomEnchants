<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\utils\Facing;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class DrillerEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools
 */
class DrillerEnchant extends BlockBreakingEnchant
{
    /** @var string */
    public $name = "Driller";

    /** @var array */
    public static $lastBreakFace;

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["distanceMultiplier" => 1];
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param Event $event
     * @param int $level
     * @param int $stack
     */
    public function breakBlocks(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $breakFace = self::$lastBreakFace[$player->getName()];
            for ($i = 0; $i <= $level * $this->extraData["distanceMultiplier"]; $i++) {
                $block = $event->getBlock()->getSide(Facing::opposite($breakFace), $i);
                $faceLeft = Facing::rotate($breakFace, Facing::axis($breakFace) !== Facing::AXIS_Y ? Facing::AXIS_Y : Facing::AXIS_X, true);
                $faceUp = Facing::rotate($breakFace, Facing::axis($breakFace) !== Facing::AXIS_Z ? Facing::AXIS_Z : Facing::AXIS_X, true);
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
                    $player->getLevel()->useBreakOn($b, $item, $player, true);
                }
                if (!$block->equals($event->getBlock())) {
                    $player->getLevel()->useBreakOn($block, $item, $player, true);
                }
            }
        }
    }
}