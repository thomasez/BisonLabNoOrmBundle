
Creating an example application
-------------------------------

You have to have configured the NosqlBundle first.

We'll do this quite simple, by some cooking.

This bundle does have another bundle inside it and it's perfect for playing.

Installation
------------

Zero: Read the NosqlBundle install doc (index.rst) and set up everything
there, like parameters.ini for the db config.

First, create the bundle::

    mkdir -p src/RedpillLinpro
    cd src/RedpillLinpro
    cp -a ../../vendor/bundles/RedpillLinpro/NosqlBundle/Resources/Examples/ExamplesBundle .

    or

    ln -s ../../vendor/bundles/RedpillLinpro/NosqlBundle/Resources/Examples/ExamplesBundle .

We need to add the RedpillLinpro namespace in src on top of vendor/bundles in app/autoload.php::

   'RedpillLinpro'    => array(__DIR__.'/../vendor/bundles', __DIR__.'/src'),

Then, like for any other bundle, include it in your Kernel class, usually app/AppKernel::

    public function registerBundles()
    {
        $bundles = array(
            ...

            new RedpillLinpro\ExamplesBundle\RedpillLinproExamplesBundle(),
        );

        ...
    }

Second, we need the web resources available to the public;

Most unixes and set up apaches can do with a symbolic link::

    cd web/bundles
    ln -s ../../src/RedpillLinpro/ExamplesBundle/Resources/public examples


Configuration
-------------

First, 'app/config/config.yml', we have to point at the service configuration in our Bundle:

.. configuration-block::

    .. code-block:: yaml

    imports:
        - { resource: parameters.ini }
        - { resource: security.yml }
        RedpillLinproExamplesBundle:
            resource: @RedpillLinproExamplesBundle/Resources/config/services.yml

Second, 'app/config/routing.yml':

.. configuration-block::

    .. code-block:: yaml

    RedpillLinproExamplesBundle:
        resource: "@RedpillLinproExamplesBundle/Controller/"
        type:     annotation
        prefix:   /example

And then it might even work.

But, what this did was basically point to the config files in the Bundle itself.
You'll find them, or rather service.yml under Resources/config/. The examples uses annotations for the routing to there are no routing file there.


Cooking
-------

If you want to play directly with it, the whole meat of this is found in 
model/Example.php where you can find the array used for defining the 
"schema" / data array. This is the only place to do this, for now. 

It should of course be handled by depencency injection and configurations
in app/config/ or the bundles config.
