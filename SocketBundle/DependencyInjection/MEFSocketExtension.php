<?php
/**
 * Interacts with the ContainerBuilder to load up the services provided 
 * by the bundle.  Also registers any compiler passes and modifies service definitions
 * where configuration options dictate.
 */

namespace MEF\SocketBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MEFSocketExtension extends Extension
{
    
    /**
     * load function.
     * 
     * @access public
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $server = $container->getDefinition('mef.socket.server');
        
        $client = $container->getDefinition('mef.socket.client');
        
        //if host and port are set directly, that will be used
        if(isset($config['host']) && isset($config['port'])){
            $this->setServerClientCombo($server, $client, $config);
        }//tcp specific settings, web also inherits this unless overriden
        else if(isset($config['tcp'])){
            $this->setServerClientCombo($server, $client, $config['tcp']);
        }
        
        //web setting override
        if(isset($config['web'])){
            $server = $container->getDefinition('mef.websocket.server');
        
            $client = $container->getDefinition('mef.websocket.client');
            
            $this->setServerClientCombo($server, $client, $config['web']);
        }    
        
    }
    
    protected function setServerClientCombo($server, $client, $config)
    {
        
        $server->addMethodCall('setHost', array($config['host']));
        $server->addMethodCall('setPort', array($config['port']));
        
        $client->addMethodCall('setHost', array($config['host']));
        $client->addMethodCall('setPort', array($config['port']));        
        
    }
    
    
}
