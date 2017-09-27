<?php

namespace Loevgaard\DoctrineManager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testGetClass()
    {
        $className = 'FooBar';
        $manager = $this->getManager($className);
        $this->assertSame($className, $manager->getClass());
    }

    public function testGetClassWithColon()
    {
        $className = 'Foo:Bar';
        $manager = $this->getManager($className);
        $this->assertSame($className, $manager->getClass());
    }

    public function testSetClass()
    {
        $className = 'FooBar';
        $manager = $this->getManager($className);
        $manager->setClass('Bar');
        $this->assertSame('Bar', $manager->getClass());
    }

    public function testGetRepository()
    {
        $manager = $this->getManager('FooBar');
        $this->assertInstanceOf(ObjectRepository::class, $manager->getRepository());
    }

    public function testCreate()
    {
        $manager = $this->getManager(TestEntity::class);
        $this->assertInstanceOf(TestEntity::class, $manager->create());
    }

    public function testDelete()
    {
        $manager = $this->getManager(TestEntity::class);
        $obj = new TestEntity();
        $manager->delete($obj);
        $this->assertTrue(true);
    }

    public function testUpdate()
    {
        $manager = $this->getManager(TestEntity::class);
        $obj = new TestEntity();
        $manager->update($obj, false);
        $this->assertTrue(true);

        $manager->update($obj, true);
        $this->assertTrue(true);
    }

    public function testMagicCall()
    {
        $manager = $this->getManager(TestEntity::class);
        $res = $manager->findAll();
        $this->assertSame([], $res);

        $manager->flush();
        $this->assertTrue(true);

        // non existent magic call
        $manager->nonExistent();
        $this->assertTrue(true);
    }

    /**
     * @param string $className
     * @return Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getManager(string $className)
    {
        $classMetaData = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $classMetaData
            ->expects($this->any())
            ->method('getName')
            ->willReturn($className)
        ;

        $repository = $this->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $repository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn([])
        ;

        $objectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $objectManager
            ->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($classMetaData)
        ;
        $objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->with($className)
            ->willReturn($repository)
        ;

        $managerRegistry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $managerRegistry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($objectManager)
        ;

        $manager = $this->getMockForAbstractClass(Manager::class, [$managerRegistry, $className]);
        return $manager;
    }
}
