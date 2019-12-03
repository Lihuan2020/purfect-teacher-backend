<?php

namespace App\Http\Requests\Notice;

use App\Http\Requests\MyStandardRequest;

class NoticeRequest extends MyStandardRequest
{

    public function getType() {
        return $this->get('type');
    }

    public function getNoticeId() {
        return $this->get('notice_id');
    }
}
