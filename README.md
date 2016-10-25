Notes
====

Tag Install-psr-4
----------------------

This first commit has PSR-4 which is a standard of coding style as well as loads an autoloader.
The auto loader allows us to put our classes such as TestClass.php in `src/LTPP/Inv` and have 
load it by saying:

````
$test = new \LTPP\Inv\TestClass()
````

The Class stored in  `src/LTPP/Inv/TestClass.php` contains

````
<?php

namespace LTPP\Inv;

class TestClass
{
    function hello() { print "hello\n"; }
}
````

Our test program `test.php` contains:

````
<?php

require 'vendor/autoload.php';

$test = new \LTPP\Inv\TestClass();

$test->hello();
````

Note we have added a require for the autoloader, so it will go out and find our `TestClass`


