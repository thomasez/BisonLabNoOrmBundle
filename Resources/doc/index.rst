RedpillLinproNosqlBundle
==========================

A Nosql Bundle for Symfony 2. 

Author: Thomas Lundquist <thomasez@redpill-linpro.com>

Project started june 2011. 

This is yet another way to access MongoDB (for now) in Symfony 2 and was
created because of the disliking of the concept of the Doctrine ODM.

This is way smaller, simpler and definately not enterprisey but seems to 
work on schema less databases. 

The concept is to use MongoDB for storage of array objects with as little
work as possible to maintain the arrays.

Installation
------------

`Download`_ the bundle and put it under the ``RedpillLinpro\\NosqlBundle\\`` namespace.

Since I'd have to name it on Github with the complete name and it'll be split 
into RedpillLinpro/NosqlBundle you have to do a quick mkdir and mv yourself.

Usually in src/ do:

    cd src 

    mkdir RedpillLinpro

    mv RedpillLinproNosqlBundle RedpillLinpro/NosqlBundle

You probably does not have one so creating the src/RedpillLinpro directory
and add it to app/autoload.php as any other new namespaces::

    $loader->registerNamespaces(array(
        ...
        'RedpillLinpro'    => __DIR__.'/../src/RedpillLinpro',
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

.. configuration-block::

    .. code-block:: ini

      [parameters]
          ...
          simple_mongo.dbhost = localhost
          simple_mongo.dbname = example
          simple_mongo.dbuser = example
          simple_mongo.dbpass = example



.. configuration-block::

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

