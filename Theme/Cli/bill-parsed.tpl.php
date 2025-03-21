<?php declare(strict_types=1);

echo \json_encode($this->data['bill'] ?? null, \JSON_PRETTY_PRINT);
