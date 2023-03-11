<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models\Tax
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Tax;

/**
 * Null model
 *
 * @package Modules\Billing\Models\Tax
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class NullTaxCombination extends TaxCombination
{
    /**
     * Constructor
     *
     * @param int $id Model id
     *
     * @since 1.0.0
     */
    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return ['id' => $this->id];
    }
}
