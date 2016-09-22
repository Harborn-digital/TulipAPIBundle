<?php

namespace ConnectHolland\TulipAPIBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * TulipAPIExtension loads and manages the bundle configuration.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class TulipAPIExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('tulip_api.url', $config['url']);
        $container->setParameter('tulip_api.version', $config['version']);
        $container->setParameter('tulip_api.client_id', $config['client_id']);
        $container->setParameter('tulip_api.shared_secret', $config['shared_secret']);
        $container->setParameter('tulip_api.file_upload_path', $config['file_upload_path']);
        $container->setParameter('tulip_api.objects', $config['objects']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
