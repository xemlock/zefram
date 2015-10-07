# Zefram Application

## Application config

### Container class

    container = CONTAINER_CLASS

Container can also be set up via `setContainer()` method. It accepts either container class name or a container object. If the container has already been retrieved (via call to `getContainer()`) the former has no effect. If, however, the container is not yet instantiated, the provided class name will be used.

To revert to built-in ZF container set `Zend_Registry` as container class name in `application.ini`.


## Resources

Resources are all that during bootstrap will be written to application container. These can be any values, not necessarily objects.

Resources can be divided in three groups: class resources, plugin resources and raw resources.

Resources that are not recognized as plugin resources will be added to the container as-is during the bootstrapping. Such resources that have the same name as existing class resources will be ignored.

To maintain parent logic plugin and raw resources can be registered in bootstrap via `registerPluginResource` method. If a resource name cannot be recognized as a valid plugin name, the resource value will be stored in a separate collection. To disable this setting set `disableRawResources` option to `TRUE`.

## Pre-bootstrap sequence

1. load modules - module bootstraps are initialized, their configs are retrieved

2. module configs are merged with the application configs

## Bootstrap sequence

1. raw resources are written to the container

2. class and plugin resources are bootstrapped
