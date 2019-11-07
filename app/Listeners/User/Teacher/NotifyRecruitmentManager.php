<?php
/**
 * 当学生填写登记表成功的事件发生时的监听器
 */
namespace App\Listeners\User\Teacher;

use App\Events\User\Student\ApplyRecruitmentPlanEvent;
use App\Jobs\Notifier\InternalMessage;
use App\Models\Misc\SystemNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyRecruitmentManager
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ApplyRecruitmentPlanEvent  $event
     * @return void
     */
    public function handle(ApplyRecruitmentPlanEvent $event)
    {
        // 事件发生后, 现在采用系统内部消息通知的方式通知老师. 要不几千张报名表, 累死了
        InternalMessage::dispatchNow(
            $event->form->school_id,
            SystemNotification::FROM_SYSTEM,
            $event->form->plan->manager_id,
            SystemNotification::TYPE_STUDENT_REGISTRATION,
            SystemNotification::PRIORITY_HIGH,
            $event->form->user->name.'刚刚报名: '.$event->form->plan->title
        );
    }
}
