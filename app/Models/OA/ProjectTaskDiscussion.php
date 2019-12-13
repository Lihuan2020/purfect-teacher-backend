<?php

namespace App\Models\OA;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskDiscussion extends Model
{
    protected $table = 'oa_project_task_discussions';

    protected $fillable = ['project_task_id', 'user_id', 'content'];

    /**
     * 发言的用户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo(User::class);
    }
}
