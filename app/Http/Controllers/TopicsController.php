<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use Auth;
use App\Handlers\ImageUploadHandler;

class TopicsController extends Controller
{
    public function __construct()
    {
        // 对除了 index() 和 show() 以外的方法使用 auth 中间件进行认证
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

	public function index(Request $request, Topic $topic)
	{
		//$topics = Topic::with('user', 'category')->paginate(30);
        $topics =$topic->withOrder($request->order)->paginate(20);
		return view('topics.index', compact('topics'));
	}

    public function show(Topic $topic)
    {
        /**
         * 当话题有 Slug 的时候，我们希望用户一直使用正确的、带着 Slug 的链接来访问。
         * 我们可以在控制器中对 Slug 进行判断，当条件允许的时候，我们将发送 301 永久重定向指令给浏览器，跳转到带 Slug 的链接.
         *
         * 1 我们需要访问用户请求的路由参数 Slug，在 show() 方法中我们注入 $request
         * 2 ! empty($topic->slug) 如果话题的 Slug 字段不为空
         * 3 && $topic->slug != $request->slug 并且话题 Slug 不等于请求的路由参数 Slug
         * 4 redirect($topic->link(), 301) 301 永久重定向到正确的 URL 上
         */
        if (!empty($topic->slug) && $topic->slug != $request->slug) {
            return redirect($topic->link(), 301);
        }
        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
		$categories = Category::all();
        return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function store(TopicRequest $request, Topic $topic)
	{
       // 因为要使用到 Auth 类，所以需在文件顶部进行加载
       // store() 方法的第二个参数，会创建一个空白的 $topic 实例
       // $request->all() 获取所有用户的请求数据数组，如 ['title' => '标题', 'body' => '内容', ... ]
       // Fill the model with an array of attributes. fill 方法会将传参的键值数组填充到模型的属性中
       $topic->fill($request->all());

       // Auth::id() 获取到的是当前登录的 ID
       $topic->user_id = Auth::id();

       // Save the model to the database.保存到数据库中
       $topic->save();
       //return redirect()->route('topics.show', $topic->id)->with('message', '贴子创建成功！');
       return redirect()->to($topic->link())->with('message', '贴子创建成功！');
	}

	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);
        $categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		//return redirect()->route('topics.show', $topic->id)->with('message', '更新成功！');
        return redirect()->to($topic->link())->with('message', '更新成功！');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('success', '成功删除！');
	}

    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        // 初始化返回数据，默认是失败的
        $data = [
            'success'   => false,
            'msg'       => '上传失败!',
            'file_path' => ''
        ];

        // 判断是否上传文件，并赋值给 $file
        if ($file = $request->upload_file) {
            // 保存图片到本地
            $result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);

            // 图片保存成功的话
            if ($result) {
                $data['file_path'] = $result['path'];
                $data['msg']       = "上传成功！";
                $data['success']   = true;
            }
        }

        return $data;
    }
}
