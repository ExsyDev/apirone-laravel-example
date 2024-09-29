# Apirone Laravel Package

## Installation

To install the Apirone Laravel package in your Laravel project, follow these steps:

1. **Require the package using Composer:**

    ```bash
    composer require apirone/apirone-laravel-package
    ```

2. **Publish the configuration file:**

    ```bash
    php artisan vendor:publish --provider="Apirone\ApironeServiceProvider"
    ```

3. **Configure the package:**

   Open the generated configuration file `config/apirone.php` and set your API keys and other necessary configurations.
4. **Migrate the database:**

    ```bash
    php artisan migrate
    ```

## Usage

### Example

#### Create invoice

```php
Route::get('/invoice', function () {
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
```
#### Get invoice info
```php
Route::get('/invoice-info', function () {
    $apirone = new \Apirone\ApironeManager();

    $invoice = \Apirone\Models\Invoice::first();

    return $apirone->getInvoiceInfo($invoice->invoice);
});
```

#### Callback handler
```php
Route::get('/api/callback', function () {
    Log::info('Callback received');
    Log::info(json_encode(request()->all()));

    //do something with the callback data
    $manager = new \Apirone\ApironeManager();
    $manager->callbackHandler(request()->all()); //will update the invoice status

    return response()->json(['status' => 'ok']);
});
```
