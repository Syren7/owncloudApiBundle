<?php

namespace Syren7\OwncloudApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Syren7\OwncloudApiBundle\DependencyInjection\Syren7OwncloudApiExtension;

class Syren7OwncloudApiBundle extends Bundle {
	public function getContainerExtension() {
		return new Syren7OwncloudApiExtension();
	}
}
