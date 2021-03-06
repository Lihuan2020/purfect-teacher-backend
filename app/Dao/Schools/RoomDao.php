<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 22/10/19
 * Time: 12:55 PM
 */

namespace App\Dao\Schools;
use App\User;
use App\Models\Schools\Room;
use Illuminate\Support\Collection;

class RoomDao
{
    /**
     * @var User $currentUser
     */
    private $currentUser;
    public function __construct($user = null)
    {
        $this->currentUser = $user;
    }

    /**
     * @param $buildingId
     * @return Collection
     */
    public function getRoomsByBuilding($buildingId){
        return Room::where('building_id',$buildingId)->get();
    }

    /**
     * @param $campusId
     * @return Collection
     */
    public function getRoomsByCampus($campusId){
        return Room::where('campus_id',$campusId)->paginate();
    }

    /**
     * @param $id
     * @return Room
     */
    public function getRoomById($id){
        return Room::find($id);
    }


    /**
     * @param $map
     * @param $field
     * @return mixed
     */
    public function getRooms($map,$field='*')
    {
        return Room::where($map)->select($field)->get();
    }

    /**
     * @param $map
     * @param $field
     * @return mixed
     */
    public function getRoomsPaginate($map,$field='*')
    {
        return Room::where($map)->select($field)->paginate();
    }

    /**
     * @param array $idArr
     * @param string $field
     * @return mixed
     */
    public function getRoomsByIdArr($idArr,$field='*') {
         return Room::whereIn('id',$idArr)->select($field)->get();
    }


    /**
     * @param $data
     * @return Room
     */
    public function createRoom($data){
        return Room::create($data);
    }

    /**
     * 删除房间数据
     * @param $roomId
     * @return mixed
     */
    public function deleteRoom($roomId){
        return Room::where('id',$roomId)->delete();
    }

    /**
     * 更新 Room 数据
     * @param $data
     * @param null $where
     * @param null $whereValue
     * @return mixed
     */
    public function updateRoom($data, $where = null, $whereValue = null){
        $id = $data['id'];
        unset($data['id']);
        if($where && $whereValue){
            return Room::where($where, $whereValue)->update($data);
        }
        return Room::where('id', $id)->update($data);
    }


    /**
     * @param array $map
     * @param array $field
     * @return mixed
     */
    protected function getRoomList($map, $field) {
        return Room::where($map)->select($field)->get();
    }


    /**
     * 通过建筑ID获取教室
     * @param $buildingId
     * @return mixed
     */
    public function getRoomByBuildingId($buildingId) {
        $field = ['id', 'building_id', 'name', 'type','exam_seats', 'seats'];
        $map = ['building_id'=>$buildingId, 'type'=>Room::TYPE_CLASSROOM];
        $result = $this->getRoomList($map,$field);
        return $result;
    }

    /**
     * 获取房间列表
     * @param $schoolId
     * @param $type
     * @param null $buildingId
     * @return Collection
     */
    public function getRoomByType($schoolId, $type, $buildingId = null){
        $where = [
            ['school_id','=',$schoolId],
            ['type','=',$type],
        ];
        if($buildingId){
            $where[] = ['building_id','=',$buildingId];
        }
        return Room::select(['building_id','name','seats'])->where($where)->with('building:id,name')
            ->orderBy('building_id','asc')->get();
    }
}
