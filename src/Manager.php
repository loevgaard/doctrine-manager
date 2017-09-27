<?php

namespace Loevgaard\DoctrineManager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Util\ClassUtils;

/**
 * @method null|object find($id)
 * @method array findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null)
 * @method null|object findOneBy(array $criteria)
 * @method array findAll()
 */
abstract class Manager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $class;

    public function __construct(ManagerRegistry $registry, $class)
    {
        $this->setClass($class);
        $this->objectManager = $registry->getManagerForClass($this->class);
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->objectManager->getRepository($this->getClass());
    }

    /**
     * @param string $class
     * @return Manager
     */
    public function setClass($class) {
        if (false !== strpos($this->class, ':')) {
            $metadata = $this->objectManager->getClassMetadata($this->class);
            $class = $metadata->getName();
        }
        $this->class = ClassUtils::getRealClass($class);
        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns an instantiated entity class
     *
     * @return mixed
     */
    public function create()
    {
        $class = $this->getClass();
        $obj = new $class();
        return $obj;
    }

    /**
     * @param mixed $obj The entity
     */
    public function delete($obj)
    {
        $this->objectManager->remove($obj);
        $this->objectManager->flush();
    }

    /**
     * Will update/save the entity
     *
     * @param mixed $obj The entity
     * @param bool $flush
     */
    public function update($obj, $flush = true)
    {
        $this->objectManager->persist($obj);

        if($flush) {
            $this->objectManager->flush();
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if(method_exists($this->getRepository(), $name)) {
            return call_user_func_array([$this->getRepository(), $name], $arguments);
        }
        if(method_exists($this->objectManager, $name)) {
            return call_user_func_array([$this->objectManager, $name], $arguments);
        }
        if(!method_exists($this, $name) && in_array($name, ['create', 'update', 'delete', 'find', 'findBy', 'findOneBy', 'findAll'])) {
            $name = '_'.$name;
            return call_user_func_array([$this, $name], $arguments);
        }
    }
}
