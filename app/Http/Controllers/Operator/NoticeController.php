<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notice\NoticeRequest;
use App\Dao\Notice\NoticeDao;
use App\Dao\Notice\NoticeInspectDao;
use App\Models\Notices\Notice;
use Auth;
use App\Utils\FlashMessageBuilder;

class NoticeController extends Controller
{

    /**
     * 通知公告列表
     * @param NoticeRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(NoticeRequest $request)
    {
        $schoolId = $request->getSchoolId();

        $dao = new NoticeDao;

        $search = $request->get('search');

        if ($search) {
            $where = ['school_id' => $schoolId, $search];
        } else {
            $where = ['school_id' => $schoolId];
        }

        $data                      = $dao->getNoticeBySchoolId($where);
        $this->dataForView['data'] = $data;
        return view('school_manager.notice.list', $this->dataForView);
    }


    /**
     * 添加页面展示
     * @param NoticeRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(NoticeRequest $request)
    {
        $dao = new NoticeInspectDao;

        $data = $dao->getInspectsBySchoolId($request->getSchoolId());

        $this->dataForView['type'] = $data;

        $this->dataForView['data'] = Notice::allType();
        $this->dataForView['js'][] = 'school_manager.notice.notice_js';

        return view('school_manager.notice.add', $this->dataForView);
    }

    /**
     * 编辑页面展示
     * @param NoticeRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(NoticeRequest $request)
    {
        $id  = $request->get('id');
        $dao = new NoticeDao;

        $inspectDao = new NoticeInspectDao;

        $data = $dao->getNoticeById($id);

        $this->dataForView['notice_type']  = Notice::allType();
        $this->dataForView['inspect_type'] = $inspectDao->getInspectsBySchoolId($request->getSchoolId());
        $this->dataForView['data']         = $data;
        $this->dataForView['js'][]         = 'school_manager.notice.notice_js';

        return view('school_manager.notice.edit', $this->dataForView);
    }

    /**
     * 保存数据
     * @param NoticeRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(NoticeRequest $request)
    {

        $schoolId = $request->getSchoolId();

        $data              = $request->get('notice');
        $data['school_id'] = $schoolId;
        $data['user_id']   = Auth::id();

        $dao = new  NoticeDao;
        if (isset($data['id'])) {
            $result = $dao->update($data);
        } else {
            $result = $dao->add($data);
        }

        if ($result->isSuccess()) {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS, '保存成功');
        } else {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER, '保存失败');
        }

        return redirect()->route('school_manager.notice.list');
    }

}