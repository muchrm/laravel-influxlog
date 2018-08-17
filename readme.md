##Notic
This is recieve inspiration from  [swisnl/laravel-graylog2](https://github.com/swisnl/laravel-graylog2)
i'm using it on my project 
Don't use me yet.

## Installation

1. Run composer require for this package: `composer require muchrm/laravel-influxlog`
2. Add the service provider to app.php if you don't like auto discovery: `Muchrm\InfluxLog\InfluxLogServiceProvider`
3. Run `php artisan vendor:publish` to publish the config file to ./config/influxlog.php.
4. Configure it to your liking
5. Done!

## Logging exceptions
The default settings enable logging of exceptions. It will add the HTTP request to the GELF message, but it will not add POST values. Check the graylog2.log-requests config to enable or disable this behavior.

## Message Processors 
Processors add extra functionality to the handler. You can register processors by modifying the AppServiceProvider:
```php
public function register()
{
    //...
    InfluxLog::registerProcessor(new \Muchrm\InfluxLog\Processor\ExceptionProcessor());
    InfluxLog::registerProcessor(new \Muchrm\InfluxLog\Processor\RequestProcessor());
    InfluxLog::registerProcessor(new MyCustomProcessor());
    //...
}
```