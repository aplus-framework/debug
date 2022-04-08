<?php
/*
 * This file is part of Aplus Framework Debug Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPSTORM_META;

registerArgumentsSet(
    'eh_env',
    \Framework\Debug\ExceptionHandler::DEVELOPMENT,
    \Framework\Debug\ExceptionHandler::PRODUCTION,
);
expectedArguments(
    \Framework\Debug\ExceptionHandler::__construct(),
    0,
    argumentsSet('eh_env')
);
expectedArguments(
    \Framework\Debug\ExceptionHandler::setEnvironment(),
    0,
    argumentsSet('eh_env')
);
expectedReturnValues(
    \Framework\Debug\ExceptionHandler::getEnvironment(),
    argumentsSet('eh_env')
);
