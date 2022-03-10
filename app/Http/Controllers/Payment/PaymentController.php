<?php

namespace App\Http\Controllers\Payment;

use App\Exceptions\ApiExceptions\Http404;
use App\Exceptions\ApiExceptions\Http422;
use App\Http\Controllers\Controller;
use App\Mail\SendUserRegistrationMail;
use App\Models\Plan;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function notifyEmail(Request $request) {
        $email = $request->email;

        if (is_null($email)) {
            throw Http422::makeForField('email', 'not-found');
        }

        $user = User::where('email', $request->email)->first();
        if (is_null($user)) {
            throw Http404::makeForField('user', 'not-found');
        }

        if ($user->status_id === 1 && !is_null($user->expirationDate()) && $user->expirationDate() > Carbon::now()) {
            Mail::to($user->email)->send(new SendUserRegistrationMail($user, $user->remember_token));
            return response()->json(true);
        } else {
            throw Http404::makeForField('user', 'not-found');
        }
    }

    public function notify(Request $request) {
        $notificationId = $request->notificationCode;

        if (is_null($notificationId)) {
            throw Http422::makeForField('notificationId', 'not-found');
        }

        $email = env('EMAIL_PAGSEGURO');
        $token = env('TOKEN_PAGSEGURO');
        $url = "https://ws.pagseguro.uol.com.br/v3/transactions/notifications/" . $notificationId;

        $client = new Client();
        $response = $client->request('GET', $url, ['query' => [
            "email" => $email,
            "token" => $token
        ]]);

        $xml = simplexml_load_string($response->getBody(),'SimpleXMLElement',LIBXML_NOCDATA);

        $plan = Plan::where('name', $xml->reference)->first();

        if (is_null($plan)) {
            throw Http404::makeForField('plan', 'not-found');
        }

        // START USER
        $user = User::where('email', $xml->sender->email)->first();
        if (!$user) {
            $user = new User();
            $user->name = 'usuario pendente';
            $user->email = $xml->sender->email;
            $user->status_id = 1; // 1 = PENDENTE
            $user->remember_token = Str::random(10);
            $user->save();
        }
        // END USER

        $sale = Sale::where('code', $xml->code)->first();
        if (!$sale) {
            $sale = new Sale();
            $sale->user_id = $user->id;
            $sale->created_at = $xml->date;
            $sale->code = $xml->code;
            $sale->plan_id = $plan->id;
            $sale->value_total = $xml->grossAmount;
            $sale->final_value = $xml->netAmount;
        }

        $sale->status_id = $xml->status;
        $sale->cancellation_source = $xml->cancellationSource ?? null;
        $sale->updated_at = $xml->lastEventDate;
        $sale->transaction_body = json_encode($xml);
        $sale->save();

        if ($user->status_id === 1 && ($sale->status_id == 3 || $sale->status_id == 4)) {
            Mail::to($user->email)->send(new SendUserRegistrationMail($user, $user->remember_token));
        }

        return response(true, 200);
        // return json_encode($xml);
    }
}
