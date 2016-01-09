<?php

namespace Syren7\OwncloudApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('syren7_owncloud');
		$rootNode
			->children()
				->scalarNode('host')
					->isRequired()
					->cannotBeEmpty()
				->end()
				->scalarNode('user')
					->isRequired()
					->cannotBeEmpty()
				->end()
				->scalarNode('pass')
					->isRequired()
					->cannotBeEmpty()
				->end()
				->scalarNode('folder')
					->isRequired()
					->cannotBeEmpty()
				->end()
			->end()
		->end();

		return $treeBuilder;
	}
}
