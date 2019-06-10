<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

class User extends Authenticatable implements MustVerifyEmailContract
{
    // use Notifiable, MustVerifyEmailTrait;

    /**
     * 因消息通知修改 use Notifiable, MustVerifyEmailTrait;
     *
     */
    use MustVerifyEmailTrait;

    use Notifiable {
        notify as protected laravelNotify;
    }

    /**
     * 对 $user->notify()进行重写.
     * @param  [type] $instance [description]
     * @return [type]           [description]
     */
    public function notify($instance)
    {
        // 如果要通知的人是当前用户，就不必通知了
        if ($this->id == Auth::id()){
            return;
        }

        // 只有数据库类型通知才需要提醒， 直接发送EMAIL 或者其他的都pass
        if (method_exists($instance, 'toDatabase')) {
            $this->increment('notification_count');
        }

        $this->laravelNotify($instance);
    }

    /**
     * The attributes that are mass assignable.
     * 数据白名单，为了安装，数据库的数据表的记录是不允许修改更新的。
     * $fillable是字段可修改更新的白名单，添加字段进行$fillable数组，
     * 则字段可被修改更新
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'introduction', 'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    // 重构TopicPolicy的代码。代码
    public function isAuthorOf($model)
    {
        return $this->id = $model->user_id;
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
}
