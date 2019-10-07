<?php

namespace App\Http\Controllers;

use App\Transaction;
use Illuminate\Http\Request;
use Veritrans_Config;
use Veritrans_Snap;
use Veritrans_Notification;
use Illuminate\Support\Str;
use Auth;
use Illuminate\Support\Facades\Redirect;

class TransactionController extends Controller
{
    /**
     * Class constructor.
     *
     * @param \Illuminate\Http\Request $request User Request
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        // Set midtrans configuration
        Veritrans_Config::$serverKey = config('services.midtrans.serverKey');
        Veritrans_Config::$isProduction = config('services.midtrans.isProduction');
        Veritrans_Config::$isSanitized = config('services.midtrans.isSanitized');
        Veritrans_Config::$is3ds = config('services.midtrans.is3ds');
    }

    public function postTransaction() {

        $transaction = Transaction::create([
            'user_id' => Auth::user()->id,
            'name' => 'lorem ipsum',
            'amount' => '10000',
        ]);

        // Buat transaksi ke midtrans kemudian save snap tokennya.
        $payload = [
            'transaction_details' => [
                'order_id'      => $transaction->id,
                'gross_amount'  => $transaction->amount,
            ],
            'customer_details' => [
                'first_name'    => Auth::user()->id,
                'email'         => Auth::user()->email,
                // 'phone'         => '08888888888',
                // 'address'       => '',
            ],
            'item_details' => [
                [
                    'id'       => rand(1, 100),
                    'price'    => rand(1000, 10000),
                    'quantity' => 1,
                    'name'     => Str::random('16')
                ]
            ]
        ];

        $payment = Veritrans_Snap::createTransaction($payload);
        $transaction->redirect_url = $payment->redirect_url;
        $transaction->snap_token = $payment->token;
        $transaction->save();

        return Redirect::to($payment->redirect_url);
    }

    public function requestUrl(Request $request) {

    }
}
