<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants;

use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;

/**
 * Class TickingEnchantment
 * @package DaPigGuy\PiggyCustomEnchants\enchants
 */
class TickingEnchantment extends CustomEnchant
{
    use TickingTrait;
}