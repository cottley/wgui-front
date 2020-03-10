<?php
declare(strict_types=1);

namespace ParagonIE\EasyDB\Exception;

use ParagonIE\Corner\CornerInterface;
use ParagonIE\Corner\CornerTrait;

/**
 * Class InvalidTableName
 * @package ParagonIE\EasyDB\Exception
 */
class InvalidTableName extends \InvalidArgumentException implements CornerInterface
{
    use CornerTrait;
}
