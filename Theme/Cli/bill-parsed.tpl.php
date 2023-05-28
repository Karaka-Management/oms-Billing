<?php declare(strict_types=1);

echo \json_encode($this->getData('bill') ?? null, \JSON_PRETTY_PRINT);
