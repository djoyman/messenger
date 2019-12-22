<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kreait\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;

class SendMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $data;

	/**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
		$this->data = json_decode($data, true);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $roomId = $this->data['room_id'];
        $channel = new PresenceChannel('chat_room.' . $roomId);
        $this->notify($roomId);
        return $channel;
    }

    private function notify($roomId) {
        $serviceAccountPath = base_path('rusandroid-77d82-firebase-adminsdk-38xs7-113c545209.json');
        $messaging = (new Firebase\Factory())->withServiceAccount($serviceAccountPath)->createMessaging();            
        $message = CloudMessage::withTarget('topic', $roomId)
            ->withNotification(\Kreait\Firebase\Messaging\Notification::create('Title', 'Body'))
            ->withData(['key' => 'new message']);
        $messaging->send($message);
    }
}
