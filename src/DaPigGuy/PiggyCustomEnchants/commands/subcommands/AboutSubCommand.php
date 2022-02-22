<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class AboutSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchants */
    protected $plugin;

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $message = TextFormat::GREEN . "PiggyCustomEnchants version " . TextFormat::GOLD . $this->plugin->getDescription()->getVersion() . TextFormat::EOL .
            TextFormat::GREEN . "PiggyCustomEnchants is a versatile custom enchantments plugin developed by DaPigGuy (MCPEPIG) and Aericio." . TextFormat::EOL .
            "More information about our plugin can be found at " . TextFormat::GOLD . "https://piggydocs.aericio.net/" . TextFormat::GREEN . "." . TextFormat::EOL .
            TextFormat::GRAY . "Copyright 2017 DaPigGuy; Licensed under the Apache License.";
        if ($sender instanceof Player && $this->plugin->areFormsEnabled()) {
            $form = new SimpleForm(function (Player $player, ?int $data): void {
                if ($data !== null) $this->plugin->getServer()->dispatchCommand($player, "ce");
            });
            $form->setTitle(TextFormat::GREEN . "About PiggyCustomEnchants");
            $form->setContent($message);
            $form->addButton("Back");
            $sender->sendForm($form);
            return;
        }
        $sender->sendMessage($message);
    }

    public function prepare(): void
    {
        $this->setPermission("piggycustomenchants.command.ce.about");
    }
}