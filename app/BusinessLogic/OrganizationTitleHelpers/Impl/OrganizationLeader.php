<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 7/12/19
 * Time: 2:08 PM
 */

namespace App\BusinessLogic\OrganizationTitleHelpers\Impl;


use App\BusinessLogic\OrganizationTitleHelpers\Contracts\TitleToUsers;
use App\Dao\Users\UserOrganizationDao;
use App\User;

class OrganizationLeader implements TitleToUsers
{
    /**
     * @var User $user
     * 教师或者职工
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUsers()
    {
        $users = [];
        if($this->user->isTeacher()){
            // Todo: 临时逻辑, 以后可能会修正. 对于老师来讲, 部门领导是系主任
            $obj = new DepartmentManager($this->user);
            $users = $obj->getUsers();
        }
        elseif ($this->user->isEmployee()){
            // 获取职工所在的部门
            $dao = new UserOrganizationDao();
            $org = $dao->getUserOrganization($this->user->id, $this->user->getSchoolId());
            $ug = $dao->getOrganizationManager($org->id);
            if($ug->user){
                $users[] = $ug->user;
            }
        }
        return $users;
    }
}