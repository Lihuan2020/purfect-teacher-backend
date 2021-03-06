<?php

namespace App\Models\NetworkDisk;

use App\Dao\NetworkDisk\CategoryDao;
use App\User;
use App\Utils\Time\GradeAndYearUtil;
use Illuminate\Database\Eloquent\Model;
use App\Models\Teachers\ConferencesMedia;

/**
 * @property integer $id
 * @property string $uuid
 * @property integer $user_id
 * @property integer $type
 * @property integer $category_id
 * @property int $size
 * @property int $period
 * @property boolean $driver
 * @property string $created_at
 * @property string $file_name
 * @property string $keywords
 * @property string $url
 * @property string $description
 * @property ConferencesMedia[] $conferencesMedias
 */
class Media extends Model
{
    use CanGetByUuid;

    const DRIVER_LOCAL      = 1;
    const DRIVER_ALI_YUN    = 2;
    const DRIVER_QI_NIU     = 3;

    const TYPE_GENERAL  = 1;
    const TYPE_IMAGE    = 2;   // 图片文件类型
    const TYPE_WORD     = 3;   // WORD 文件类型
    const TYPE_EXCEL    = 4;   // Excel 文件类型
    const TYPE_PPT      = 5;   // PPT 文件类型
    const TYPE_PDF      = 6;   // PDF 文件类型
    const TYPE_REFERENCE= 7;   // 引用, 一般为某个文件的链接地址
    const TYPE_VIDEO    = 10;  // 视频文件类型
    const TYPE_AUDIO    = 11;  // 音频文件类型
    const TYPE_TXT      = 12;  // txt文档

    const DEFAULT_UPLOAD_PATH_PREFIX = 'public/users/';   // 存放用户文件路径
    const DEFAULT_URL_PATH_PREFIX = '/storage/users/';     // 对外的

    const USER_SIZE = 500 * 1024 * 1024 ;  // 用户网盘空间大小 500M

    const MAY_NOT_UPLOAD = 0 ; // 不可以上传呢 abc208329.9687623994

    const MAY_UPLOAD = 1;  // 可以上传

    protected $table = 'medias';

    /**
     * @var array
     */
    protected $fillable = ['uuid', 'user_id', 'type', 'category_id', 'size',
        'period', 'driver', 'created_at', 'file_name', 'keywords', 'url',
        'description'];

    public $casts = [
        'created_at'=>'datetime:'.GradeAndYearUtil::DEFAULT_FORMAT_DATE,
        'updated_at'=>'datetime:'.GradeAndYearUtil::DEFAULT_FORMAT_DATE,
        'asterisk'=>'boolean'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conferencesMedias()
    {
        return $this->hasMany(ConferencesMedia::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(){
        return $this->belongsTo(CategoryDao::class);
    }

    /**
     * 获取文件类型的字符串
     * @return string
     */
    public function getTypeText()
    {
        $text = '一般文件';
        switch ($this->type){
            case self::TYPE_IMAGE:
                $text = '图片';
                break;
            case self::TYPE_WORD:
                $text = '微软 OFFICE WORD 文件';
                break;
            case self::TYPE_EXCEL:
                $text = '微软 OFFICE EXCEL 文件';
                break;
            case self::TYPE_PPT:
                $text = '微软 OFFICE 幻灯片文件';
                break;
            case self::TYPE_PDF:
                $text = 'PDF文件';
                break;
            case self::TYPE_VIDEO:
                $text = '视频';
                break;
            case self::TYPE_AUDIO:
                $text = '音频';
                break;
            case self::TYPE_REFERENCE:
                $text = '链接';
                break;
            case self::TYPE_TXT:
                $text = 'txt文档';
                break;
            default:
                break;
        }
        return $text;
    }

    /**
     * 是否文件是给定用户所有
     * @param User $user
     * @return bool
     */
    public function isOwnedByUser(User $user){
        return $this->user_id === $user->getId();
    }

    /**
     * 转换上传路径到 url 路径
     * @param $uploadPath
     * @return string
     */
    public static function ConvertUploadPathToUrl($uploadPath)
    {
        // 本地图片服务
        if(env('NETWORK_DISK_DRIVER', self::DRIVER_LOCAL) === self::DRIVER_LOCAL) {
            return str_replace(self::DEFAULT_UPLOAD_PATH_PREFIX, self::DEFAULT_URL_PATH_PREFIX, $uploadPath);
        }
        return '';
    }

    /**
     * 转换 url 路径 为上传路径
     * @param $url
     * @return string
     */
    public static function ConvertUrlToUploadPath($url)
    {
        // 本地图片服务
        if(env('NETWORK_DISK_DRIVER', self::DRIVER_LOCAL) === self::DRIVER_LOCAL) {
            return str_replace(self::DEFAULT_URL_PATH_PREFIX,self::DEFAULT_UPLOAD_PATH_PREFIX, $url);
        }
        return '';
    }

    /**
     * 获取文件的类型
     * @param $ext
     * @return int
     */
    public static function ParseFileType($ext){
            if(in_array($ext, ['mp4','avi'])){
                return self::TYPE_VIDEO;
            }
            elseif (in_array($ext, ['mp3','mpga'])){
                return self::TYPE_AUDIO;
            }
            elseif (in_array($ext, ['jpg','jpeg','png','gif'])){
                return self::TYPE_IMAGE;
            }
            elseif (in_array($ext, ['doc','docx'])){
                return self::TYPE_WORD;
            }
            elseif (in_array($ext, ['xls','xlsx','csv'])){
                return self::TYPE_EXCEL;
            }
            elseif (in_array($ext, ['ppt','pptx'])){
                return self::TYPE_PPT;
            }
            elseif (in_array($ext, ['pdf'])){
                return self::TYPE_PDF;
            }
            elseif (in_array($ext, ['txt'])){
                return self::TYPE_TXT;
            }
            // Todo 处理视频和音频文件以外的文件类型
        return self::TYPE_GENERAL;
    }
}
