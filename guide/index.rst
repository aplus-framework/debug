Debug
=====

.. image:: image.png
    :alt: Aplus Framework Debug Library

Aplus Framework Debug Library.

- `Installation`_
- `Exception Handler`_
- `Debugger`_
- `Conclusion`_

Installation
------------

The installation of this library can be done with Composer:

.. code-block::

    composer require aplus/debug

Exception Handler
-----------------

The ExceptionHandler class acts by catching exceptions and shows a screen
according to the environment, production or development.

Optionally, it can receive an instance of a logger to save messages from
exceptions.

In the example below, we see a basic configuration to initialize by setting the
object as an exception handler in a production environment:

.. code-block:: php

    use Framework\Debug\ExceptionHandler;
    use Framework\Log\Loggers\SysLogger;
    
    $logger = new SysLogger();
    
    $exceptionHandler = new ExceptionHandler(
        ExceptionHandler::PRODUCTION,
        $logger
    );
    $exceptionHandler->initialize();

In the ``initialize`` method it is possible to pass a argument to also set the
object as the error handler, which is the default. If you don't want to set it
as an error handler, pass the first argument to false:

.. code-block:: php

    $exceptionHandler->initialize(false);

Production Environment
######################

When the environment is production, a simple screen will appear informing that
something went wrong and, if the logger is set, the log id will appear:

.. code-block:: php

    use Framework\Database\Database; 
    
    $database = new Database('root', 'lupalupa', logger: $logger);

.. image:: img/exception-production.png
    :alt: Aplus Debug - Exception Handler in Production

It is possible to customize this screen by setting a view file using the
``setProductionView`` method. See the original files inside the
**src/Views/exceptions** directory.

Show Log Id
^^^^^^^^^^^

By default, the log id is shown in the production view.

If for some reason you don't want the log id to be shown, disable it as per the
example below:

.. code-block:: php

    $exceptionHandler->setShowLogId(false);

Development Environment
#######################

When the ExceptionHandler environment is set to development, a screen is shown
with much more details about the exception; the message, file location and line
number, trace, server input information, and the latest log.

.. code-block:: php

    $exceptionHandler = new ExceptionHandler(
        ExceptionHandler::DEVELOPMENT,
        $logger
    );

Example:

.. image:: img/exception-development.png
    :alt: Aplus Debug - Exception Handler in Development

The development view can also be customized. Set the file path via the
``setDevelopmentView`` method.

Hidden Inputs
^^^^^^^^^^^^^

All input global variables (``$_COOKIE``, ``$_ENV``, ``$_FILES``, ``$_GET``,
``$_POST`` and ``$_SERVER``) are shown on the exception page under development.

If you want to hide any of them, do so as per the following example:

.. code-block:: php

    $exceptionHandler->setHiddenInputs('$_ENV', '$_POST');

Search Engine
^^^^^^^^^^^^^

On the development page there is a link to search for the exception in a search
engine.

You can choose between several predefined engines:

- `ask <https://www.ask.com/web?q=aplus+framework>`_
- `baidu <https://www.baidu.com/s?wd=aplus+framework>`_
- `bing <https://www.bing.com/search?q=aplus+framework>`_
- `duckduckgo <https://duckduckgo.com/?q=aplus+framework>`_
- `google <https://www.google.com/search?q=aplus+framework>`_
- `yahoo <https://search.yahoo.com/search?p=aplus+framework>`_
- `yandex <https://yandex.com/search/?text=aplus+framework>`_

The default engine is ``google``.

If you want to change to another engine, do as in the example below:

.. code-block:: php

    $exceptionHandler->getSearchEngines()->setCurrent('bing');

Command Line
############

When the exception is thrown on the command line, it will be shown as in the
example below:

.. image:: img/exception-cli.png
    :alt: Aplus Debug - Exception Handler in CLI

Language
########

ExceptionHandler texts can be customized, using an instance of the Language
class, which can be passed by the constructor or the setter:

.. code-block:: php

    use Framework\Language\Language;

    $language = new Language('es');
    $exceptionHandler->setLanguage($language);

Example of the exception page in production with the Spanish language:

.. image:: img/exception-production-es.png
    :alt: Aplus Debug - Exception Handler in Production with Spanish language

JSON Responses
##############

If the server variable ``$_SERVER['HTTP_ACCEPT']`` contains ``application/json``
or the variable ``$_SERVER['HTTP_CONTENT_TYPE']`` starts with ``application/json``
the exception response page will be a page with JSON.

Below is an example of what the body of the responses will look like:

JSON in Production
^^^^^^^^^^^^^^^^^^

The production page is very simple. Remember that the log id can be disabled:

.. code-block:: json

    {
      "status": {
        "code": 500,
        "reason": "Internal Server Error"
      },
      "data": {
        "message": "Something went wrong. Please, back later.",
        "log_id": "632617344ccd"
      }
    }

JSON in Development
^^^^^^^^^^^^^^^^^^^

The development page contains detailed information about the exception:

