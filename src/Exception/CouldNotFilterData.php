<?php

declare(strict_types=1);

namespace PetrKnap\FilterCommand\Exception;

use PetrKnap\Shorts\Exception\CouldNotProcessData;

/**
 * @extends CouldNotProcessData<string>
 */
abstract class CouldNotFilterData extends CouldNotProcessData implements Exception
{
}
