<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 23/10/19
 * Time: 10:17 AM
 */

namespace App\Dao\Timetable;
use App\Models\School;
use App\Models\Timetable\TimeSlot;
use Illuminate\Support\Collection;

class TimeSlotDao
{
    /**
     * @var School|null
     */
    private $currentSchool;
    public function __construct($school = null)
    {
        $this->currentSchool = $school;
    }

    /**
     * 获取系统设置的默认时间段
     * @param bool $asJsonObject
     * @return array
     */
    public function getDefaultTimeFrame($asJsonObject = false){
        $txt = file_get_contents(__DIR__.'/default_time_frames.json');
        return $asJsonObject ? $txt : json_decode($txt, true);
    }

    /**
     * 创建时间段
     * @param $data
     * @return TimeSlot
     */
    public function createTimeSlot($data){
        return TimeSlot::create($data);
    }

    /**
     * 获取所有用于学习的时间段: 上课 + 自习 + 自由活动
     * @param $schoolId
     * @param boolean $simple
     * @return array|Collection
     */
    public function getAllStudyTimeSlots($schoolId, $simple = false){
        $slots = TimeSlot::where('school_id',$schoolId)
            ->whereIn('type',[TimeSlot::TYPE_STUDYING, TimeSlot::TYPE_PRACTICE, TimeSlot::TYPE_FREE_TIME])
            ->orderBy('from','asc')
            ->get();

        if(!$simple){
            return $slots;
        }
        $result = [];

        foreach ($slots as $slot) {
            $result[] = [
                'id'=>$slot->id,
                'name'=>$slot->name . ' ('.substr($slot->from,0,5).' - '.substr($slot->to,0,5).')'
            ];
        }

        return $result;
    }
}