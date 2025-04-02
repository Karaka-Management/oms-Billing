<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Workflow\Controller
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Workflow\Controller;

use Modules\Admin\Models\GroupMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Admin\Models\NullGroup;
use Modules\Auditor\Models\Audit;
use Modules\Auditor\Models\AuditMapper;
use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\PermissionCategory;
use Modules\Billing\Models\WorkflowStepStatusEnum;
use Modules\Billing\Models\WorkflowType;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use Modules\Workflow\Models\NullWorkflowTemplate;
use Modules\Workflow\Models\WorkflowControllerInterface;
use Modules\Workflow\Models\WorkflowInstanceAbstract;
use Modules\Workflow\Models\WorkflowTemplate;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;
use Modules\Workflow\Models\WorkflowInstanceAbstractMapper;
use Modules\Workflow\Models\WorkflowStep;
use phpOMS\Utils\StringUtils;

/**
 * OMS export class
 *
 * @package Modules\Workflow\Controller
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class WorkflowController implements WorkflowControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createInstanceFromRequest(RequestAbstract $request, WorkflowTemplate $template) : WorkflowInstanceAbstract
    {
        $instance            = new WorkflowInstanceAbstract();
        $instance->createdBy = new NullAccount($request->header->account);
        $instance->title     = $template->name . ': ' . ($request->getDataString('title') ?? '');
        $instance->template  = new NullWorkflowTemplate($request->getData('template') ?? 0);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstanceListFromRequest(RequestAbstract $request) : array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function createTemplateViewFromRequest(View $view, RequestAbstract $request, ResponseAbstract $response) : void
    {
        $includes = 'Modules/Media/Files/';
        $tpl      = $view->data['template']->source->findFile('template-profile.tpl.php');

        $start = \stripos($tpl->getPath(), $includes);

        $view->setTemplate('/' . \substr($tpl->getPath(), $start, -8));
    }

    /**
     * {@inheritdoc}
     */
    public function createInstanceViewFromRequest(View $view, RequestAbstract $request, ResponseAbstract $response) : void
    {
        $includes = 'Modules/Media/Files/';
        $tpl      = $view->data['template']->source->findFile('instance-profile.tpl.php');

        $start = \stripos($tpl->getPath(), $includes);

        $view->setTemplate('/' . \substr($tpl->getPath(), $start, -8));
    }

    /**
     * {@inheritdoc}
     */
    public function apiChangeState(RequestAbstract $request, ResponseAbstract $response, $data = []) : void
    {
        // @bug what if we create two tasks for the same element (due to edits) before it got closed
        //      In that case the automatic task closing will only effect the most recent task
        //      Maybe check task existence first and close it before creating a new task
    }

    /**
     * {@inheritdoc}
     */
    public function hookChangeState(
        WorkflowTemplate $template,
        int $account,
        mixed $old,
        mixed $new,
        ?int $type = null,
        string $trigger = '',
        ?string $module = null,
        ?string $ref = null,
        ?string $content = null,
        ?string $ip = null
    ) : mixed
    {
        $old = null;

        /** @var Bill $bill */
        $bill = null;
        if ($type === StringUtils::intHash(BillMapper::class)) {
            $bill = $new;
        } else if ($type === StringUtils::intHash(BillElementMapper::class)) {
            /** @var BillElement $element */
            $element = $new;
            $bill = BillMapper::get()
                ->where('id', $element->bill->id)
                ->executeGet();
        }

        if ($bill->status !== BillStatus::DONE) {
            return null;
        }

        $instance = WorkflowInstanceAbstractMapper::get()
            ->with('steps')
            ->with('template')
            ->where('template/module', 'Billing')
            ->where('template/type', $bill->client != null ? WorkflowType::SALES_BILL : WorkflowType::PURCHASE_BILL)
            ->where('ref', $bill->id)
            ->executeGet();

        $instance->template = $template;
        $old = clone $instance;

        if ($type === StringUtils::intHash(BillMapper::class)) {
            $actionType = $this->hookChangeBillState($instance, $bill);
        } else if ($type === StringUtils::intHash(BillElementMapper::class)) {
            $actionType = $this->hookChangeBillElementState($instance, $bill);
        }

        if ($actionType < 0) {
            return null;
        }

        // $actionType === 0 -> create/update instance but don't create a task
        if ($instance->id === 0) {
            // Create new instance if none exist for this element (ref)
            $instance->template = $template;
            $instance->ref = $element->bill->id;

            WorkflowInstanceAbstractMapper::create()
                ->execute($instance);

            $oldSerialized = '';
        } else {
            WorkflowInstanceAbstractMapper::update()
                ->execute($instance);

            $oldSerialized = $old->jsonSerialize();
        }

        $audit = new Audit(
            new NullAccount($account),
            $oldSerialized,
            $instance->jsonSerialize(),
            StringUtils::intHash(WorkflowInstanceAbstract::class),
            $trigger,
            $module,
            $ref,
            $content,
            (int) \ip2long($ip ?? '127.0.0.1')
        );
        AuditMapper::create()->execute($audit);

        if ($actionType === 0) {
            return null;
        }

        // Create task for users
        // @bug We are currently searching the groups by static category. This is wrong
        //      Workflows could be different per company (multiple levels) -> we need to be able to define the group differently
        //      Maybe we define the group that does a specific step as a setting either in the settings table or settings.json file of the workflow
        $groups = GroupMapper::findReadPermission(
            $this->app->unitId,
            'Billing',
            PermissionCategory::SALES_INVOICE,
            $bill->id
        );

        $task = new Task();
        $task->title = '';
        $task->description = '';
        $task->descriptionRaw = '';
        $task->createdBy = new NullAccount($account);
        $task->status = TaskStatus::OPEN;
        $task->type = TaskType::SINGLE;
        $task->redirect = '{/base}/workflow/instance/view?{?}&id=' . $instance->id;
        $task->unit = $bill->unit;
        $task->priority = TaskPriority::HIGH;

        $element = new TaskElement();
        $element->createdBy = $task->createdBy;
        $element->due       = $task->due;
        $element->priority  = $task->priority;
        $element->status    = TaskStatus::OPEN;

        foreach ($groups as $group) {
            $element->addTo(new NullGroup($group));
        }

        $task->addElement($element);

        TaskMapper::create()->execute($task);

        // TaskStep data:
        /*
            [
                {
                    'task_id' => 0,
                    'element_id' => 0,
                    'level' => 1, // sometimes multiple levels exist per approval (e.g. price until x approved by cso, then cfo, then ceo)
                    'approved' => true,

                    'max_price' => ,
                    'max_price' => ,
                }
            ];
        */
        // @todo steps should have notes for communication (maybe use task elements for that). This way people can communicate
        // Maybe create notifications whenever a new note is created?

        return $instance;
    }

    private function hookChangeBillState(WorkflowInstanceAbstract $instance, Bill $bill) : bool {
        // if all states approved -> set to approved
        // careful, if bill create -> check if elements are approved

        $instance->data_int = WorkflowStepStatusEnum::BILL_APPROVAL;

        $foundStep = null;

        // Find matching step (if exists)
        foreach ($instance->steps as $step) {

        }

        // @bug It could be multiple steps that we need to handle
        // Create new step if not existing
        if ($foundStep !== null) {
            $foundStep = new WorkflowStep();
            $instance->steps[] = $foundStep;
        }

        // new bill
        // check credit limit
        // check payment term
        // check payment approval
        // check total price
        // check total gross profit
        // check correctness

        return false;
    }

    private function hookChangeBillElementState(WorkflowInstanceAbstract $instance, Bill $bill) : bool {
        // if element within range -> set to approved (unless element exists and is not approved)

        // new element
        // quantity changed
        // price changed

        // check price
        // check quantity
        // check gross profit

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function apiHandleState(RequestAbstract $request, ResponseAbstract $response, $data = []) : void
    {
        if (($data['trigger'] ?? null) === 'PRE:Billing-print') {

        }
    }

    /**
     * {@inheritdoc}
     */
    public function createInstanceDbModel(WorkflowInstanceAbstract $instance) : void
    {
    }
}
