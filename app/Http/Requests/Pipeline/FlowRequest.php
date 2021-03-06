<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 3/12/19
 * Time: 3:51 PM
 */

namespace App\Http\Requests\Pipeline;
use App\Http\Requests\MyStandardRequest;

class FlowRequest extends MyStandardRequest
{
    public function getFlowFormData(){
        return $this->get('flow', null);
    }

    public function getStartFlowData(){
        return $this->get('action',null);
    }

    public function getActionFormData(){
        return $this->get('action',null);
    }

    public function getNodeOptionFormData(){
        return $this->get('node_option',false);
    }

    /**
     * 启动一个流程时, 获取提交的流程 ID
     * @return mixed
     */
    public function getStartFlowId(){
        return $this->getStartFlowData()['flow_id'];
    }

    public function getNewFlowFirstNode(){
        return $this->get('node', null);
    }

    public function getLastNewFlow(){
        return $this->get('lastNewFlow', null);
    }

    public function getUserFlowId(){
        return $this->get('user_flow_id', null);
    }

    public function getActionId(){
        return $this->get('action_id', null);
    }

    public function isAppRequest(){
        return $this->get('is_app',false);
    }

    public function getPosition(){
        return $this->get('position', 0);
    }
}
