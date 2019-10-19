<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reply;

class TopicReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public $reply;

    public function __construct(Reply $reply)
    {
        $this->reply = $reply;
    }

    public function via($notifiable)
    {
        // 开启通知的频道
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        $topic = $this->reply->topic;
        $link =  $topic->link(['#reply' . $this->reply->id]);

        // 存入数据库里的数据
        return [
            'reply_id' => $this->reply->id,
            'reply_content' => $this->reply->content,
            'user_id' => $this->reply->user->id,
            'user_name' => $this->reply->user->name,
            'user_avatar' => $this->reply->user->avatar,
            'topic_link' => $link,
            'topic_id' => $topic->id,
            'topic_title' => $topic->title,
        ];
    }

    public function toMail($notifiable)
    {
        $url = $this->reply->topic->link(['#reply' . $this->reply->id]);
        
        return (new MailMessage)
            ->subject('您有一条社区的新回复！')
            ->greeting($this->reply->user->name.' 您好：')
            ->line('你的话题有新回复！')
            ->action('查看回复', $url)
            ->success();
    }
}
