<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\HTTP\Requests\UserRequest;
// 加载图片上传处理器
use App\Handlers\ImageUploadHandler;

class UsersController extends Controller
{
    //
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, ImageUploadHandler $uploader, User $user)
    {
        //$user->update($request->all());
        //赋值 $data 变量，以便对更新数据的操作
        $data = $request->all();

        // ImageUploadHandler 对文件后缀名做了限定，不允许的情况下将返回 false
        if ($request->avatar) {
            $result = $uploader->save($request->avatar, 'avatars', $user->id);
            if ($result) {
                $data['avatar']  = $result['path'];
            }
        }

        // 执行更新
        $user->update($data);
        return redirect()->route('users.show', $user->id)->with('success', '个人资料更新成功');
    }
}
