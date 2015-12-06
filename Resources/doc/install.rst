
Installation Symfony 2.1 and newer.
-----------------------------------

This bundle can be found on Pagckagist so all you should have to do for
download is to add ``bisonlab/nosql-bundle``` in the require section
of ``composer.json``. For now, the only version available is ``master``.

Run ``php composer.phar update`` and it should be downloaded and added to the autoloader.

You also have to enable the bundle by registering it in ``app/AppKernel.php``::

    $bundles = array(
        // ...
       new BisonLab\NoOrmBundle\BisonLabNoOrmBundle(),
    );



Installation Symfony 2.0
------------------------

`Download`_ the bundle and put it under the ``BisonLab\\NoOrmBundle\\`` namespace, usually under vendor/bundles/

Unlucky enough, te author did not tag a version of the bundle when it was made
for Symfony 2.0. The commit ``e2367943302ecea634e40499eb46a9940b8a718a`` should
work.

Since I'd have to name it on Github with the complete name and it'll be split 
into BisonLab/NoOrmBundle you have to do a quick mkdir and mv yourself.

Usually in vendor/bundles/ do::

    mkdir BisonLab

    mv BisonLabNoOrmBundle BisonLab/NoOrmBundle

You probably do not have one so creating the vendor/bundles/BisonLab directory and add it to app/autoload.php as any other new namespaces::

    $loader->registerNamespaces(array(
        ...
        'BisonLab'    => __DIR__.'/../vendor/bundles/BisonLab',
    ));

Then, like for any other bundle, include it in your Kernel class::

    public function registerBundles()
    {
        $bundles = array(
            ...

            new BisonLab\NoOrmBundle\BisonLabNoOrmBundle(),
        );


Configuration
-------------

Set up MongoDB and add the settings to ``app/config/parameters.yml``

.. configuration-block ::

    .. code-block :: ini

      [parameters]
          ...
          simple_mongo.dbhost = localhost
          simple_mongo.dbname = example
          simple_mongo.dbuser = example
          simple_mongo.dbpass = example



.. configuration-block ::

    .. code-block :: yaml

        simple_mongo.dbhost: example
        simple_mongo.dbname: example
        simple_mongo.dbuser: example
        simple_mongo.dbpass: example


.. _Download: http://github.com/thomasez/BisonLabNoOrmBundle

