# Apirone Laravel Package

## Installation

To install the Apirone Laravel package in your Laravel project, follow these steps:

1. **Require the package using Composer:**

    ```bash
    composer require apirone/apirone-laravel-package
    ```

2. **Publish the configuration file, migrations and assets:**

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

#### Set currencies and create invoice

```php
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
   
   return 'Invoice created';
});
```
#### Get invoice info
```php
Route::get('/invoice-info', function () {
    $apirone = new \Apirone\ApironeManager();

    $invoice = \Apirone\Models\Invoice::first();

    return $apirone->getInvoice($invoice->invoice);
});
```

#### Callback handler
```php
Route::get('/api/callback', function () {
    Log::info('Callback received');

    $manager = new \Apirone\ApironeManager();
    $manager->callbackHandler();

    return response()->json(['status' => 'ok']);
});
```
