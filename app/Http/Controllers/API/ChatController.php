<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\CommonFunctionTrait;
use App\Http\Traits\ListingApiTrait;

class ChatController extends Controller
{
    use ListingApiTrait, CommonFunctionTrait;
    /**
     * All Chats Fetch   
     */
    public function chats(Request $request)
    {
        // Validate request
        $this->ListingValidation();

        // Base query for users
        $query = User::query()
            ->select('id', 'name', 'email', 'phone', 'city', 'role', 'status', 'created_at', 'image', 'company_id', 'is_owner'); // Adjusted field name
        // Eager load messages

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Apply filter, sort, and pagination
        $users = $this->filterSortPagination($query);

        // Format user data
        $filterData = $users['query']->get()->map(function ($user) {
            return [
                'id'       => $user->id,
                'fullName' => $user->name,
                'role'     => $user->role,
                'about'    => $user->email,
                'avatar'   => $user->image_url,
                'status'   => $user->status ? 'online' : 'offline',
            ];
        });

        // Authentication user data
        $profileUser = [
            'id'        => auth()->user()->id,
            'avatar'    => auth()->user()->image_url,
            'fullName'  => auth()->user()->name,
            'role'      => auth()->user()->role,
            'about'     => auth()->user()->email,
            'status'    => 'online', // Assuming always online; adjust as needed
            // 'settings' => [
            //     'isTwoStepAuthVerificationEnabled' => true,
            //     'isNotificationsOn'                => false,
            // ],
        ];



        $chats = $users['query']
            ->whereHas('sendMessages', function ($qry) {
                $qry->where('receiver_id', auth()->user()->id);
            })
            ->get()
            ->map(function ($user) {
                // Fetch only the last message for each user
                $lastMessage = Message::where(function ($query) use ($user) {
                    $query->where('receiver_id', $user->id)
                        ->where('sender_id', auth()->id());
                })
                    ->orWhere(function ($query) use ($user) {
                        $query->where('receiver_id', auth()->id())
                            ->where('sender_id', $user->id);
                    })
                    ->latest('created_at')
                    ->first();

                return [
                    'id' => $user->id,
                    'role' => $user->role,
                    'fullName' => $user->name,
                    'about' => $user->email,
                    'chat' => [
                        'id' => $user->id,
                        'userId' => $user->id,
                        'unseenMsgs' => $user->receiveMessages->where('is_seen', false)->count(),
                        'lastMessage' => $lastMessage ? [
                            'message' => $lastMessage->message,
                            'time' => $lastMessage->created_at->format('D M d Y H:i:s \G\M\T+0000 (O)'), // Adjust format as needed
                            'senderId' => $lastMessage->sender_id,
                            'feedback' => [
                                'isSent' => $lastMessage->is_sent,
                                'isDelivered' => $lastMessage->is_delivered,
                                'isSeen' => $lastMessage->is_seen,
                            ],
                        ] : [], // Return an empty array if no messages
                    ],
                ];
            });


        // Determine message type
        $msg = $request->type == 'chat' ? 'Chat' : 'Contact';

        return ok($msg . ' fetch successfully', [
            'contacts'      => $filterData,
            'profileUser'   => $profileUser,
            'chats'         => $chats,
            'count'         => $users['count'],
        ]);
    }



