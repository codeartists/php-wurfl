<?php

if (!class_exists('Predis_Client')) {
	require_once dirname(__FILE__) . '/../../redis.php';
}

class WURFL_Storage_Redis extends WURFL_Storage_Base {

    const EXTENSION_MODULE_NAME = "redis";

    private $redis;
    private $expiration;
    private $namespace;

    private $defaultParams = array(
        "namespace" => "wurfl",
        "expiration" => 0
    );

    public function __construct($params=array()) {
        $currentParams = is_array($params) ? array_merge($this->defaultParams, $params) : $this->defaultParams;
        $this->toFields($currentParams);
        $this->initialize();
    }

    private function toFields($params) {
        foreach($params as $key => $value) {
            $this->$key = $value;
        }
    }

    public final function initialize() {
        // global handler
    	global $redis;
    	$this->redis = $redis;
    }

    public function save($objectId, $object) {
    	return ($this->expiration > 0) ?
    		$this->redis->setex($this->encode($this->namespace, $objectId), $this->expiration, serialize($object)) :
    		$this->redis->set($this->encode($this->namespace, $objectId), serialize($object));
    }

    public function load($objectId) {
        $value = $this->redis->get($this->encode($this->namespace, $objectId));
        return $value ? unserialize($value) : null;
    }

    public function clear() {
    	$keys = $this->redis->keys($this->encode($this->namespace, "*"));
    	
    	foreach ($keys as $key) {
    		$this->redis->del($key);
    	}
    }

}
