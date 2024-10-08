<?php

use Illuminate\Support\Facades\Route;

Route::get('/create/invoice', function () {
   $apirone = new \Apirone\ApironeManager();

   $apirone = $apirone->setCurrencies([
       [
           'name' => 'tbtc',
           'destination' => '2NCQrj3y5BqRFGHz8zN7RYxhjHj9eGbkTow',
           'fee' => 'fixed'
       ]
   ]);

    $apirone->createInvoice('tbtc', 1000);

    return "Invoice created";
});

Route::get('/invoice', function () {
    $apirone = new \Apirone\ApironeManager();

    $invoice = \Apirone\Models\Invoice::latest()->first();

    return $apirone->getInvoice($invoice->invoice); //shows invoice info in json
});

Route::get('/api/callback', function () {
    $manager = new \Apirone\ApironeManager();

    $manager->callbackHandler(); //handle callback from apirone
});
