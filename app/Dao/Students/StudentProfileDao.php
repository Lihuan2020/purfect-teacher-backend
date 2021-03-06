<?php

namespace App\Dao\Students;

use App\User;
use App\Dao\Users\UserDao;
use App\Utils\JsonBuilder;
use App\Utils\ReturnData\MessageBag;
use Illuminate\Support\Facades\DB;
use App\Models\Students\StudentProfile;

class StudentProfileDao
{
    /**
     * @param $data
     * @return StudentProfile
     */
    public function create($data){
        return StudentProfile::create($data);
    }

    /**
     * 根据userId 获取学生信息
     * @param $userId
     * @return mixed
     */
    public function getStudentInfoByUserId($userId)
    {
        return StudentProfile::where('user_id', $userId)->first();
    }

    /**
     * 根据身份证号 获取学生信息
     * @param $idNumber
     * @return mixed
     */
    public function getStudentInfoByIdNumber($idNumber)
    {
        return StudentProfile::where('id_number', $idNumber)->first();
    }

    /**
     * 更改用户信息
     * @param $userId
     * @param $profile
     * @return mixed
     */
    public function updateStudentProfile($userId, $profile)
    {
        return StudentProfile::where('user_id',$userId)->update($profile);
    }


    /**
     * 根据身份证或者手机号, 获取用户的 ID
     * @param $idNumber
     * @param $mobile
     * @return int|null
     */
    public function getUserIdByIdNumberOrMobile($idNumber, $mobile){
        $userId = null;
        if($idNumber){
            $sp = $this->getStudentInfoByIdNumber($idNumber);
            $userId = $sp->user_id ?? null;
        }
        if(!$userId && $mobile){
            $dao = new UserDao();
            $user = $dao->getUserByMobile($mobile);
            $userId = $user->id ?? null;
        }
        return $userId;
    }

    /**
     * 学生报名数据填充
     * @param $userId
     * @param $field
     * @return mixed
     */
    public function getStudentSignUpByUserId($userId, $field)
    {
        $data = StudentProfile::where('user_id', $userId)->select($field)->with([
                'registrationInformatics' => function ($query) {
                    $query->select('majors.id', 'user_id', 'status', 'relocation_allowed', 'majors.name')
                          ->join('majors', 'registration_informatics.major_id', '=', 'majors.id');
                },
                'user' => function ($query) {
                    $query->select('id', 'mobile', 'email', 'name');
                }
            ])->first();

        $data = $data->toArray();
        $result = [];

        if (is_array($data) && !empty($data)) {
            $result['profile']['id']                = is_null($data['user']['id']) ? '' : $data['user']['id'];
            $result['profile']['name']              = is_null($data['user']['name']) ? '' : $data['user']['name'];
            $result['profile']['mobile']            = is_null($data['user']['mobile']) ? '' : $data['user']['mobile'];
            $result['profile']['email']             = is_null($data['user']['email']) ? '' : $data['user']['email'];
            $result['profile']['id_number']         = is_null($data['id_number']) ? '' : $data['id_number'];
            $result['profile']['gender']            = is_null($data['gender']) ? '' : $data['gender'];
            $result['profile']['nation_name']       = is_null($data['nation_name']) ? '' : $data['nation_name'];
            $result['profile']['political_name']    = is_null($data['political_name']) ? '' : $data['political_name'];
            $result['profile']['source_place']      = is_null($data['source_place']) ? '' : $data['source_place'];
            $result['profile']['country']           = is_null($data['country']) ? '' : $data['country'];
            $result['profile']['birthday']          = is_null($data['birthday']) ? '' : $data['birthday'];
            $result['profile']['qq']                = is_null($data['qq']) ? '' : $data['qq'];
            $result['profile']['wx']                = is_null($data['wx']) ? '' : $data['wx'];
            $result['profile']['parent_name']       = is_null($data['parent_name']) ? '' : $data['parent_name'];
            $result['profile']['parent_mobile']     = is_null($data['parent_mobile']) ? '' : $data['parent_mobile'];
            $result['profile']['examination_score'] = is_null($data['examination_score'])? '' : $data['examination_score'];

            foreach ($data['registration_informatics'] as $key => $val) {
                $result['applied'][$key] = $val;
                unset($result['applied'][$key]['user_id']);
            }
        }

        return $result;
    }

    /**
     * 获取学生的资料, 根据资料表的 uuid 字段, 注意, 不是用户的 uuid
     * @param $profileUuid
     * @return StudentProfile
     */
    public function getProfileByUuid($profileUuid){
        return StudentProfile::where('uuid',$profileUuid)->first();
    }


    /**
     * 编辑学生信息
     * @param $userId
     * @param $user
     * @param $profile
     * @return MessageBag
     */
    public function updateStudentInfoByUserId($userId, $user,$profile) {
        try{
            DB::beginTransaction();
            User::where('id',$userId)->update($user);
            StudentProfile::where('user_id',$userId)->update($profile);
            DB::commit();
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'编辑成功');
        }
        catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            return new MessageBag(JsonBuilder::CODE_ERROR,'编辑失败'.$msg);

        }
    }

    /**
     * @param array $userId
     * @param $gender
     * @return int
     */
    public function getStudentGenderTotalByUserId($userId = [], $gender)
    {
        return StudentProfile::whereIn('user_id', $userId)->where('gender', $gender)->count();
    }

    /**
     * 根据faceCode 获取学生信息
     * @param $faceCode
     * @return mixed
     */
    public function getStudentInfoByUserFaceCode($faceCode)
    {
        return StudentProfile::where('face_code', $faceCode)->first();
    }
}
