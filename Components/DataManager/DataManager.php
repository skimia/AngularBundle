<?php

namespace Skimia\AngularBundle\Components\DataManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

class DataManager{ 

	protected $_container;

    public function __construct(ContainerInterface $container) {
        
        $this->_container = $container;
    }

    protected function getRamMod(){
    	return array(
    		'all'    => apc_fetch('angular_data_all'),
    		'group' => apc_fetch('angular_data_group'),
    		'user'  => apc_fetch('angular_data_user')
    		);
    }

    public function getModifications($time)
    {
    	$mods = array();
    	$servers_mods = $this->getRamMod();
    	//MODIFS ALL
    	if($servers_mods['all'] !== false){
	    	foreach ($servers_mods['all'] as $type => $modifications) {
	    		if(!isset($mods[$type]))
	    			$mods[$type] = array(); 

	    		foreach ($modifications as $id => $info) {
	    			if($time <= $info['time']){
	    				$mods[$type][$id] = $info['mod'];
	    			}
	    		}
	    	}
	    }
	    $user = $this->getUser();
        if(isset($user)){
	    	//MODIFS GROUP
	    	if($servers_mods['group'] !== false){
	    		foreach ($user->getGroups() as $group) {
	    			foreach ($servers_mods['group'][$group->getId()] as $type => $modifications) {
			    		if(!isset($mods[$type]))
			    			$mods[$type] = array(); 

			    		foreach ($modifications as $id => $info) {
			    			if($time <= $info['time']){
			    				$mods[$type][$id] = $info['mod'];
			    			}
			    		}
			    	}
	    		}
	    		/**/
	    	}

	    	//MODIFS USER
	    	if($servers_mods['user'] !== false){
	    		foreach ($servers_mods['user'][$user->getId()] as $type => $modifications) {

		    		if(!isset($mods[$type]))
		    			$mods[$type] = array(); 

			    	foreach ($modifications as $id => $info) {
			    		if($time <= $info['time']){
			    			$mods[$type][$id] = $info['mod'];
			    		}
			    	}
			    }
	    	}
		}
    	return $mods;
    }

    //FOR ALL
    public function createForAll($entity){
    	$this->addDataAll('CREATE',$entity);
    }
    public function deleteForAll($entity){
    	$this->addDataAll('DELETE',$entity);
    }
    public function editForAll($entity){
    	$this->addDataAll('EDIT',$entity);
    }

    protected function addDataAll($modification, $entity){
    	if(isset($entity::$__type) && $entity->getId() != null)
    	{
    		$this->addData('all',$entity::$__type, $modification,$entity->getId());

    	}else
    	{
    		throw new \Exception('addDataAll : entity invalid');
    	}
    }


    //FOR GROUP
    public function createForGroup($group,$entity){
    	$this->addDataGroup($group, 'CREATE', $entity);
    }
    public function deleteForGroup($group,$entity){
    	$this->addDataGroup($group, 'DELETE', $entity);
    }
    public function editForGroup($group,$entity){
    	$this->addDataGroup($group, 'EDIT', $entity);
    }

    protected function addDataGroup($group, $modification, $entity){
    	if(isset($entity::$__type) && $entity->getId() != null && $group->getId() != null)
    	{
    		$this->addData('group',$group->getId(), $entity::$__type, $modification,$entity->getId());

    	}else
    	{
    		throw new \Exception('addDataAll : entity invalid');
    	}
    }

    //FOR USER
    public function createForUser($user,$entity){
    	$this->addDataUser($user, 'CREATE', $entity);
    }
    public function deleteForUser($group,$entity){
    	$this->addDataUser($user, 'DELETE', $entity);
    }
    public function editForUser($group,$entity){
    	$this->addDataUser($user, 'EDIT', $entity);
    }

    protected function addDataUser($user, $modification, $entity){
    	if(isset($entity::$__type) && $entity->getId() != null && $user->getId() != null)
    	{
    		$this->addData('user',$user->getId(), $entity::$__type, $modification,$entity->getId());

    	}else
    	{
    		throw new \Exception('addDataAll : entity invalid');
    	}
    }


    //SUPERFUNCTION
    protected function addData($for,$a,$b,$c,$d= null){
    	switch($for){

    		case 'all':
    			$type = $a;
    			$modif_type = $b;
    			$id = $c;

    			$data = apc_fetch('angular_data_all');

    			if(!isset($data[$type])){
    				$data[$type] = array();
    			}

    			$data[$type][$id] = array('time'=> time(),'mod'=>$modif_type);
    			apc_store('angular_data_all', $data);
    			break;

    		case 'group':
    			$group_id = $a;
    			$type = $b;
    			$modif_type = $c;
    			$id = $d;

    			$data = apc_fetch('angular_data_group');

    			if(!isset($data[$group_id])){
    				$data[$group_id] = array();
    			}

    			if(!isset($data[$group_id][$type])){
    				$data[$group_id][$type] = array();
    			}

    			$data[$group_id][$type][$id] = array('time'=> time(),'mod'=>$modif_type);
				apc_store('angular_data_group', $data);

    			break;

    		case 'user':
    			$user_id = $a;
    			$type = $b;
    			$modif_type = $c;
    			$id = $d;

    			$data = apc_fetch('angular_data_user');

    			if(!isset($data[$user_id])){
    				$data[$user_id] = array();
    			}

    			if(!isset($data[$user_id][$type])){
    				$data[$user_id][$type] = array();
    			}

    			$data[$user_id][$type][$id] = array('time'=> time(),'mod'=>$modif_type);
				apc_store('angular_data_user', $data);
    			break;
    	}
    }
    public function getUser()
	{
		if (!$security = $this->getSecurity()){
			return;
		}
		if (!$token = $security->getToken()) {
			return;
		}
		$user = $token->getUser();
		if (!is_object($user)) {
			return;
		}
		return $user;
	}

	public function getSecurity()
	{
		if ($this->_container->has('security.context')) {
			return $this->_container->get('security.context');
		}
	}
}
