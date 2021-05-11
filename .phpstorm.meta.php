<?php namespace PHPSTORM_META;

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
