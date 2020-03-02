<?php


namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;


use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AboutSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchants */
    private $plugin;

    public function __construct(PiggyCustomEnchants $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player && $this->plugin->areFormsEnabled()) {
            $form = new SimpleForm(function (Player $player, ?int $data): void {
                if ($data !== null) {
                    $this->plugin->getServer()->dispatchCommand($player, "ce");
                }
            });
            $form->setTitle(TextFormat::GREEN . "About PiggyCustomEnchants");
            $form->setContent(TextFormat::GREEN . "PiggyCustomEnchants v" . $this->plugin->getDescription()->getVersion() . " is a custom enchants plugin made by DaPigGuy (IGN: MCPEPIG) & Aericio. You can find it at https://github.com/DaPigGuy/PiggyCustomEnchants.");
            $form->addButton("Back");
            $sender->sendForm($form);
            return;
        }
        $sender->sendMessage(TextFormat::GREEN . "PiggyCustomEnchants v" . $this->plugin->getDescription()->getVersion() . " is a custom enchants plugin made by DaPigGuy (IGN: MCPEPIG) & Aericio. You can find it at https://github.com/DaPigGuy/PiggyCustomEnchants.");
    }

    public function prepare(): void
    {
        $this->setPermission("piggycustomenchants.command.ce.about");
    }
}