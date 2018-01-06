<?php

namespace PiggyCustomEnchants\Tasks;


use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

/**
 * Class UseEnchantedBookTask
 * @package PiggyCustomEnchants\Tasks
 */
class UseEnchantedBookTask extends PluginTask
{
    private $plugin;
    private $player;
    private $action;

    /**
     * UseEnchantedBookTask constructor.
     * @param Main $plugin
     * @param Player $player
     * @param SlotChangeAction $action
     */
    public function __construct(Main $plugin, Player $player, SlotChangeAction $action)
    {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->action = $action;
        parent::__construct($plugin);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        $source = $this->action->getSourceItem();
        $target = $this->action->getTargetItem();
        foreach ($source->getEnchantments() as $enchant) {
            if (!$enchant->getType() instanceof CustomEnchants || $this->plugin->canBeEnchanted($target, $enchant, $enchant->getLevel())) {//TODO: Check XP
                $item = clone $target;
                if($enchant->getType() instanceof CustomEnchants){
                    $item = $this->plugin->addEnchantment($item, $enchant->getId(), $enchant->getLevel());
                }else{
                    $item->addEnchantment($enchant);
                }
                if ($this->player->getCursorInventory()->contains($target)) { //W10 UI
                    $this->player->getCursorInventory()->removeItem($target);
                    $this->player->getCursorInventory()->addItem($item);
                } else {
                    $this->player->getInventory()->setItem($this->action->getSlot(), $item);
                }
                if ($this->player->getCursorInventory()->contains($source)) { //W10 UI
                    $this->player->getCursorInventory()->removeItem($source);
                } else {
                    $this->player->getInventory()->removeItem($source);
                }
                $target = $item;
                $this->player->sendTip(TextFormat::GREEN . "Enchanting succeeded.");
            } else {
                $this->player->sendTip(TextFormat::RED . "The item is not compatible with this enchant.");
            }
        }
    }
}