.. code-block:: json

    {
      "status": {
        "code": 500,
        "reason": "Internal Server Error"
      },
      "data": {
        "exception": "mysqli_sql_exception",
        "message": "Access denied for user 'root'@'localhost'",
        "file": "/var/www/app/vendor/aplus/database/src/Database.php",
        "line": 230,
        "trace": [
          {
            "file": "/var/www/app/vendor/aplus/database/src/Database.php",
            "line": 230,
            "function": "real_connect",
            "class": "mysqli",
            "type": "->"
          },
          {
            "file": "/var/www/app/vendor/aplus/database/src/Database.php",
            "line": 103,
            "function": "connect",
            "class": "Framework\\Database\\Database",
            "type": "->"
          },
          {
            "file": "/var/www/app/public/index.php",
            "line": 19,
            "function": "__construct",
            "class": "Framework\\Database\\Database",
            "type": "->"
          }
        ],
        "log_id": "01884d106dd9"
      }
    }

JSON Flags
^^^^^^^^^^

The default flags for encoding JSON data are:

.. code-block:: php

    $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

You can customize them as per the following example:

.. code-block:: php

    $flags = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT;
    $exceptionHandler->setJsonFlags($flags);

Debugger
--------

The Framework\Debug\Debugger class has methods to help debug and, mainly, render
the debugbar.

.. code-block:: php

    <?php
    require __DIR__ . '/../vendor/autoload.php';

    use Framework\Debug\Debugger;

    $debugger = new Debugger();

    echo $debugger->renderDebugbar();

The first time the debugbar is rendered, it will be shrunk and only the icon
will appear at the bottom left of the screen:

.. image:: img/debugbar-icon.png
    :alt: Aplus Debug - Debugbar Icon

When the icon is clicked, the bar expands to the right of the screen showing the
**info** button:

.. image:: img/debugbar-wide.png
    :alt: Aplus Debug - Debugbar Wide

When **info** is clicked, the panel pops up showing basic information:

.. image:: img/debugbar-info.png
    :alt: Aplus Debug - Debugbar Info

In the code below we show how to add collections, collectors and activity data
in the debugbar:

.. code-block:: php    

    use Framework\Debug\Collector;
    use Framework\Debug\Debugger;
    
    class FooCollector extends Collector
    {
        public function getActivities() : array
        {
            $activities = [];
            foreach ($this->getData() as $index => $data) {
                $activities[] = [
                    'collector' => $this->getName(),
                    'class' => static::class,
                    'description' => 'Activity ' . ($index + 1),
                    'start' => $data['start'],
                    'end' => $data['end'],
                ];
            }
            return $activities;
        }
    
        public function getContents() : string
        {
            return '<p>Collector: ' . $this->getName() . '</p>';
        }
    }
    
    class BarCollector extends FooCollector
    {
    }
    
    $fooCollector = new FooCollector();
    
    $start = microtime(true);
    usleep(100);
    $end = microtime(true);
    $fooCollector->addData([
        'start' => $start,
        'end' => $end,
    ]);
    $start = microtime(true);
    usleep(100);
    $end = microtime(true);
    $fooCollector->addData([
        'start' => $start,
        'end' => $end,
    ]);
    
    $fooCollector2 = new FooCollector('other');
    
    $barCollector = new BarCollector();
    $start = microtime(true);
    usleep(100);
    $end = microtime(true);
    $barCollector->addData([
        'start' => $start,
        'end' => $end,
    ]);
    
    $debugger = new Debugger();
    $debugger->addCollector($fooCollector, 'Collection 1');
    $debugger->addCollector($fooCollector2, 'Collection 1');
    $debugger->addCollector($barCollector, 'Collection 2');

    echo $debugger->renderDebugbar();

Below we have images of the debugbar generated by the code above:

.. image:: img/debugbar-collection.png
    :alt: Aplus Debug - Debugbar Collection

Each collection can have multiple collectors and they will appear in a select at
the top right of the debugbar panel. Note that "other" has been selected in the
image below:

.. image:: img/debugbar-collection-other.png
    :alt: Aplus Debug - Debugbar Collection Other

When a new collection is added, it will appear in the bottom bar, to the right
of the last collection button:

.. image:: img/debugbar-collection-2.png
    :alt: Aplus Debug - Debugbar Collection 2

The activities of all collectors are compared and shown in the panel by clicking
on the **info** button, located at the bottom right of the debugbar.

.. image:: img/debugbar-activities.png
    :alt: Aplus Debug - Debugbar Activities

You can toggle the debugbar by pressing the ``Ctrl + F12`` keys.

Using the ``setDebugbarView`` method you can set a custom debug bar view for
your brand.

Conclusion
----------

Aplus Debug Library is an easy-to-use tool for, beginners and experienced, PHP developers. 
It is perfect for high-level debugging and exception handling. 
The more you use it, the more you will learn.

.. note::
    Did you find something wrong? 
    Be sure to let us know about it with an
    `issue <https://github.com/aplus-framework/debug/issues>`_. 
    Thank you!
