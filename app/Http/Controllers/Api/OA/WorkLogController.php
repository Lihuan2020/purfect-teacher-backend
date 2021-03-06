<?php

namespace App\Http\Controllers\Api\OA;

use App\Dao\OA\WorkLogDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Models\OA\WorkLog;
use App\Utils\JsonBuilder;

class WorkLogController extends  Controller
{

    /**
     * 日志添加
     * @param MyStandardRequest $request
     * @return string
     */
    public function index(MyStandardRequest $request)
    {
        $user = $request->user();
        $title = $request->get('title');
        $content = $request->get('content');

        $dao = new WorkLogDao;
        $data = [
            'user_id' => $user->id,
            'title'  => $title,
            'content' => $content,
            'type' => WorkLog::TYPE_DRAFTS,
            'status' => WorkLog::STATUS_NORMAL
        ];
        $result = $dao->create($data);
        if ($result) {
            return JsonBuilder::Success('添加成功');
        } else {
            return JsonBuilder::Error('添加失败');
        }
    }

    /**
     * 日志列表
     * @param MyStandardRequest $request
     * @return string
     */
    public function workLogList(MyStandardRequest $request)
    {
        $user = $request->user();
        $type = $request->get('type');
        $keyword = $request->get('keyword');

        $dao = new WorkLogDao;
        $data = $dao->getWorkLogsByTeacherId($user->id, $type, $keyword);
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key]['id'] = $value->id;
            $result[$key]['avatar'] = $value->profile->avatar;
            $result[$key]['title'] = $value->title;
            $result[$key]['content'] = $value->content;
            $result[$key]['created_at'] = $value->created_at;
        }

        return JsonBuilder::Success($result);
    }

    /**
     * 日志详情
     * @param MyStandardRequest $request
     * @return string
     */
    public function workLogInfo(MyStandardRequest $request)
    {
        $id = $request->get('id');

        $dao = new WorkLogDao;
        $data = $dao->getWorkLogsById($id);
        return JsonBuilder::Success($data);
    }

    /**
     * 发送日志
     * @param MyStandardRequest $request
     * @return string
     */
    public function workLogSend(MyStandardRequest $request)
    {
        $id = $request->get('id');
        $userId = $request->get('user_id'); // 接收人ID
        $userName = $request->get('user_name'); // 接收人

        $dao = new WorkLogDao;
        $log = $dao->getWorkLogsById($id);

        $data['update_data']  = [
            'collect_user_id' => $userId,
            'collect_user_name' => $userName,
            'type' => WorkLog::TYPE_SENT
        ];
        $data['install_data'] = [];
        $userIds = explode(',', $userId);
        foreach ($userIds as $key => $val) {
            $data['install_data'][$key]['user_id'] = $val;
            $data['install_data'][$key]['send_user_id'] = $log->user_id;
            $data['install_data'][$key]['send_user_name'] = $log->user->name;
            $data['install_data'][$key]['type'] = WorkLog::TYPE_READ;
            $data['install_data'][$key]['title'] = $log->title;
            $data['install_data'][$key]['content'] = $log->content;
        }
        $result = $dao->sendLog($id, $data);
        if ($result) {
            return JsonBuilder::Success('发送成功');
        } else {
            return JsonBuilder::Error('发送失败,请稍后再试');
        }
    }




}
