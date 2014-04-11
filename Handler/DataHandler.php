<?php

namespace Skimia\AngularBundle\Handler;

use Doctrine\ORM\Event\LifecycleEventArgs;

class DataHandler
{
	protected $_dataService;

    public function __construct($dataService) {
        
        $this->_dataService = $dataService;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
    	$service = $this->_dataService;
        $entity = $args->getEntity();
        if(!method_exists($entity,'getScope')){
        	$service->createForAll($entity);
        }
        else{
        	$scope = $entity->getScope();
        	switch($scope['for']){
        		case 'all':
        			$service->createForAll($entity);
        			break;
        		case 'group':
        			$service->createForGroup($scope['group'], $entity);
        			break;
        		case 'groups':
        			foreach ($scope['groups'] as  $group) {
        				$service->createForGroup($group, $entity);
        			}  			
        			break;
        		case 'user':
        			$service->createForUser($scope['user'], $entity);
        			break;
        		case 'users':
        			foreach ($scope['users'] as  $user) {
        				$service->createForUser($user, $entity);
        			}  
        			break;
        	}
        }

    }

    public function postUpdate(LifecycleEventArgs $args)
    {
    	$service = $this->_dataService;
        $entity = $args->getEntity();
        if(!method_exists($entity,'getScope')){
        	$service->editForAll($entity);
        }
        else{
        	$scope = $entity->getScope();
        	switch($scope['for']){
        		case 'all':
        			$service->editForAll($entity);
        			break;
        		case 'group':
        			$service->editForGroup($scope['group'], $entity);
        			break;
        		case 'groups':
        			foreach ($scope['groups'] as  $group) {
        				$service->editForGroup($group, $entity);
        			}  			
        			break;
        		case 'user':
        			$service->editForUser($scope['user'], $entity);
        			break;
        		case 'users':
        			foreach ($scope['users'] as  $user) {
        				$service->editForUser($user, $entity);
        			}  
        			break;
        	}
        }

    }

    public function postDelete(LifecycleEventArgs $args)
    {
    	$service = $this->_dataService;
        $entity = $args->getEntity();
        if(!method_exists($entity,'getScope')){
        	$service->deleteForAll($entity);
        }
        else{
        	$scope = $entity->getScope();
        	switch($scope['for']){
        		case 'all':
        			$service->deleteForAll($entity);
        			break;
        		case 'group':
        			$service->deleteForGroup($scope['group'], $entity);
        			break;
        		case 'groups':
        			foreach ($scope['groups'] as  $group) {
        				$service->deleteForGroup($group, $entity);
        			}  			
        			break;
        		case 'user':
        			$service->deleteForUser($scope['user'], $entity);
        			break;
        		case 'users':
        			foreach ($scope['users'] as  $user) {
        				$service->deleteForUser($user, $entity);
        			}  
        			break;
        	}
        }

    }
}