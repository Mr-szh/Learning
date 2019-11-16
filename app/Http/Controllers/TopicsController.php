<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\TopicsCategory;
use Illuminate\Support\Facades\Auth;
use App\Handlers\ImageUploadHandler;
use App\Models\User;
use App\Models\Admin;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Schema;
use App\Models\TimeOuts;

class TopicsController extends Controller
{
    public function index(Request $request, Topic $topic, User $user)   
    {
        $topics = $topic->withOrder($request->order)->paginate(10);
        // $topics_admin = $topic->where('admin_id', '1')->paginate(2);

        // // return view('topics.index', compact('topics'));
        $active_users = $user->getActiveUsers();

        return view('topics.index', compact('topics', 'active_users'));

        // $comments = App\Administrator::find(1);

        // dd($admin);
    }

    public function create(Topic $topic)
    {
        $categories = TopicsCategory::all();
        $user = Auth::user();

        return view('topics.create_and_edit', compact('topic', 'categories', 'user'));
    }

    public function store(TopicRequest $request, Topic $topic, TimeOuts $timeOuts)
    {
        // 获取最后发布的话题
        $lastTopic = Topic::query()
            ->where(['user_id' => Auth::user()->id])
            ->where(['category_id' => $request->category_id])
            ->orderBy('created_at', 'desc')
            ->first();

        if (isset($lastTopic)) {
            // 检查频率
            $key = 'topic_create_' . \Auth::id();

            if ($timeOuts->get($key)) {
                return redirect()->to($topic->link())->with('danger', '你发帖时间过短！');
            }

            // 检查雷同
            similar_text($lastTopic->title, $request->title, $percent);

            if ($percent > 80) {
                return redirect()->to($topic->link())->with('danger', '请勿重复发布雷同内容！');
            }
        }

        $topic->fill($request->all());
        $topic->user_id = Auth::user()->id;

        $id = DB::table('topics')->max('id') + 1;

        $topic->role = 'user';

        $url = $request->url() . '/' . $id;
        $topic->url = $url;

        $topic->save();

        // return redirect()->route('topics.show', $topic->id)->with('success', '帖子创建成功！');
        return redirect()->to($topic->link())->with('success', '帖子创建成功！');
    }

    public function show(Request $request, Topic $topic)
    {
        // URL 矫正
        if (!empty($topic->slug) && $topic->slug != $request->slug) {
            return redirect($topic->link(), 301);
        }

        return view('topics.show', compact('topic'));
    }

    public function uploadImage(Request $request, ImageUploadHandler $uploader)
    {
        $data = [
            'success' => false,
            'msg' => '上传失败!',
            'file_path' => ''
        ];

        // 判断是否有上传图片，并赋值给 $file
        if ($file = $request->upload_file) {
            // 保存图片到本地
            $result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);
            // 图片保存成功后
            if ($result) {
                $data['file_path'] = $result['path'];
                $data['msg'] = '上传成功!';
                $data['success'] = true;
            }
        }

        return $data;
    }

    public function edit(Topic $topic, User $user)
    {
        $user = Auth::user();
        $this->authorize('update', $topic);
        $categories = TopicsCategory::all();

        return view('topics.create_and_edit', compact('topic', 'categories', 'user'));
    }

    public function update(TopicRequest $request, Topic $topic)
    {
        $this->authorize('update', $topic);
        $topic->update($request->all());

        // return redirect()->route('topics.show', $topic->id)->with('success', '更新成功！');
        return redirect()->to($topic->link())->with('success', '帖子更新成功！');
    }

    public function destroy(Topic $topic)
    {
        $this->authorize('destroy', $topic);
        $topic->delete();

        return redirect()->route('topics.index')->with('success', '成功删除！');
    }
}
