# PHP ErrorNot

Php notifier for ErrorNot
http://github.com/errornot/ErrorNot

## Requirements

* PHP 5
* Http_Request2 (http://pear.php.net/package/HTTP_Request2)

## Usage

You only want to notify some data:

    $errornot = new Services_ErrorNot('http://example.net/', 'my-api-key');
    $errornot->notify('big error');
    $errornot->notify('big error', '2010-03-03T00:00:42+01:00');
    $errornot->notify('big error', '2010-03-03T00:00:42+01:00', $mybacktrace, $myrequest, $myenvironnement, $mydata);

Notify exception:

    try
    {
        throw new MyException();
    }
    catch (MyException $e)
    {
        $errornot->notifyException($e); // send specific exception
        $errornot->notifyException($e, 'foo'); // send specific exception with extra data
    }

## Non-blocking notifications

It is possible to perform non-blocking notifications. This means that ErrorNot client will not wait
for server response and therefore will not be affected if ErrorNot server is misbehaving.

This can be achieved by using `ErrorNotSocketNonBlockingHttpAdapter` adapter.

    $errornot = new Services_ErrorNot('http://example.net/', 'my-api-key');
    $errornot->setNetworkAdapter(new ErrorNotSocketNonBlockingHttpAdapter());
    $errornot->notify('big error', '2010-03-03T00:00:42+01:00');

## Exception Handler

ErrorNot can also install a custom exception handler:

    $errornot = new Services_ErrorNot('http://example.net/', 'my-api-key', true);

Be carefull about exception handler.

If you call *set_exception_handler* after create errornot instance, you override
previous exception_handler.

ErrorNot will save your previous custom exception handler.

The good way:


    function my_exception_handler($e)
    {
        echo 'plop';
    }

    set_exception_handler('my_exception_handler'); // ok
    $errornot = new Services_ErrorNot('http://example.net/', 'my-api-key', true);

The bad way:

    function my_exception_handler($e)
    {
        echo 'plop';
    }

    $errornot = new Services_ErrorNot('http://example.net/', 'my-api-key', true);
    set_exception_handler('my_exception_handler'); // not ok

You can also reinstall errornot exception handler

    $errornot->installExceptionHandler();


## Tests

Simpletest for launching tests (http://www.simpletest.org/)

    $> git submodule update --init
    $> php tests/test_errornot.php
    test_errornot.php
    OK
    Test cases run: 2/2, Passes: 16, Failures: 0, Exceptions: 0


## Author

François de Metz <francois@2metz.fr>

## License

LGPL v3
