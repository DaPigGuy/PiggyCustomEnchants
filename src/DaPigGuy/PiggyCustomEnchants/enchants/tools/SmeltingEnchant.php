<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use ReflectionException;

/**
 * Class SmeltingEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools
 */
class SmeltingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Smelting";
    /** @var int */
    public $maxLevel = 1;

    /** @var array */
    public $inputTable;
    /** @var array */
    public $outputTable;

    /**
     * SmeltingEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param int $rarity
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, int $rarity = self::RARITY_RARE)
    {
        parent::__construct($plugin, $id, $rarity);
        foreach ($plugin->getServer()->getCraftingManager()->getFurnaceRecipes() as $furnaceRecipe) {
            $this->inputTable[] = $furnaceRecipe->getInput();
            $this->outputTable[] = $furnaceRecipe->getResult();
        }
    }

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
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
    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $event->setDrops(array_map(function (Item $item) {
                $clonedItem = clone $item;
                if (($key = array_search($clonedItem, $this->inputTable)) || ($key = array_search($clonedItem->setDamage(-1), $this->inputTable))) {
                    return $this->outputTable[$key];
                }
                return $item;
            }, $event->getDrops()));
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_TOOLS;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 2;
    }
}