============
PHP ErrorNot
============

Php notifier for ErrorNot
http://github.com/AF83/ErrorNot

Requirements
============

* PHP 5
* Http_Request2 (http://pear.php.net/package/HTTP_Request2)

Usage
=====

$e = new ErrorNot('http://example.net/', 'my-api-key');
$e->notify('big error');
$e->notify('big error', '2010-03-03T00:00:42+01:00');

ErrorNot can install a custom exception handler:

$e = new ErrorNot('http://example.net/', 'my-api-key', true);


Be carefull about exception handler.

If you call set_exception_handler after create errornot instance, you override 
previous exception_handler.

ErrorNot will save your previous custom exception handler.

function my_exception_handler($e)
{
    echo 'plop';
}

set_exception_handler('my_exception_handler'); // ok
$e = new ErrorNot('http://example.net/', 'my-api-key', true);

$e = new ErrorNot('http://example.net/', 'my-api-key', true);
set_exception_handler('my_exception_handler'); // not ok

$e->installExceptionHandler(); // or reinstall exception handler

TESTS
=====

* simpletest for launching tests (http://www.simpletest.org/)

git submodule update --init

$> php tests/test_errornot.php 
test_errornot.php
OK
Test cases run: 2/2, Passes: 8, Failures: 0, Exceptions: 0


Author
======

Francois de Metz <francois@2metz.fr>
