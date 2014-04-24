angular.module('angular-repo',['ng'])
.service('$repo', function($http, $injector, $q, $auth,$rootScope){

	var that = this;
	that.getRelations = function(entity){
		var array = [];
		angular.forEach(skimia_bdd_relations, function(relation){
			if(relation.entity_a == entity)
				array.push(relation.field_a);
			if(relation.entity_b == entity)
				array.push(relation.field_b);
		});
		return array;
	};
	that.repository = {
		arrangeRelations: function(){

			angular.forEach(skimia_bdd_relations, function(relation){

				var pass1_entities = that.repository.getResource(relation.entity_a);

				angular.forEach(pass1_entities, function(entity){

					switch(relation.type){
							case 'MO':
								//ADD
								if(angular.isDefined(entity[relation.field_a])){
									if(angular.isObject(entity[relation.field_a][relation.field_b])){
										var array = [];
										angular.forEach(entity[relation.field_a][relation.field_b],function(value){
											array.push(value);
										});
										entity[relation.field_a][relation.field_b] = array;
									}
									if(!angular.isDefined(entity[relation.field_a][relation.field_b]))
										entity[relation.field_a][relation.field_b] = [];

									//VERIF HAS
									if(!contains(entity[relation.field_a][relation.field_b],entity)){
										entity[relation.field_a][relation.field_b].push(entity);
										that.repository[relation.entity_b][entity[relation.field_a].id] = entity[relation.field_a];
									}
								}
								break;
							case 'MM':
								angular.forEach(entity[relation.field_a], function(entity_e){
									if(angular.isObject(entity_e[relation.field_b])){
										var array = [];
										angular.forEach(entity_e[relation.field_b],function(value){
											array.push(value);
										});
										entity_e[relation.field_b] = array;
									}
									if(!angular.isDefined(entity_e[relation.field_b]))
										entity_e[relation.field_b] = [];
									//VERIF HAS
									if(!contains(entity_e[relation.field_b],entity)){
										entity_e[relation.field_b].push(entity);
										that.repository[relation.entity_b][entity_e.id] = entity_e;
									}
								});
								break;
							case 'OM':
								//SET
								

								angular.forEach(entity[relation.field_a], function(entity_e){
									entity_e[relation.field_b] = entity;
									that.repository[relation.entity_b][entity_e.id] = entity_e;
								});

								break;
						}
						that.repository[relation.entity_a][entity.id] = entity;
				});


				var pass2_entities = that.repository.getResource(relation.entity_b);

				angular.forEach(pass2_entities, function(entity){

					switch(relation.type){
						case 'MO':
								//SET
								angular.forEach(entity[relation.field_b], function(entity_e){
									entity_e[relation.field_a] = entity;
									that.repository[relation.entity_a][entity_e.id] = entity_e;
								});
								break;
							case 'MM':
								angular.forEach(entity[relation.field_b], function(entity_e){
									if(angular.isObject(entity_e[relation.field_a])){
										var array = [];
										angular.forEach(entity_e[relation.field_a],function(value){
											array.push(value);
										});
										entity_e[relation.field_a] = array;
									}
									if(!angular.isDefined(entity_e[relation.field_a]))
										entity_e[relation.field_a] = [];
									//VERIF HAS
									if(!contains(entity_e[relation.field_a],entity)){
										entity_e[relation.field_a].push(entity);
										that.repository[relation.entity_a][entity_e.id] = entity_e;
									}
									
								});
								break;
							case 'OM':
								if(angular.isObject(entity[relation.field_b][relation.field_a])){
									var array = [];
									angular.forEach(entity[relation.field_b][relation.field_a],function(value){
										array.push(value);
									});
									entity[relation.field_b][relation.field_a] = array;
								}
								//ADD
								if(!angular.isDefined(entity[relation.field_b][relation.field_a]))
									entity[relation.field_b][relation.field_a] = [];

								//VERIF HAS
								if(!contains(entity[relation.field_b][relation.field_a], entity)){
									entity[relation.field_b][relation.field_a].push(entity);

									that.repository[relation.entity_a][entity[relation.field_b].id] = entity[relation.field_b];
								}
								break;
						}
						that.repository[relation.entity_b][entity.id] = entity;
				});








			});

			/*angular.forEach(that.repository,function(list,repo){
				if(that.hasModel(repo)){
					angular.forEach(list,function(model,id){
						var relations = that.getRelations(repo);
						angular.forEach(relations,function(r){
	
							if(angular.isDefined(model[r])){
							
								if(angular.isArray(model[r])){
									var array = [];
									angular.forEach(model[r], function(model_value){
										array.push(that.repository[model_value.__type][model_value.id]);
									});
									model[r] = array;
								}
								else{
									model[r] = that.repository[model[r].__type][model[r].id];
								}
							}
						});
						that.repository[repo][id] = model;
					});
					
				}
			});*/
		},
		getResource : function(type,id){
			if(this.hasResource(type,id)){
				if(angular.isDefined(id)){

					return that.model(type).entity(this[type][id]);
				}else{
					return this[type];
				}
			}
			return null;
		},
		hasResource : function(type,id){
			if(!angular.isDefined(that.repository[type])){
				return false;
			}
			if(angular.isDefined(id) && !angular.isDefined(that.repository[type][id])){
				return false;
			}
			return true;
		},
		addResources : function(array, model){
			if(array.length != null){
				angular.forEach(array,function(entity){
					if(angular.isDefined(entity.id))
						that.repository.addResource( model,entity.id,entity);
					else
						that.repository.addResource( model,entity.commit.author.date,entity);
				});
			}else{
				throw new Error("InvalidParameter");
			}
		},
		addResource : function(type,id,data){
			if(angular.isObject(type)){
				data = type.entity(data);
				type = type.$type;
			}
			if(!angular.isDefined(that.repository[type])){
				that.repository[type] = {};
			}

			data.__repolastchange = new Date().getTime();
			if(angular.isDefined(that.repository[type][id]))
				that.repository[type][id] = angular.extend(that.repository[type][id], data);
			else
				that.repository[type][id] = data;
			angular.forEach(data,function(value){
				//Single
				if(angular.isDefined(value) && value != null)
				{
					if(angular.isDefined(value.id) && angular.isDefined(value.__type) && !that.repository.hasResource(value.__type,value.id)){
						that.repository.addResource(that.hasModel(value.__type) ? that.model(value.__type) : value.__type, value.id, value);
					}
					//array
					if(value.length != null){

						angular.forEach(value,function(entity){
							if(angular.isDefined(entity.id) && angular.isDefined(entity.__type) && !that.repository.hasResource(entity.__type,entity.id)){
								that.repository.addResource(that.hasModel(entity.__type) ? that.model(entity.__type) : entity.__type, entity.id, entity);
							}
						});
					}
				}
			});

		},
		removeResource : function(type,id){
			if(!that.repository.hasResource(type,id)){
				return false;
			}
			delete this[type][id];
			var removedResources = [];
			angular.forEach(skimia_bdd_relations, function(relation){
				if(relation.entity_a == type)
				{

					entity_name = relation.entity_b;
					field_name = relation.field_b;
					var entities = that.repository.getResource(entity_name);
					angular.forEach(entities, function(entity){
						switch(relation.type){
								case 'OM':
									if(entity[field_name].id == id)
										removedResources.push(entity);
									break;
								case 'MM':
									var arr = [];
									angular.forEach(entity[field_name],function(value,key){
										if(value.id != id){
											arr.push(value);
										}
									});
									entity[field_name] = arr;
									break;
								case 'MO':
									var arr = [];
									angular.forEach(entity[field_name],function(value,key){
										if(value.id != id){
											arr.push(value);
										}
									});
									entity[field_name] = arr;
									break;
							}
							that.repository[entity_name][entity.id] = entity;
					});
				}else if(relation.entity_b == type){
					entity_name = relation.entity_a;
					field_name = relation.field_a;
					var entities = that.repository.getResource(entity_name);
					angular.forEach(entities, function(entity){
						switch(relation.type){
								case 'OM':
									var arr = [];
									angular.forEach(entity[field_name],function(value,key){
										if(value.id != id){
											arr.push(value);
										}
									});
									entity[field_name] = arr;
									break;
								case 'MM':
									var arr = [];
									angular.forEach(entity[field_name],function(value,key){
										if(value.id != id){
											arr.push(value);
										}
									});
									entity[field_name] = arr;
									break;
								case 'MO':
									if(entity[field_name].id == id)
										removedResources.push(entity);
									break;
							}
							that.repository[entity_name][entity.id] = entity;
					});
				}
			});
			angular.forEach(removedResources,function(value){
				that.repository.removeResource(value.__type,value.id);
			});

		}

	};
	that.test = function(){
		//that.repository.removeResource('Bundle',1);
	}
	$rootScope.repo = that.repository;
	$rootScope.loads = {};

	that.addLoads = function($loads){
		that.loads=$loads;
		$rootScope.$broadcast('event:RepoModelsLoaded');
	}


	that.instance = function(model){
		var data = {
			$settings : {
				$url: "",
				$params: {
					id : "@id"
				}
			},
			query : function(callback){
				return that.readCollection(data, callback);
			},
			get : function(id, callback){
				if(angular.isDefined(id))
					return that.readOne(data, id,callback);
				else
					return false;
			},
			find : function(query, callback){
				return that.find(data, query, callback);
			},
			remove : function(id,callback){
				return that.deleteOne(data, id, callback);
			},
			delete : function(id,callback){
				data.remove(id,callback);
			},
			save : function(datas,callback){
				if(angular.isDefined(datas.__id))
					return that.saveOne(data, datas.__id, datas, callback);
				else
					return that.create(data,datas,callback)
			},
			$beginEdit : function(){
				var OBJ  = {}
				OBJ.__form = skimia_forms[data.$type];
				OBJ.__form.values = {};
				angular.forEach(skimia_forms[data.$type],function(value,key){
					if(key.split('_').length > 1){
						OBJ[key.split('_')[0]] = [];
					}
				});
				OBJ.$save = function(callback){
					return data.save(OBJ,callback);
				};
				return OBJ;

			}

		};
		data.entity = function(_data){
			_data.$remove = function(callback){
				return data.remove(_data.id,callback);
			};
			_data.$beginEdit = function(depth){
				var OBJ = deepObjCopy(_data,depth);
				if(angular.isDefined(OBJ.__type)){
					if(angular.isDefined(skimia_forms[OBJ.__type])){
						type = OBJ.__type;
						OBJ.__form = skimia_forms[type];
						OBJ.__form.values = {};
						OBJ.__id = _data.id;
						angular.forEach(OBJ,function(value,key){

							if(key != key.toCamel()&& key !=='__type' && key !=='__form' && key !=='id' && key !=='__id'&& key!=='__repolastchange'){
								OBJ[key.toCamel()] = OBJ[key];
								delete OBJ[key];

							}

						});
						newOBJ = {};
						angular.forEach(skimia_forms[type] ,function(value,key){
							if(angular.isDefined(OBJ[key.split('_')[0]])){
								newOBJ[key.split('_')[0]] = OBJ[key.split('_')[0]];
							}
						});
						newOBJ.__form = skimia_forms[type];
						newOBJ.__id = _data.id;
						newOBJ.id = _data.id;
						newOBJ.$save = function(callback){
							return data.save(newOBJ,callback);
						}
					}
				}
				return newOBJ;
			};
			return _data;
		}
		var model = angular.extend(data,model);
		return model
	};

	that.model = function(name){
		if($injector.has(name)){
			return $injector.get(name);
		}else{
			throw new Error("Model with name '"+name+"' not Found");
		}

		
	};
	that.hasModel = function(name){
		if($injector.has(name)){
			return true;
		}else{
			return false;
		}

		
	};

	that.readCollection = function(model, callback){
		var defered = $q.defer();
		//HAS
		if(that.repository.hasResource(model.$type)){
			var e = that.repository.getResource(model.$type);
			defered.resolve(e);
			if(callback)
				callback(e);
		}
		//LOAD
		else{
			var url = that.collectionUrl(model);
			$http.get(url).success(function(data){

				that.repository.addResources(data,model);
				that.repository.arrangeRelations();
				var e = that.repository.getResource(model.$type);
				defered.resolve(e);
				if(callback)
					callback(e);
			}).
			error(function(data){
				defered.reject("http Collection get '"+model.$type+"' error"+data);
				throw new Error("http Collection get '"+model.$type+"' error"+data);
			});
		}

		return defered.promise;

	};

	that.readOne = function(model, id, callback){
		if(!angular.isDefined(id)){
			return false;
		}
		var defered = $q.defer();

		//HAS
		if(that.repository.hasResource(model.$type,id)){
			e = that.repository.getResource(model.$type,id);
			defered.resolve(e);
			if(callback)
				callback(e);
		}else{
			var url = that.uniqueUrlId(model,id);
			$http.get(url).success(function(data){
				that.repository.addResource(model,data.id,data);
				that.repository.arrangeRelations();
				var e = that.repository.getResource(model.$type,data.id);
				defered.resolve(e);
				if(callback)
					callback(e);
			}).
			error(function(data){
				defered.reject("http One get '"+model.$type+"' error"+data);
				throw new Error("http One get '"+model.$type+"' error"+data);
			});

		}
		//LOAD
		return defered.promise;
	};

	that.find = function(model, query, callback){
		var defered = $q.defer();
		if(!that.repository.hasResource(model.$type)){
			that.readCollection(model).then(function(data){
				return that.find(model,query,callback);
			});
		}else{
			var elements = {};
			angular.forEach(that.repository.getResource(model.$type),function(element){
			if(that.isValid(element,query))
				elements[element.id] = element;
			});
			defered.resolve(elements);
			if(callback)
				callback(elements);
		}
		return defered.promise;
	};

	that.isValid = function(entity,constrain){
		var isValid = true;
		try{
			angular.forEach(constrain,function(value,name){
				if(!isValid)
					return;
				if(name.split('.').length > 1){
					v = entity;
					angular.forEach(name.split('.'),function(arb){
						v = v[arb];

					});
					if(v != value){
						isValid = false;
					}
				}
				else if(entity[name] != value){
					isValid = false;
				}
			});
		}catch (e) {
			return false;
		}
		return isValid;
	};

	that.deleteOne = function(model, id, callback){
		var defered = $q.defer();

		//HAS
		if(that.repository.hasResource(model.$type,id)){
			var url = that.uniqueUrlId(model,id);

			$http({method: 'DELETE', url: url}).success(function(data){
				that.repository.removeResource(model.$type,id);
				defered.resolve(true);
				if(callback)
					callback(true);
			}).
			error(function(data){
				defered.reject("http One delete '"+model.$type+"' error"+data);
				throw new Error("http One delete '"+model.$type+"' error"+data);
			});
		}else{
			throw new Error("http One delete '"+model.$type+"' error");
			var url = that.uniqueUrlId(model,id);
			

		}
		//LOAD
		return defered.promise;
	};

	that.saveOne = function(model, id, data, callback){
		var defered = $q.defer();
		var url = that.uniqueUrlId(model,id);
		data = that.normaliseSave(data);
			$http({method: 'POST', url: url, data: data}).success(function(data){
				that.repository.addResource(model,data.id,data);
				that.repository.arrangeRelations();
				defered.resolve(data);
				if(callback)
					callback(data);
			}).
			error(function(data){
				defered.reject("http One delete '"+model.$type+"' error"+data);
				throw new Error("http One delete '"+model.$type+"' error"+data);
			});
			return defered;
	}
	that.create = function(model, data, callback){
		var defered = $q.defer();
		var url = that.collectionUrl(model);
		data = that.normaliseSave(data);
			$http({method: 'POST', url: url, data: data}).success(function(data){
				that.repository.addResource(model,data.id,data);
				that.repository.arrangeRelations();
				defered.resolve(data);
				if(callback)
					callback(data);
			}).
			error(function(data){
				defered.reject("http One delete '"+model.$type+"' error"+data);
				throw new Error("http One delete '"+model.$type+"' error"+data);
			});
			return defered;
	}

	that.normaliseSave = function(data){
		var newData = {};
		angular.forEach(data.__form,function(type,name){
			console.log(name);
			if(name != 'values'){
				if(type == 'multiselect'){
					var ids = [];
					angular.forEach(data[name], function(value,key){
						ids.push(value.id);
					});
					newData[name] = ids;
				}
				else if(type == 'entity'){
					newData[name] = data[name].id;
				}
				else if(type == 'checkbox'){
					if(name.indexOf('_') > -1){
						var split = name.split('_');
						switch(split.length){
							case 1:
								data[name] = data[split[0]];
								break;
							case 2:
								data[name] = data[split[0]][split[1]];
								break;
							case 3:
								data[name] = data[split[0]][split[1]][split[2]];
								break;
						}
						if(data[name]){
							newData[name] = data[name];
						}
					}else{
						if(data[name]){
							newData[name] = data[name];
						}
					}
					
				}
				else{
					if(name.indexOf('_') > -1){
						var split = name.split('_');
						switch(split.length){
							case 1:
								newData[name] = data[split[0]];
								break;
							case 2:
								newData[name] = data[split[0]][split[1]];
								break;
							case 3:
								newData[name] = data[split[0]][split[1]][split[2]];
								break;
						}
					}else{

						newData[name] = data[name];
					}
				}
			}
		});
		return newData;
	}
	that.collectionUrl = function(model){

		return that.urlEncode(model.$settings.$url);
	};

	that.uniqueUrlId = function(model,id){
		var params = {};
		angular.forEach(model.$settings.$params, function(value,key){
			if(value == "@id"){
				params[key] = id;
			}
			} ) ;
		return that.urlEncode(model.$settings.$url,params);
	};

	that.urlEncode = function(url,params){
		var urlParams = {};
        angular.forEach(url.split(/\W/), function(param){
          if (param === 'hasOwnProperty') {
            throw $resourceMinErr('badname', "hasOwnProperty is not a valid parameter name.");
          }
          if (!(new RegExp("^\\d+$").test(param)) && param &&
               (new RegExp("(^|[^\\\\]):" + param + "(\\W|$)").test(url))) {
            urlParams[param] = true;
          }
        });
        url = url.replace(/\\:/g, ':');

        params = params || {};

        angular.forEach(urlParams, function(_, urlParam){
          	val = params.hasOwnProperty(urlParam) ? params[urlParam] : null;
          	if (angular.isDefined(val) && val !== null) {
            	url = url.replace(new RegExp(":" + urlParam + "(\\W|$)", "g"), val + "$1");
          	} else {
            	url = url.replace(new RegExp("(\/?):" + urlParam + "(\\W|$)", "g"), function(match, leadingSlashes, tail) {
              		if (tail.charAt(0) == '/') {
                		return tail;
              		} else {
                		return leadingSlashes + tail;
              		}
            	});
          	}
        });
        url = url.replace(/\/+$/, '') || '/';
        // then replace collapse `/.` if found in the last URL path segment before the query
        // E.g. `http://url.com/id./format?q=x` becomes `http://url.com/id.format?q=x`
        url = url.replace(/\/\.(?=\w+($|\?))/, '.');
        // replace escaped `/\.` with `/.`
        url = url.replace(/\/\\\./, '/.');
		return url;

	};

	that.load = function(callback){
		var defered = $q.defer();
		var modelsToLoad = [];
		angular.forEach(that.loads, function(models,$for){
			angular.forEach(models,function(modelName){
				var model = that.model(modelName);
				if($for == 'all'){
					modelsToLoad.push(model.$type);
				}
				else{
					if($auth.isGranted($for)){
						modelsToLoad.push(model.$type);
					}
				}
			});
		});
		if(modelsToLoad.length > 0){
			initPercent(20,'Chargement de Données 1 / '+modelsToLoad.length);
			percentFull = 80;
			percentOne = percentFull/modelsToLoad.length;
			modelsLoaded = 0;
			angular.forEach(modelsToLoad,function(modelName){
				that.model(modelName).query(function(){	
					modelsLoaded++;
					initPercent(getPercent()+percentOne,'Chargement de Données '+(modelsLoaded+1)+' / '+modelsToLoad.length);
					if(modelsToLoad.length <= modelsLoaded){
						callback();
						defered.resolve();
					}
				});
			});

		}else
		{
			callback();
			defered.resolve();
		}
		return defered.promise;
	}

})
.service('$auth', function($rootScope, $q, $http, authService, $flash){
	that = this;
    this.setUser = function($user){

    	if($user === true){
    		that.user = {
                name          : 'guest',
                email         : null,
                authenticated : false,
                roles: ['IS_AUTHENTICATED_ANONYMOUSLY'],
                isGranted : function($role){
						return that.isGranted($role);
    				}
            };
            $rootScope.user = that.user;
    	}else{
    		that.user = merge_options(that.user,$user);
    		$rootScope.user = that.user;
    	}
    };
    this.setUser(true);
	this.connected = function(){
		if($rootScope.user.authenticated === true)
			return true;
		else
			return false;
	};

	this.logout = function(){
		$http.get('logout').
            success(function(data, status, headers, config) {
               	that.setUser(true);
            }).
            error(function(data, status, headers, config) {
                console.log(data);
            });
		//send logout request to symfony

	};
	this.connect = function($ident,$password,$remember){
		var data = {
            '_username' : $ident,
            '_password' : $password
        };
        if($remember == true){
        	data['_remember_me'] = 'on';
        }
        $http.post('login_check', data).
        success(function(data, status, headers, config) {
        	that.setUser(data);
        	that.user.authenticated = true;
        	authService.loginConfirmed();
      	}).
        error(function(data, status, headers, config) {
            if(typeof( data['error']) !== "undefined"){
                $flash.add('danger',data['error']);
            }
        });
    },

	this.init = function(){
		var defered = $q.defer();
		$http.get('api/user').
            success(function(data, status, headers, config) {
            	that.setUser(data);
                that.user.authenticated = true;
                defered.resolve(that.user);
            }).error(function(data, status, headers, config) {defered.resolve(that.user);});
        return defered.promise;
	};

	this.isGranted = function($role){
		var ret = false;
		angular.forEach(that.user.roles,function(role){
			if(role == $role)
				ret = true;
		});
        return ret;
    
    };


})

