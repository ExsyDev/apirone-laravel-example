<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   $apirone = new \Apirone\ApironeManager();

   $apirone = $apirone->currencies([
       [
           'name' => 'tbtc',
           'destination' => '2NCQrj3y5BqRFGHz8zN7RYxhjHj9eGbkTow',
           'fee' => 'fixed'
       ]
   ]);

   $apirone->createInvoice('tbtc', 1000);
});

Route::get('/invoice', function () {
    $apirone = new \Apirone\ApironeManager();

    $invoice = \Apirone\Models\Invoice::first();

    return $apirone->getInvoiceInfo($invoice->invoice);
});

Route::get('/api/callback', function () {
    Log::info('Callback received');
    Log::info(json_encode(request()->all()));

    //do something with the callback data
    $manager = new \Apirone\ApironeManager();
    $manager->callbackHandler(request()->all()); //will update the invoice status

    return response()->json(['status' => 'ok']);
});
