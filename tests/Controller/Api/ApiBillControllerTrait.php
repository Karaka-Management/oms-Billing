<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Controller\Api;

use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Utils\RnG\DateTime;

trait ApiBillControllerTrait
{
    public function testBillCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;

        $request->setData('client', 1);
        $request->setData('address', null);
        $request->setData('type', 1);
        $request->setData('status', null); // null = system settings, value = individual input
        $request->setData('performancedate', DateTime::generateDateTime(new \DateTime('2020-01-01'), new \DateTime('now'))->format('Y-m-d H:i:s'));
        $request->setData('sales_referral', null); // who these sales belong to
        $request->setData('shipping_terms', 1); // e.g. incoterms
        $request->setData('shipping_type', 1);
        $request->setData('shipping_cost', null);
        $request->setData('insurance_type', 1);
        $request->setData('insurance_cost', null); // null = system settings, value = individual input
        $request->setData('info', null); // null = system settings, value = individual input
        $request->setData('currency', null); // null = system settings, value = individual input
        $request->setData('payment', null); // null = system settings, value = individual input
        $request->setData('payment_terms', null); // null = system settings, value = individual input

        $this->module->apiBillCreate($request, $response);
        self::assertEquals('ok', $response->getData('')['status']);
        self::assertGreaterThan(0, $response->getDataArray('')['response']->id);
    }

    public function testBillElementCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;

        $request->setData('bill', 1);
        $request->setData('item', 1);
        $request->setData('text', null);
        $request->setData('quantity', 5);
        $request->setData('tax', null);

        //$request->setData('discount_percentage', \mt_rand(5, 30));

        $this->module->apiBillElementCreate($request, $response);
        self::assertEquals('ok', $response->getData('')['status']);
        self::assertGreaterThan(0, $response->getDataArray('')['response']->id);
    }

    public function testBillArchiveCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;

        $request->setData('bill', 1);

        $this->module->apiBillPdfArchiveCreate($request, $response);
        self::assertEquals('ok', $response->getData('')['status']);
        self::assertGreaterThan(0, $response->getDataArray('')['response']->id);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testBillCreate')]
    public function testBillNoteCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = \mt_rand(2, 5);

        $request->setData('id', 1);
        $request->setData('title', 'Test note title');
        $request->setData('plain', 'Test content text');

        $this->module->apiNoteCreate($request, $response);
        self::assertEquals('ok', $response->getDataArray('')['status']);
        self::assertGreaterThan(0, $response->getDataArray('')['response']->id);
    }

    /**
     * @covers \Modules\Billing\Controller\ApiController
     */
    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testBillCreateInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiBillCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    /**
     * @covers \Modules\Billing\Controller\ApiController
     */
    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testBillElementCreateInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiBillElementCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }
}
