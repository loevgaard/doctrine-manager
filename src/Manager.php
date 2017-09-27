<?php

namespace Loevgaard\DoctrineManager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @method null|object find($id)
 * @method array findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null)
 * @method null|object findOneBy(array $criteria)
 * @method array findAll()
 * @method object persist($object)
 * @method flush()
 */
abstract class Manager
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var string
     */
    protected $class;

    public function __construct(ManagerRegistry $registry, string $class)
    {
        $this->managerRegistry = $registry;
        $this->class = $class;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->getRepository(), $name)) {
            return call_user_func_array([$this->getRepository(), $name], $arguments);
        }
        if (method_exists($this->getObjectManager(), $name)) {
            return call_user_func_array([$this->getObjectManager(), $name], $arguments);
        }
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository() : ObjectRepository
    {
        return $this->getObjectManager()->getRepository($this->getClass());
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager() : ObjectManager
    {
        return $this->managerRegistry->getManagerForClass($this->class);
    }

    /**
     * @param string $class
     * @return Manager
     */
    public function setClass(string $class) : Manager
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass() : string
    {
        if (false !== strpos($this->class, ':')) {
            $metadata = $this->getObjectManager()->getClassMetadata($this->class);
            $this->class = $metadata->getName();
        }

        return $this->class;
    }

    /**
     * Returns an instantiated entity class
     *
     * @return mixed
     */
    public function create()
    {
        $obj = new $this->class();
        return $obj;
    }

    /**
     * @param mixed $obj The entity
     */
    public function delete($obj)
    {
        $this->getObjectManager()->remove($obj);
        $this->getObjectManager()->flush();
    }

    /**
     * Will update/save the entity
     *
     * @param mixed $obj The entity
     * @param bool $flush
     */
    public function update($obj, $flush = true)
    {
        $this->getObjectManager()->persist($obj);

        if ($flush) {
            $this->getObjectManager()->flush();
        }
    }
}
