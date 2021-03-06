<?php
namespace SimpleFW\Loaders;

use SimpleFW\Annotations\Service;
use SimpleFW\Annotations\Controller;

class ComponentLoader
{

    public static function recursiveScan(array $paths = [], &$files = [])
    {
        foreach ($paths as $path) {
            $scan = scandir($path);
            foreach ($scan as $found) {
                if ($found !== "." && $found !== "..") {
                    $p = realpath($path . DIRECTORY_SEPARATOR . $found);
                    if (is_dir($p)) {
                        ComponentLoader::recursiveScan([
                            $p
                        ], $files);
                    } else {
                        $files[] = $p;
                    }
                }
            }
        }
        return $files;
    }

    public static function filter(array $files, $class)
    {
        $filtered = [];
        foreach ($files as $file) {
            $reflectionClass = new \ReflectionClass($file);
            if (count($reflectionClass->getAttributes($class)) > 0) {
                $filtered[] = $file;
            }
        }
        return $filtered;
    }

    public static function filterControllers(array $files)
    {
        return ComponentLoader::filter($files, Controller::class);
    }

    public static function filterServices(array $files)
    {
        return ComponentLoader::filter($files, Service::class);
    }

    public static function resolveServiceName($class)
    {
        $reflectionClass = new \ReflectionClass($class);
        $service = $reflectionClass->getAttributes(Service::class);
        $controller = $reflectionClass->getAttributes(Controller::class);
        if (count($service) == 1) {
            $instance = $service[0]->newInstance();
        } else if (count($controller) == 1) {
            $instance = $controller[0]->newInstance();
        }else{
            return NULL;
        }
        if ($instance->getName() !== NULL) {
            return $instance->getName();
        }
        return end(explode("\\", $class));
    }
    
    public static function resolveServiceArgs($class){
        $reflectionClass = new \ReflectionClass($class);
        $service = $reflectionClass->getAttributes(Service::class);
        $controller = $reflectionClass->getAttributes(Controller::class);
        if (count($service) == 1) {
            $instance = $service[0]->newInstance();
        } else if (count($controller) == 1) {
            $instance = $controller[0]->newInstance();
        }
        
        return $instance->getArgs();
    }
}

