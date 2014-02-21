<?php

namespace Skimia\AngularBundle\Components\BundleManager;

use Symfony\Component\HttpKernel\Kernel;

class BundleManager {

    public function __construct(Kernel $kernel, $globalConfig, $bundleConfig) {
        $this->_kernel = $kernel;
        $this->_globalConfig = $globalConfig;
        $this->_bundleConfig = $bundleConfig;
    }

    protected $_kernel;
    protected $_globalConfig;
    protected $_bundleConfig;

    /**
     * Can use shortName or Symfony Bundle Name
     */
    public function hasBundle($name) {
        if (isset($this->_globalConfig['bundles'][$name])) {
            return true;
        } else {
            foreach ($this->_bundleConfig as $value) {
                if ($value['short_name'] == $name) {
                    return true;
                }
            }
            return false;
        }
    }

    public function getBundle($name) {
        $config = null;
        if (isset($this->_bundleConfig[$name])) {
            $config = $this->_bundleConfig[$name];
            $config['bundle_name'] = $name;
        } else {
            foreach ($this->_bundleConfig as $key => $value) {
                if ($value['short_name'] == $name) {
                    $config = $value;
                    $config['bundle_name'] = $key;
                }
            }
        }
        if (!isset($config)) {
            return null;
        }
        return new Bundle($this->_kernel, $config);
    }

    public function getBundles() {
        $bundles = array();
        foreach ($this->_bundleConfig as $key=>$value) {
            $bundles[] = $this->getBundle($key);
        }
        return $bundles;
    }

}
