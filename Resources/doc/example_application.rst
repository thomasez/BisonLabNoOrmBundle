
Creating an example application
-------------------------------

You have to have configured the NosqlBundle first.

We'll do this quite simple, by some cooking.

This bundle does have another bundle inside it and it's perfect for playing.

Installation
------------

First, create the bundle:

    cd src
    cp -a NosqlBundle/Resources/Examples/ExamplesBundle .

Then, like for any other bundle, include it in your Kernel class::

    public function registerBundles()
    {
        $bundles = array(
            ...

            new RedpillLinpro\ExamplesBundle\RedpillLinproExamplesBundle(),
        );

        ...
    }

Second, we need the web resources available to the public:

Most unixes and set up apaches can do with a symbolic link:

    cd web/bundles
    ln -s ../../src/RedpillLinpro/ExamplesBundle/Resources/public examples


Configuration
-------------

First, 'app/config/config.yml':

.. configuration-block::

    .. code-block:: yaml

      services:

          ...

          contract_manager:
               class: RedpillLinpro\ExamplesBundle\Manager\ExampleManagerMongo
               arguments: [ @simple_mongo ]


Second, 'app/config/routing.yml':

.. configuration-block::

    .. code-block:: yaml

      _example:
          resource: "@RedpillLinproExamplesBundle/Controller/ExampleController.php"
          type:     annotation
          prefix:   /example


And then it might even work.


Cooking
-------

If you want to play directly with it, the whole meat of this is found in 
model/Example.php where you can find the array used for defining the 
"schema" / data array. This is the only place to do this, for now. 

It should of course be handled by depencency injection and configurations
in app/config/ or the bundles config.
