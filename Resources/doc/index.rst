BisonLabNoOrmBundle
==========================

A Nosql Bundle for Symfony 2. 

Authors: 
    Base, Mongo and arraystuff: Thomas Lundquist <github@bisonlab.no>

    Annotations (not longer here): Danel Andre Eikeland <dae@redpill-linpro.com>

Project started june 2011. 

This is yet another way to access MongoDB (for now) in Symfony 2 and was
created because of the disliking of the concept of the Doctrine ODM.

This is way smaller, simpler and definately not enterprisey but seems to 
work on schema less databases.

It's basically an adapter - manager setup with model objects that can be plain
array access or configured objects. They are still meant to be accessed as
arrays, it's just how the objecs are created that differs.

The main adapter is for MongoDB but there are also an example adapter for
MS-SQL (ironical? yup. The author just needed to access an MS-SQL table and
this was the quickest way to handle it.). An adapter for a REST service was
also created but it was way too specific for being a part of this bundle.

A simple example application is available for source based documentation.

