<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants;

use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;

/**
 * Class ToggleableEnchantment
 * @package DaPigGuy\PiggyCustomEnchants\enchants
 */
class ToggleableEnchantment extends CustomEnchant
{
    use ToggleTrait;
}