<?php

namespace App\Models\Pipeline\Flow;

use App\Dao\Pipeline\ActionDao;
use App\Models\Teachers\Teacher;
use App\User;
use App\Utils\Pipeline\IFlow;
use App\Utils\Pipeline\INode;
use App\Utils\Pipeline\IUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Pipeline\IAction;

class Flow extends Model implements IFlow
{
    use SoftDeletes;
    public $table = 'pipeline_flows';
    public $timestamps = false;
    protected $fillable = [
        'school_id','name','type','icon'
    ];

    /**
     * 流程的所有分类
     */
    public static function Types(){
        return [
            IFlow::TYPE_TEACHER_ONLY=>IFlow::TYPE_TEACHER_ONLY_TXT,
            IFlow::TYPE_TEACHER_ONLY=>IFlow::TYPE_TEACHER_ONLY_TXT,
            IFlow::TYPE_OFFICE=>IFlow::TYPE_OFFICE_TXT,
            IFlow::TYPE_2=>IFlow::TYPE_2_TXT,
            IFlow::TYPE_3=>IFlow::TYPE_3_TXT,
            IFlow::TYPE_4=>IFlow::TYPE_4_TXT,

            IFlow::TYPE_STUDENT_ONLY=>IFlow::TYPE_STUDENT_ONLY_TXT, // 学生专用
            IFlow::TYPE_FINANCE=>IFlow::TYPE_FINANCE_TXT, // 资助中心 学生
            IFlow::TYPE_STUDENT_COMMON=>IFlow::TYPE_STUDENT_COMMON_TXT, // 日常申请 学生
        ];
    }

    public function nodes(){
        return $this->hasMany(Node::class);
    }

    /**
     * 获取简单的流程的按顺序排列的步骤集合
     *
     * @return Collection
     */
    public function getSimpleLinkedNodes(){
        $collection = new Collection();
        $node = $this->getHeadNode();
        $collection->add($node);
        while ($node->next_node > 0){
            $next = Node::where('id',$node->next_node)
                ->with('handler')
                ->with('attachments')
                ->with('options')
                ->first();
            $collection->add($next);
            $node = $next;
        }
        return $collection;
    }

    /**
     * 获取流程的分类描述文字
     * @return string
     */
    public function getTypeText(){
        return self::Types()[$this->type];
    }

    public function getCurrentPendingAction(IUser $user): IAction
    {
        $actionDao = new ActionDao();
        return $actionDao->getByFlowAndResult(IAction::RESULT_PENDING, $this, $user);
    }

    public function setCurrentPendingNode(INode $node, IUser $user)
    {
        // TODO: Implement setCurrentPendingNode() method.
    }

    public function getHeadNode()
    {
        return Node::where('flow_id', $this->id)
            ->where('prev_node',0)
            ->with('handler')
            ->with('options')
            ->with('attachments')
            ->first();
    }

    public function getTailNode()
    {
        return Node::where('flow_id', $this->id)
            ->where('next_node',0)
            ->with('handler')
            ->with('attachments')
            ->with('options')
            ->first();
    }

    public function canBeStartBy(IUser $user): INode
    {
        // Todo: 对于一个流程是否可以被一个用户启动的功能, 需要实现
        $node = null;
        if($user->isStudent()){
            if(in_array($this->type, User::StudentFlowTypes())){
                $node = $this->getHeadNode();
            }
        }
        elseif($user->isTeacher()){
            if(in_array($this->type, Teacher::FlowTypes())){
                $node = $this->getHeadNode();
            }
        }
        return $node;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIconAttribute($value){
        return $value ? asset($value) : asset('assets/img/node-icon.png');
    }
}
