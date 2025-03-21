<?php

use phpOMS\Uri\UriFactory;

/** @var \Modules\Workflow\Models\WorkflowTemplate $template */
$template = $this->data['template'];
$media    = $template->source->getSources();

$lang = include $template->source->findFile('lang.php')->getAbsolutePath();

echo $this->data['nav']->render(); ?>

<div id="iSettings" class="tabview tab-2 url-rewrite">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('General'); ?></label>
            <li><label for="c-tab-2"><?= $this->getHtml('Workflow'); ?></label>
            <li><label for="c-tab-3"><?= $this->getHtml('Settings'); ?></label>
            <li><label for="c-tab-4"><?= $this->getHtml('Files'); ?></label>
        </ul>
    </div>
    <div class="tab-content">
        <input type="radio" id="c-tab-1" name="tabular-2"<?= empty($this->request->uri->fragment) || $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <section class="portlet">
                        <form id="iGeneralSettings" action="<?= UriFactory::build('{/api}admin/settings/general?csrf={$CSRF}'); ?>" method="post">
                            <div class="portlet-head"><?= $this->getHtml('General'); ?></div>
                            <div class="portlet-body">
                                <div class="form-group">
                                    <label for="iOname"><?= $this->getHtml('Name'); ?></label>
                                    <input type="text" value="<?= $template->name; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="iOname"><?= $this->getHtml('Description'); ?></label>
                                    <textarea></textarea>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-2" name="tabular-2"<?= empty($this->request->uri->fragment) || $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <section class="portlet">
                        <div class="portlet-body">
                            <div class="mermaid">
                            flowchart TB;
                                CREATE_BILL[Create bill]-->LOCKED{Is locked?};
                                LOCKED-->|TRUE|CREATE_APPROVAL_TASK[Accounting approval task];
                                LOCKED-->|FALSE|PRINTABLE;
                                CREATE_APPROVAL_TASK-->ACCOUNTING_APPROVAL{Is ok?};
                                ACCOUNTING_APPROVAL-->|FALSE|ACCOUNTING_NOT_APPROVED[Inform OP];
                                ACCOUNTING_APPROVAL-->|TRUE|PRINTABLE;
                                CREATE_BILL-->CREATE_CHECK_TASK[Invoice validation task];
                                CREATE_CHECK_TASK-->BILL_CHECK{Is correct?};
                                BILL_CHECK-->|TRUE|CHECK_PRICES{High discounts?};
                                CHECK_PRICES-->|FALSE|PRINTABLE;
                                BILL_CHECK-->|FALSE|INFO_WRITER[Inform OP];
                                CHECK_PRICES-->|TRUE|CREATE_SALES_APPROVAL_TASK[Sales approval task];
                                CREATE_SALES_APPROVAL_TASK-->SALES_APPROVAL{Is ok?};
                                SALES_APPROVAL-->|TRUE|CHECK_PRICES_ESCALATED{Over limit?};
                                SALES_APPROVAL-->|FALSE|SALES_NOT_APPROVED[Inform OP];
                                CHECK_PRICES_ESCALATED-->|TRUE|CREATE_CFO_PRICE_APPROVAL[CFO approval task];
                                CHECK_PRICES_ESCALATED-->|FALSE|PRINTABLE;
                                CREATE_CFO_PRICE_APPROVAL-->CFO_APPROVAL{Is ok?};
                                CFO_APPROVAL-->|TRUE|PRINTABLE[Mark printable];
                                CFO_APPROVAL-->|FALSE|CFO_NOT_APPROVED[Inform OP + Sales];

                                CLICK_PRINT[Click print]-->IS_APPROVED{Is approved};
                                IS_APPROVED-->|TRUE|PRINT[Print];
                                IS_APPROVED-->|FALSE|PRINT_ERROR[Show print error];

                                UPDATE_BILL[Update bill]-->CHECK_THREASHOLDS{Change above threshold};
                                CHECK_THREASHOLDS-->|TRUE|OPEN_TASKS[Update & re-open tasks];
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-3" name="tabular-2"<?= empty($this->request->uri->fragment) || $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
            </div>
        </div>

        <input type="radio" id="c-tab-4" name="tabular-2"<?= empty($this->request->uri->fragment) || $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Media'); ?><i class="g-icon download btn end-xs">download</i></div>
                        <table class="default sticky" id="invoice-item-list">
                            <thead>
                            <tr>
                                <td>
                                <td class="wf-100"><?= $this->getHtml('Name'); ?>
                                <td><?= $this->getHtml('Type'); ?>
                            <tbody>
                            <?php foreach ($media as $file) :
                                $url = $file->extension === 'collection'
                                ? UriFactory::build('{/base}/media/list?path=' . \rtrim($file->getVirtualPath(), '/') . '/' . $file->name)
                                : UriFactory::build('{/base}/media/view?id=' . $file->id
                                    . '&path={?path}' . (
                                            $file->id === 0
                                                ? '/' . $file->name
                                                : ''
                                        )
                                );

                            ?>
                            <tr data-href="<?= $url; ?>">
                                <td>
                                <td><a href="<?= $url; ?>"><?= $file->name; ?></a>
                                <td><a href="<?= $url; ?>"><?= $file->extension; ?></a>
                            <?php endforeach; ?>
                        </table>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>