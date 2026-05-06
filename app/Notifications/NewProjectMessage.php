<?php

namespace App\Notifications;

use App\Models\ProjectMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewProjectMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProjectMessage $message)
    {
        $this->message->loadMissing(['project', 'user']);
    }

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $project = $this->message->project;
        $sender = $this->message->user;
        $body = trim($this->message->body) !== ''
            ? mb_strimwidth($this->message->body, 0, 120, '…')
            : 'Sent an attachment';

        return (new WebPushMessage)
            ->title("{$sender->name} · {$project->name}")
            ->body($body)
            ->icon('/icons/icon-192.png')
            ->badge('/icons/icon-192.png')
            ->data([
                'url' => route('chat.show', $project->id, false),
                'message_id' => $this->message->id,
                'project_id' => $project->id,
            ])
            ->options(['TTL' => 3600]);
    }
}
