<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\ClassificationBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\ClassificationBundle\Model\ContextInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;

abstract class ContextAwareAdmin extends AbstractAdmin
{
    /**
     * @var ContextManagerInterface
     */
    protected $contextManager;

    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName, ContextManagerInterface $contextManager)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->contextManager = $contextManager;
    }

    protected function alterNewInstance(object $object): void
    {
        if ($contextId = $this->getPersistentParameter('context')) {
            $context = $this->contextManager->find($contextId);

            if (!$context) {
                /** @var ContextInterface $context */
                $context = $this->contextManager->create();
                $context->setEnabled(true);
                $context->setId($contextId);
                $context->setName($contextId);

                $this->contextManager->save($context);
            }

            $object->setContext($context);
        }
    }

    protected function configurePersistentParameters(): array
    {
        $parameters = [
            'context' => '',
            'hide_context' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_context', 0) : 0,
        ];

        if ($this->hasSubject()) {
            $parameters['context'] = $this->getSubject()->getContext() ? $this->getSubject()->getContext()->getId() : '';

            return $parameters;
        }

        if ($this->hasRequest()) {
            $parameters['context'] = $this->getRequest()->get('context');

            return $parameters;
        }

        return $parameters;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $options = [];

        if (1 === $this->getPersistentParameter('hide_context')) {
            $options['disabled'] = true;
        }

        $datagridMapper
            ->add('context', null, [], null, $options)
        ;
    }
}
