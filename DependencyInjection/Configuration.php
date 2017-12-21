<?php

/*
 * This file is part of the PhpMob package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMob\ReCaptchaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Ishmael Doss <nukboon@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dos_recaptcha');

        $rootNode
            ->children()
                ->scalarNode("site_key")->isRequired()->end()
                ->scalarNode("secret_key")->isRequired()->end()
                ->booleanNode("enabled")->defaultTrue()->end()
                ->booleanNode("verify_host")->defaultFalse()->end()
                ->scalarNode("locale_key")->defaultValue("%kernel.default_locale%")->end()
                ->booleanNode("locale_from_request")->defaultFalse()->end()
            ->end()
        ;

        $this->addHttpClientConfiguration($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addHttpClientConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode("http_proxy")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode("host")->defaultValue(null)->end()
                        ->scalarNode("port")->defaultValue(null)->end()
                        ->scalarNode("auth")->defaultValue(null)->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
