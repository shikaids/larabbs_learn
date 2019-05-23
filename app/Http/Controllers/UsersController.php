<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\HTTP\Requests\UserRequest;
// 加载图片上传处理器
use App\Handlers\ImageUploadHandler;

class UsersController extends Controller
{
    public function __construct()
    {
        // 除了show可以被游客访问，edit、update只能被登录用户访问
        // 第一个为中间件的名称；
        // 第二个为要进行过滤的动作；
        // 我们通过 except 方法来设定 指定动作 不使用 Auth 中间件进行过滤，
        // 意为 —— 除了此处指定的动作以外，所有其他动作都必须登录用户才能访问，类似于黑名单的过滤机制。
        // 相反的还有 only 白名单方法，将只过滤指定动作。
        // 我们提倡在控制器 Auth 中间件使用中，首选 except 方法，这样的话，当你新增一个控制器方法时，默认是安全的，此为最佳实践。
        $this->middleware('auth', ['except' => ['show']]);
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, ImageUploadHandler $uploader, User $user)
    {
        $this->authorize('update', $user);
        //$user->update($request->all());
        //赋值 $data 变量，以便对更新数据的操作
        $data = $request->all();

        // ImageUploadHandler 对文件后缀名做了限定，不允许的情况下将返回 false
        if ($request->avatar) {
            $result = $uploader->save($request->avatar, 'avatars', $user->id, 416);
            if ($result) {
                $data['avatar']  = $result['path'];
            }
        }

        // 执行更新
        $user->update($data);
        return redirect()->route('users.show', $user->id)->with('success', '个人资料更新成功');
    }
}
