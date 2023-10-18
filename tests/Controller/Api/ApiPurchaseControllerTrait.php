<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Controller\Api;

use phpOMS\Account\AccountStatus;
use phpOMS\Account\AccountType;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\System\File\Local\Directory;
use phpOMS\Uri\HttpUri;
use phpOMS\Utils\RnG\DateTime;
use phpOMS\Utils\TestUtils;

trait ApiPurchaseControllerTrait
{
    public function testPurchaseBillUpload() : void
    {
        if (!\is_dir(__DIR__ . '/temp')) {
            \mkdir(__DIR__ . '/temp');
        }

        $tmpInvoices = \scandir(__DIR__ . '/billing');
        $invoiceDocs = [];
        foreach ($tmpInvoices as $invoice) {
            if ($invoice !== '..' && $invoice !== '.') {
                $invoiceDocs[] = $invoice;
            }
        }

        $count = \count($invoiceDocs);

        for ($i = 0; $i < $count; ++$i) {
            $toUpload = [];
            $file = $invoiceDocs[$i];

            $response = new HttpResponse();
            $request  = new HttpRequest(new HttpUri(''));

            $request->header->account = 1;

            \copy(__DIR__ . '/billing/' . $file, __DIR__ . '/temp/' . $file);

            $toUpload['file0'] = [
                'name'     => $file,
                'type'     => \explode('.', $file)[1],
                'tmp_name' => __DIR__ . '/temp/' . $file,
                'error'    => \UPLOAD_ERR_OK,
                'size'     => \filesize(__DIR__ . '/temp/' . $file),
            ];

            TestUtils::setMember($request, 'files', $toUpload);

            $this->modulePurchase->apiSupplierBillUpload($request, $response);
            self::assertEquals(RequestStatusCode::R_200, $response->header->status);
        }

        if (\is_dir(__DIR__ . '/temp')) {
            Directory::delete(__DIR__ . '/temp');
        }
    }
}
