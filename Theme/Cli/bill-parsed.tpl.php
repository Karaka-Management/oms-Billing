<?php

echo \json_encode($this->getData('bill') ?? null, \JSON_PRETTY_PRINT);