.run(function($location,$repo,$auth,$state,$rootScope,$urlRouter,$templateFactory){
	path = $location.path();
	$location.path('/');	
	
	$rootScope.$on('event:RepoModelsLoaded',function(){$location.path('/');
		initPercent(10,'Chargement du Compte');
		$auth.init().then(function(user){
			initPercent(20,'Chargement des Données');
			$repo.load(function(){}).then(function(){
				$location.path(path);
				$repo.test();
				initPercent(100,'Terminé');

				
			});
		});
	});

})
.factory('authService', ['$rootScope','httpBuffer', function($rootScope, httpBuffer) {
    return {
      /**
       * Call this function to indicate that authentication was successfull and trigger a
       * retry of all deferred requests.
       * @param data an optional argument to pass on to $broadcast which may be useful for
       * example if you need to pass through details of the user that was logged in
       */
      loginConfirmed: function(data, configUpdater) {
        var updater = configUpdater || function(config) {return config;};
        $rootScope.$broadcast('event:auth-loginConfirmed', data);
        httpBuffer.retryAll(updater);
      },

      /**
       * Call this function to indicate that authentication should not proceed.
       * All deferred requests will be abandoned or rejected (if reason is provided).
       * @param data an optional argument to pass on to $broadcast.
       * @param reason if provided, the requests are rejected; abandoned otherwise.
       */
      loginCancelled: function(data, reason) {
        httpBuffer.rejectAll(reason);
        $rootScope.$broadcast('event:auth-loginCancelled', data);
      }
    };
  }])
