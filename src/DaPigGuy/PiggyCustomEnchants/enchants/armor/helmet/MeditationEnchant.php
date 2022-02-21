<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class MeditationEnchant extends ReactiveEnchantment
{
    use TickingTrait;

    public string $name = "Meditation";
    public int $rarity = Rarity::UNCOMMON;
    public int $maxLevel = 2;

    public int $usageType = CustomEnchant::TYPE_HELMET;
    public int $itemType = CustomEnchant::ITEM_TYPE_HELMET;

    /** @var Player[] */
    public array $meditating = [];
    /** @var int[] */
    public array $meditationTick;

    public function getReagent(): array
    {
        return [PlayerMoveEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["duration" => 20 * 20, "healthReplenishAmountMultiplier" => 1, "foodReplenishAmountMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerMoveEvent) {
            $this->meditating[$player->getName()] = $player;
            $this->meditationTick[$player->getName()] = 0;
        }
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if (isset($this->meditationTick[$player->getName()])) {
            $this->meditationTick[$player->getName()]++;
            $time = (int)($this->meditationTick[$player->getName()] / 40);
            $player->sendTip(TextFormat::DARK_GREEN . "Meditating...\n" . TextFormat::GREEN . str_repeat("▌", $time) . TextFormat::GRAY . str_repeat("▌", (20 * 20 / 40) - $time));
            if ($this->meditationTick[$player->getName()] >= $this->extraData["duration"]) {
                $this->meditationTick[$player->getName()] = 0;
                $event = new EntityRegainHealthEvent($player, $level * $this->extraData["healthReplenishAmountMultiplier"], EntityRegainHealthEvent::CAUSE_MAGIC);
                if (!$event->isCancelled()) $player->heal($event);
                $player->getHungerManager()->setFood($player->getHungerManager()->getFood() + $level * $this->extraData["foodReplenishAmountMultiplier"] > $player->getHungerManager()->getMaxFood() ? $player->getHungerManager()->getMaxFood() : $player->getHungerManager()->getFood() + $level * $this->extraData["foodReplenishAmountMultiplier"]);
            }
        }
    }
}