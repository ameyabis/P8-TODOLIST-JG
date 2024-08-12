<?php

namespace App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/config/{packages}/*.yaml', 'glob');
        $loader->load($this->getProjectDir().'/config/{packages}/'.$this->environment.'/*.yaml', 'glob');
        $loader->load($this->getProjectDir().'/config/services.yaml');
        $loader->load($this->getProjectDir().'/config/{services}_'.$this->environment.'.yaml', 'glob');
    }
}