.config(function($httpProvider){

	var interceptor = ['$rootScope', '$q', 'httpBuffer','$flash', function($rootScope, $q, httpBuffer, $flash) {
      function success(response) {
      	//ANNALYSER
        return response;
      }

      function error(response) {
        if (response.status === 401 && !response.config.ignoreAuthModule) {
          var deferred = $q.defer();
          httpBuffer.append(response.config, deferred);
          $rootScope.$broadcast('event:auth-loginRequired', response);
          return deferred.promise;
        }
        if(response.status === 400){
        	
        	if(response.data.message =="Validation Failed"){
        		$rootScope.$broadcast('event:error-formValidation', response.data.errors.children);
        	}
        }
        // otherwise, default behaviour
        return $q.reject(response);
      }

      return function(promise) {
        return promise.then(success, error);
      };

    }];
    $httpProvider.responseInterceptors.push(interceptor);
})

.factory('httpBuffer', ['$injector', function($injector) {
    /** Holds all the requests, so they can be re-requested in future. */
    var buffer = [];

    /** Service initialized later because of circular dependency problem. */
    var $http;

    function retryHttpRequest(config, deferred) {
      function successCallback(response) {
        deferred.resolve(response);
      }
      function errorCallback(response) {
        deferred.reject(response);
      }
      $http = $http || $injector.get('$http');
      $http(config).then(successCallback, errorCallback);
    }

    return {
      /**
       * Appends HTTP request configuration object with deferred response attached to buffer.
       */
      append: function(config, deferred) {
        buffer.push({
          config: config,
          deferred: deferred
        });
      },

      /**
       * Abandon or reject (if reason provided) all the buffered requests.
       */
      rejectAll: function(reason) {
        if (reason) {
          for (var i in buffer) {
            buffer[i].deferred.reject(reason);
          }
        }
        buffer = [];
      },

      /**
       * Retries all the buffered requests clears the buffer.
       */
      retryAll: function(updater) {
        for (var i in buffer) {
          retryHttpRequest(updater(buffer[i].config), buffer[i].deferred);
        }
        buffer = [];
      }
    };
  }])
