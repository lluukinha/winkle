<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Sale;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function notify(Request $request) {
        $notificationId = $request->notificationCode;
        $email = env('EMAIL_PAGSEGURO');
        $token = env('TOKEN_PAGSEGURO');
        $url = "https://ws.pagseguro.uol.com.br/v3/transactions/notifications/" . $notificationId;

        $client = new Client();
        $response = $client->request('GET', $url, ['query' => [
            "email" => $email,
            "token" => $token
        ]]);

        $xml = simplexml_load_string($response->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);

        // START USER
        $user = User::where('email', $xml->sender->email)->first();
        if (!$user) {
            $temporaryPassword = Str::random(10);
            $user = new User();
            $user->name = $xml->sender->name;
            $user->email = $xml->sender->email;
            $user->area_code = $xml->sender->phone->areaCode;
            $user->phone = $xml->sender->phone->number;
            $user->status_id = 1; // 1 = PENDENTE
            $user->password = Hash::make($temporaryPassword);
            $user->save();
        }
        // END USER

        $plan = Plan::where('name', $xml->reference)->first();

        $sale = Sale::where('code', $xml->code)->first();
        if (!$sale) {
            $sale = new Sale();
            $sale->user_id = $user->id;
            $sale->created_at = $xml->date;
            $sale->code = $xml->code;
            $sale->plan_id = $plan->id;
            $sale->status_id = $xml->status;
            $sale->cancellation_source = $xml->cancellationSource;
            $sale->updated_at = $xml->lastEventDate;
            $sale->value_total = $xml->grossAmount;
            $sale->final_value = $xml->netAmount;
            $sale->transaction_body = json_encode($xml);
            $sale->save();
            return response(true, 200);
        }

        $sale->status_id = $xml->status;
        $sale->cancellation_source = $xml->cancellationSource;
        $sale->updated_at = $xml->lastEventDate;
        $sale->transaction_body = json_encode($xml);
        $sale->save();

        return response(true, 200);
        // return json_encode($xml);
    }
}
