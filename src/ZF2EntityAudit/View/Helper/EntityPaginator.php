<?php

namespace ZF2EntityAudit\View\Helper;

use Zend\View\Helper\AbstractHelper
    , Doctrine\ORM\EntityManager
    , Zend\ServiceManager\ServiceLocatorAwareInterface
    , Zend\ServiceManager\ServiceLocatorInterface
    , Zend\View\Model\ViewModel
    , DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter
    , Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator
    , Zend\Paginator\Paginator
    , ZF2EntityAudit\Entity\AbstractAudit
    ;

final class EntityPaginator extends AbstractHelper implements ServiceLocatorAwareInterface
{
    private $serviceLocator;

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function __invoke($page, $entityClass) {
        $entityManager = $this->getServiceLocator()->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $auditService = $this->getServiceLocator()->getServiceLocator()->get('auditService');

        if (in_array($entityClass, \ZF2EntityAudit\Module::getServiceManager()->get('auditModuleOptions')->getAuditedEntityClasses())) {
            $auditEntityClass = 'ZF2EntityAudit\\Entity\\' . str_replace('\\', '_', $entityClass);
        } else {
            $auditEntityClass = $entityClass;
        }

        $repository = $entityManager->getRepository('ZF2EntityAudit\\Entity\\RevisionEntity');

        $qb = $repository->createQueryBuilder('revisionEntity');
        $qb->orderBy('revisionEntity.id', 'DESC');

        $qb->andWhere('revisionEntity.auditEntityClass = ?1')
            ->setParameter(1, $auditEntityClass);

        $adapter = new DoctrineAdapter(new ORMPaginator($qb));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(20);
        $paginator->setCurrentPageNumber($page);

        return $paginator;
    }
}