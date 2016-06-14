<?php

namespace edr\SingleInstanceContainerTestCase;

use edr\SingleInstanceContainer\SingleInstanceContainer;
use Interop\Container\ContainerInterface;
use \Interop\Container\Exception\NotFoundException;
use edr\SingleInstanceContainerTestAsset\Foo;

class SingleInstanceContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $alias
     * @param bool   $expected
     *
     * @dataProvider provideHasExpectations
     */
    public function testHas($alias, $expected)
    {
        $wrapped = $this->getMockContainer([
            'foo' => new Foo(),
        ]);
        
        $container = new SingleInstanceContainer($wrapped);
        
        $this->assertSame($expected, $container->has($alias));
    }
    
    public function provideHasExpectations()
    {
        return [
            ['foo', true],
            ['bar', false],
        ];
    }
    
    public function testGet()
    {
        $object  = new Foo();
        
        $wrapped = $this->getMockContainer([
            'foo' => $object
        ]);
        
        $container = new SingleInstanceContainer($wrapped);
        
        $this->assertSame($object, $container->get('foo'));
    }
    
    public function testGetThrowsWhenWrappedContainerNotFound()
    {
        $wrapped = $this->getMockContainer();
        
        $container = new SingleInstanceContainer($wrapped);
        
        $this->expectException(NotFoundException::class);
        
        $container->get('foo');
    }
    
    public function testGetReturnsSameInstance()
    {
        $wrapped = $this->getMockContainer([
            'foo' => function () {
                return new Foo();
            }
        ]);
        
        $this->assertNotSame($wrapped->get('foo'), $wrapped->get('foo'));
        
        $container = new SingleInstanceContainer($wrapped);
        
        $this->assertSame($container->get('foo'), $container->get('foo'));
    }
    
    /**
     * @param array $mapping Id => service key-value mapping
     *
     * @return ContainerInterface
     */
    private function getMockContainer(array $mapping = [])
    {
        $container = $this->createMock(ContainerInterface::class);
        
        $container->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function ($alias) use ($mapping) {
                    if (!isset($mapping[$alias])) {
                        throw new \edr\SingleInstanceContainerTestAsset\NotFoundException();
                    }
                    
                    if (is_callable($mapping[$alias])) {
                        return $mapping[$alias]();
                    }
                    
                    return $mapping[$alias];
                }
            );
        
        $container->expects($this->any())
            ->method('has')
            ->willReturnCallback(
                function ($alias) use ($mapping) {
                    return isset($mapping[$alias]);
                }
            );
        
        return $container;
    }
}