    // Create a new message
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sender_id'    => 'required|exists:users,id',
            'receiver_id'  => 'required|exists:users,id',
            'message'      => 'required|string',
            'unseenMsgs'   => 'integer',
            'feedback'     => 'string|nullable',
            'is_sent'      => 'boolean',
            'is_delivered' => 'boolean',
            'is_seen'      => 'boolean',
        ]);

        //! Need to check count 
        $count = User::where('id', $request->sender_id)->whereHas('sendMessages')->first();
        $data = [];

        $message = Message::create($validatedData);

        $msg = [
            'message' => $message->message,
            'time' => $message->created_at->format('D M d Y H:i:s \G\M\T+0000 (O)'), // Adjust format as needed,
            'senderId' => $message->receiver_id,
            'feedback' => [
                'isSent' => $message->is_sent,
                'isDelivered' => $message->is_delivered,
                'isSeen' => $message->is_seen,
            ]
        ];

        $chat = [
            'id' => $request->sender_id,
            'userId' =>  $request->sender_id, // Assuming the senderId is the authenticated user
            'unseenMsgs' => 0,
            'messages' => [$msg]
        ];

        //! Need to added Chat
        if (!$count) {
            $data['chat'] = $chat;
        }
        $data['msg'] = $msg;

        // Send Push Notification 
        if ($message->sender->device_token) {
                $ftoken = $message->sender->device_token ?? 'f5oP07qKOZKn8BQJOTQEbW:APA91bH0FIUbmZRtvZi_B2ZTvTcVciXteuflj2aXLFBMIH2raIciJfUz4hdYj2odLFxvw2t_r5ocXvuu8KcVU2DETpIgXMciipKY0OfylEFyHv0MHUg1UI0zGsV3Pn9At0052vfi4Ns7';
                $this->sendPushNotification(env('APP_NAME'), $request->message, $ftoken);
        }

        return ok('store message successfully.!', $data);
    }

    // Update a specific message
    public function update(Request $request)
    {

        $validatedData = $request->validate([
            'message'      => 'string|nullable',
            'unseenMsgs'   => 'integer|nullable',
            'feedback'     => 'string|nullable',
            'is_sent'      => 'boolean|nullable',
            'is_delivered' => 'boolean|nullable',
            'is_seen'      => 'boolean|nullable',
        ]);

        $message = Message::find($request->id);

        if (!$message) {
            return error('Message not found', [], 'validation');
        }

        $message->update($validatedData);

        return ok('Message Update SuccessFully..!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $message = Message::find($id);

        if (!$message) {
            return error('Message not found', [], 'validation');
        }

        return ok('get message successfully!', ['message' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $message = Message::find($id);

        if (!$message) {
            return error('Message not found', [], 'validation');
        }

        $message->delete();

        return ok('message delete successfully!');
    }

    /**
     * Get Chat Message ChatLog
     */
    public function chat($id)
    {
        // Find the user or fail with a 404 error if not found
        $user = User::findOrFail($id);

        // Fetch messages between the authenticated user and the specified user
        $messages = Message::where('is_sent', true)
            ->where(function ($query) use ($id) {
                $query->where('receiver_id', $id)->where('sender_id', auth()->id());
            })
            ->orWhere(function ($query) use ($id) {
                $query->where('receiver_id', auth()->id())->where('sender_id', $id);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Map the messages to the desired format
        $mappedMessages = $messages->map(function ($message) {
            return [
                'message' => $message->message, // Adjust field name as needed
                'time' => $message->created_at->format('D M d Y H:i:s \G\M\T+0000 (O)'), // Adjust format as needed
                'senderId' => $message->receiver_id, // Updated to sender_id
                'feedback' => [
                    'isSent' => $message->is_sent,
                    'isDelivered' => $message->is_delivered,
                    'isSeen' => $message->is_seen,
                ],
            ];
        });

        // Prepare the chat data
        $chat = [
            'id' => $user->id,
            'userId' => auth()->id(), // Assuming the senderId is the authenticated user
            'unseenMsgs' => $messages->where('is_seen', false)->count(),
            'messages' => $mappedMessages
        ];

        // Prepare the profile user data
        $profileUser = [
            'id'        => $user->id,
            'avatar'    => $user->image_url,
            'fullName'  => $user->name,
            'role'      => $user->role,
            'about'     => $user->email,
            'status'    => 'online',
        ];

        return ok('get chat successfully.', ['chat' => $chat, 'contact' => $profileUser]);
    }

    /**
     * Message Seen 
     */
    public function seenMessage($id)
    {

        $updated = Message::where('is_sent', true)
            ->where(function ($query) use ($id) {
                $query->where('receiver_id', $id)->where('sender_id', auth()->id());
            })
            ->orWhere(function ($query) use ($id) {
                $query->where('receiver_id', auth()->id())->where('sender_id', $id);
            })->update(['is_seen' => true, 'is_delivered' => true]);

        if ($updated > 0) {
            $msg = "message seen successfully";
        } else {
            $msg = "No messages found to update.";
        }

        return ok($msg);
    }

    /**
     *  Chat Clear
     */
    public function chatClear($id)
    {
        $updated = Message::where(function ($query) use ($id) {
            $query->where('receiver_id', $id)->where('sender_id', auth()->id());
        })
            ->orWhere(function ($query) use ($id) {
                $query->where('receiver_id', auth()->id())->where('sender_id', $id);
            })->delete();

        if ($updated > 0) {
            $msg = "Chat clear successfully.";
        } else {
            $msg = "No messages found to clear.";
        }

        return ok($msg);
    }
}