.directive('formErrors', function($rootScope) {
    return {
    	restrict:'A',
      	link : function(scope, element, attrs){
      		$rootScope.$on('event:error-formValidation', function(event,error){
        		scope.error = error;
    		});
      	}
    };
  });



function contains(a, entity) {
	var bool = false;
	angular.forEach(a,function(en){

		if(en.id == entity.id){
			bool = true;
		}
	});
	return bool;
}
function merge_options(obj1,obj2){
    var obj3 = {};
    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
    return obj3;
}

function deepObjCopy (dupeObj,deepcheck) {
	deepcheck = typeof deepcheck !== 'undefined' ? deepcheck : 3;
	var retObj = new Object();
	if (typeof(dupeObj) == 'object') {
		if (typeof(dupeObj.length) != 'undefined')
			var retObj = new Array();

		if(deepcheck == 0)
			return retObj;
		for (var objInd in dupeObj) {	
			if (typeof(dupeObj[objInd]) == 'object') {
				retObj[objInd] = deepObjCopy(dupeObj[objInd],deepcheck-1);
			} else if (typeof(dupeObj[objInd]) == 'string') {
				retObj[objInd] = dupeObj[objInd];
			} else if (typeof(dupeObj[objInd]) == 'number') {
				retObj[objInd] = dupeObj[objInd];
			} else if (typeof(dupeObj[objInd]) == 'boolean') {
				((dupeObj[objInd] == true) ? retObj[objInd] = true : retObj[objInd] = false);
			}
		}
	}
	return retObj;
}
String.prototype.toDash = function(){
	return this.replace(/([A-Z])/g, function($1){return "_"+$1.toLowerCase();});
};

String.prototype.toCamel = function(){
	return this.replace(/(_[a-z])/g, function($1){return $1.toUpperCase().replace('_','');});
};