<?php

namespace Loevgaard\DoctrineManager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

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
        $this->class = $class;
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
    protected function _create()
    {
        $class = $this->getClass();
        $obj = new $class();
        return $obj;
    }

    /**
     * @param mixed $obj The entity
     */
    protected function _delete($obj)
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
    protected function _update($obj, $flush = true)
    {
        $this->objectManager->persist($obj);

        if($flush) {
            $this->objectManager->flush();
        }
    }

    /*
     * Repository shortcuts
     */
    /**
     * @param $id
     * @return null|object
     */
    protected function _find($id) : ?object
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    protected function _findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null) : array
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $criteria
     * @return null|object
     */
    protected function _findOneBy(array $criteria) : ?object
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @return array
     */
    protected function _findAll() : array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if(!method_exists($this, $name) && in_array($name, ['create', 'update', 'delete', 'find', 'findBy', 'findOneBy', 'findAll'])) {
            $name = '_'.$name;
            return call_user_func_array([$this, $name], $arguments);
        }
    }
}
