<?php
/*
 * This file is part of The Framework Debug Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPSTORM_META;

registerArgumentsSet(
    'eh_env',
    \Framework\Debug\ExceptionHandler::ENV_DEV,
    \Framework\Debug\ExceptionHandler::ENV_PROD,
);
expectedArguments(
    \Framework\Debug\ExceptionHandler::__construct(),
    0,
    argumentsSet('eh_env')
);
