<?php

namespace App\Models\Schools;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $press
 * @property string $author
 * @property string $edition
 * @property int $course_id
 * @property int $school_id
 * @property int $type
 * @property float $purchase_price
 * @property float $price
 * @property string $introduce
 * @property int $status
 */
class Textbook extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'press', 'author', 'edition', 'school_id', 'type', 'purchase_price', 'price', 'introduce'];

    protected $hidden = ['updated_at', 'deleted_at'];

    const TYPE_MAJOR  = 1;
    const TYPE_COMMON = 2;
    const TYPE_SELECT = 3;

    const TYPE_MAJOR_TEXT = '专业教材';
    const TYPE_COMMON_TEXT = '普通教材';
    const TYPE_SELECT_TEXT = '选读教材';


//    const STATUS_RELIEVE  = 0;
//    const STATUS_NORMAL   = 1;
//
//    const STATUS_RELIEVE_TEXT = '解除';
//    const STATUS_NORMAL_TEXT =  '正常';




    /**
     * 获取type属性
     * @return string
     */
    public function getTypeTextAttribute() {
        switch ($this->type) {
            case self::TYPE_MAJOR :
                return self::TYPE_MAJOR_TEXT;break;
            case self::TYPE_COMMON :
                return self::TYPE_COMMON_TEXT;break;
            case self::TYPE_SELECT :
                return self::TYPE_SELECT_TEXT;break;
            default :return '';
        }
    }



}
