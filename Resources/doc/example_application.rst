
Setting up the example application
----------------------------------

We do presume you have Symfony set up properly and also the NoOrmBundle.

We'll do this quite simple, by some cooking.

This bundle does have another bundle inside it and it's perfect for playing.

Installation
------------

Zero: Read the NoOrmBundle install doc (index.rst) and set up everything
there, like parameters.ini for the db config.

First, create the bundle::

    mkdir -p src/BisonLab
    cd src/BisonLab
    cp -a ../../vendor/bisonlab/nosql-bundle/BisonLab/NoOrmBundle/Resources/Examples/ExamplesBundle .

    or

    ln -s ../../vendor/bisonlab/nosql-bundle/BisonLab/NoOrmBundle/Resources/Examples/ExamplesBundle .

We need to add the BisonLab namespace in src on top of vendor/bundles in app/autoload.php::

   'BisonLab'    => array(__DIR__.'/../vendor/bundles', __DIR__.'/src'),

Then, like for any other bundle, include it in your Kernel class, usually app/AppKernel::

    public function registerBundles()
    {
        $bundles = array(
            ...

            new BisonLab\ExamplesBundle\BisonLabExamplesBundle(),
        );

        ...
    }

Second, we need the web resources available to the public;

Most unixes and their apaches can do well with a symbolic link::

    cd web/bundles
    ln -s ../../src/BisonLab/ExamplesBundle/Resources/public examples


Configuration
-------------

In case you haven't configured the mongodb setup, we'll start there.

Set up MongoDB and add the settings to ``app/config/parameters.yml``

.. configuration-block ::

    .. code-block :: yaml

        simple_mongo.dbhost: example
        simple_mongo.dbname: example
        simple_mongo.dbuser: example
        simple_mongo.dbpass: example

So, in 'app/config/config.yml', we have to point at the service configuration in our Bundle:

.. configuration-block::

    .. code-block:: yaml

    imports:
        - { resource: parameters.ini }
        - { resource: security.yml }
        - { resource: @BisonLabExamplesBundle/Resources/config/services.yml }

Lastly, 'app/config/routing.yml':

.. configuration-block::

    .. code-block:: yaml

    BisonLabExamplesBundle:
        resource: "@BisonLabExamplesBundle/Controller/"
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
