<?php

namespace edr\SingleInstanceContainer;

use Interop\Container\ContainerInterface;

/**
 * Wraps a container intercepting requests for dependencies and caching the return.
 *
 * This ensures that a single instance of any service ID is only ever returned. This
 * be used with a reflection container to provide zero-configuration, single instance
 * DI.
 */
class SingleInstanceContainer implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $wrapped;
    
    /**
     * @var mixed[]
     */
    private $instances = [];
    
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->wrapped = $container;
    }
    
    /**
     * @inheritdoc
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            $this->instances[$id] = $this->wrapped->get($id);
        }
        
        return $this->instances[$id];
    }
    
    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return $this->wrapped->has($id);
    }
}

