
Installation Symfony 2.1 and newer.
-----------------------------------

This bundle can be found on Pagckagist so all you should have to do for
download is to add ``redpilllinpro/nosql-bundle``` in the require section
of ``composer.json``. For now, the only version available is ``master``.

Run ``php composer.phar update`` and it should be downloaded and added to the autoloader.

You also have to enable the bundle by registering it in ``app/AppKernel.php``::

    $bundles = array(
        // ...
       new RedpillLinpro\NosqlBundle\RedpillLinproNosqlBundle(),
    );



Installation Symfony 2.0
------------------------

`Download`_ the bundle and put it under the ``RedpillLinpro\\NosqlBundle\\`` namespace, usually under vendor/bundles/

Unlucky enough, te author did not tag a version of the bundle when it was made
for Symfony 2.0. The commit ``e2367943302ecea634e40499eb46a9940b8a718a`` should
work.

Since I'd have to name it on Github with the complete name and it'll be split 
into RedpillLinpro/NosqlBundle you have to do a quick mkdir and mv yourself.

Usually in vendor/bundles/ do::

    mkdir RedpillLinpro

    mv RedpillLinproNosqlBundle RedpillLinpro/NosqlBundle

You probably do not have one so creating the vendor/bundles/RedpillLinpro directory and add it to app/autoload.php as any other new namespaces::

    $loader->registerNamespaces(array(
        ...
        'RedpillLinpro'    => __DIR__.'/../vendor/bundles/RedpillLinpro',
    ));

Then, like for any other bundle, include it in your Kernel class::

    public function registerBundles()
    {
        $bundles = array(
            ...

            new RedpillLinpro\NosqlBundle\RedpillLinproNosqlBundle(),
        );

        ...
    }

        );

        ...
    }

        );

        ...
    }


Configuration
-------------

Creating the MongoDB database and add the settings to parameters.ini

.. configuration-block ::

    .. code-block:: ini

      [parameters]
          ...
          simple_mongo.dbhost = localhost
          simple_mongo.dbname = example
          simple_mongo.dbuser = example
          simple_mongo.dbpass = example



.. configuration-block ::

    .. code-block:: yaml

      services:
          simple_mongo:
              class: RedpillLinpro\NosqlBundle\Services\SimpleMongo
                arguments:
                    dbhost: %simple_mongo.dbhost%
                    dbname: %simple_mongo.dbname%
                    dbuser: %simple_mongo.dbuser%
                    dbpass: %simple_mongo.dbpass%


.. _Download: http://github.com/thomasez/RedpillLinproNosqlBundle

