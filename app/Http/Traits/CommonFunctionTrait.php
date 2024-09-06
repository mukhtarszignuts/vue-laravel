<?php

namespace App\Http\Traits;

use Kreait\Firebase\Factory;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Messaging\CloudMessage;

trait CommonFunctionTrait
{
    public function formatNumber($number)
    {
        if ($number >= 1_000_000) {
            return number_format($number / 1_000_000, 1) . 'M';
        } elseif ($number >= 1_000) {
            return number_format($number / 1_000, 1) . 'k';
        } else {
            return $number;
        }
    }

    public function calculateHours($startDate, $endDate)
    {
        $start = Carbon::createFromFormat('Y/m/d', $startDate);
        $end = Carbon::createFromFormat('Y/m/d', $endDate);

        // Calculate the difference in hours
        return $start->diffInHours($end);
    }

    public function calculateDaysLeft($endDate)
    {
        $end = Carbon::createFromFormat('Y/m/d', $endDate)->startOfDay();
        $today = Carbon::now()->startOfDay(); // Current date and time

        // Calculate the difference in days from today
        return $today->diffInDays($end, false); // Set false to get negative value if endDate is in the past
    }

    /**
     * events type
     */
    public function eventType($type)
    {
        switch ($type) {
            case 'P':
                return 'Personal';
                break;
            case 'B':
                return 'Business';
                break;
            case 'F':
                return 'Family';
                break;
            case 'H':
                return 'Holiday';
                break;
            case 'E':
                return 'ETC';
                break;

            default:
                return '';
                break;
        }
    }

    public function sendPushNotification($title,$msg,$device_token)
    {

        $token=$device_token;
        
        $firebase = (new Factory)
        
            ->withServiceAccount(storage_path('app/chat-vue-ab3fb-firebase-adminsdk-nmxhb-179232b4ff.json'));

        $messaging = $firebase->createMessaging();
        
        $message = CloudMessage::withTarget('TOKEN',$token)
            ->withNotification([
            'title' => $title,
            'body' => $msg,
            ]);

        $messaging->send($message);

        return response()->json(['message' => 'Push notification sent successfully']);
    }
}


