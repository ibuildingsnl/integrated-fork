<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SolrBundle;

use Integrated\Bundle\SolrBundle\DependencyInjection\CompilerPass\RegisterConfigFileProviderPass;
use Integrated\Bundle\SolrBundle\DependencyInjection\CompilerPass\RegisterTaskHandlerPass;
use Integrated\Bundle\SolrBundle\DependencyInjection\CompilerPass\RegisterTypePass;
use Integrated\Bundle\SolrBundle\DependencyInjection\IntegratedSolrExtension;
use Integrated\Common\Solr\Search\DependencyInjection\RegisterQueryTypePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class IntegratedSolrBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterConfigFileProviderPass());
        $container->addCompilerPass(new RegisterTypePass());
        $container->addCompilerPass(new RegisterTaskHandlerPass());

        $container->addCompilerPass(new RegisterQueryTypePass(
            'integrated_solr.search.type.dependency_injection_provider',
            'solr_query.type',
            'solr_query.type_extension'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new IntegratedSolrExtension();
    }
}
