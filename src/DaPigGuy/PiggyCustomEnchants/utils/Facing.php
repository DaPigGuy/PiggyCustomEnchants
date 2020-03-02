<?php
/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/
declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\utils;

use InvalidArgumentException;
use function in_array;

class Facing
{
    public const AXIS_Y = 0;
    public const AXIS_Z = 1;
    public const AXIS_X = 2;

    public const FLAG_AXIS_POSITIVE = 1;

    /* most significant 2 bits = axis, least significant bit = is positive direction */
    public const DOWN = self::AXIS_Y << 1;
    public const UP = (self::AXIS_Y << 1) | self::FLAG_AXIS_POSITIVE;
    public const NORTH = self::AXIS_Z << 1;
    public const SOUTH = (self::AXIS_Z << 1) | self::FLAG_AXIS_POSITIVE;
    public const WEST = self::AXIS_X << 1;
    public const EAST = (self::AXIS_X << 1) | self::FLAG_AXIS_POSITIVE;

    public const ALL = [
        self::DOWN,
        self::UP,
        self::NORTH,
        self::SOUTH,
        self::WEST,
        self::EAST
    ];

    public const HORIZONTAL = [
        self::NORTH,
        self::SOUTH,
        self::WEST,
        self::EAST
    ];

    private const CLOCKWISE = [
        self::AXIS_Y => [
            self::NORTH => self::EAST,
            self::EAST => self::SOUTH,
            self::SOUTH => self::WEST,
            self::WEST => self::NORTH
        ],
        self::AXIS_Z => [
            self::UP => self::EAST,
            self::EAST => self::DOWN,
            self::DOWN => self::WEST,
            self::WEST => self::UP
        ],
        self::AXIS_X => [
            self::UP => self::NORTH,
            self::NORTH => self::DOWN,
            self::DOWN => self::SOUTH,
            self::SOUTH => self::UP
        ]
    ];

    public static function axis(int $direction): int
    {
        return $direction >> 1; //shift off positive/negative bit
    }

    public static function isPositive(int $direction): bool
    {
        return ($direction & self::FLAG_AXIS_POSITIVE) === self::FLAG_AXIS_POSITIVE;
    }

    public static function opposite(int $direction): int
    {
        return $direction ^ self::FLAG_AXIS_POSITIVE;
    }

    /**
     * @throws InvalidArgumentException if not possible to rotate $direction around $axis
     */
    public static function rotate(int $direction, int $axis, bool $clockwise): int
    {
        if (!isset(self::CLOCKWISE[$axis])) {
            throw new InvalidArgumentException("Invalid axis $axis");
        }
        if (!isset(self::CLOCKWISE[$axis][$direction])) {
            throw new InvalidArgumentException("Cannot rotate direction $direction around axis $axis");
        }
        $rotated = self::CLOCKWISE[$axis][$direction];
        return $clockwise ? $rotated : self::opposite($rotated);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function rotateY(int $direction, bool $clockwise): int
    {
        return self::rotate($direction, self::AXIS_Y, $clockwise);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function rotateZ(int $direction, bool $clockwise): int
    {
        return self::rotate($direction, self::AXIS_Z, $clockwise);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function rotateX(int $direction, bool $clockwise): int
    {
        return self::rotate($direction, self::AXIS_X, $clockwise);
    }

    /**
     * @throws InvalidArgumentException if the argument is not a valid Facing constant
     */
    public static function validate(int $facing): void
    {
        if (!in_array($facing, self::ALL, true)) {
            throw new InvalidArgumentException("Invalid direction $facing");
        }
    }
}