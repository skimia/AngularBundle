'use strict';
angular.module('skimia-auth', ['http-auth-interceptor-buffer','message-center'])
  .run(['$rootScope','$http',function($rootScope,$http){
            $rootScope.user = {};
             $rootScope.user.reset = function(){
                $rootScope.user = merge_options($rootScope.user,{
                    name          : 'guest',
                    email         : null,
                    authenticated : false,
                    roles: ['IS_AUTHENTICATED_ANONYMOUSLY']
                });
            };
            $rootScope.user.reset();
            $rootScope.user.connected = function(){
                !$rootScope.user.authenticated === false;
                
            };
            $rootScope.user.isGranted = function($role){
                for (var key in $rootScope.user.roles){
                    if($rootScope.user.roles[key] === $role){
                        return true;
                    }
                }
                return false;
            };

            //construct
            $http.get('api/user', {
                }).
                success(function(data, status, headers, config) {
                    $rootScope.user = merge_options($rootScope.user,data);
                    $rootScope.user.authenticated = true;
                    $rootScope.$broadcast('event:auth-loginConfirmed', data);
                });
   }])
   .
  service('s-auth', ['$http','$timeout','$rootScope','mc-service','authService',
    function ($http,$timeout,$rootScope,$flash, authService) {
      
        return{
            authenticate : function($ident,$password){
                $http.post('login_check', {
                    '_username' : $ident,
                    '_password' : $password
                }).
                    success(function(data, status, headers, config) {
                        $rootScope.user = merge_options($rootScope.user,data);
                        $rootScope.user.authenticated = true;
                        authService.loginConfirmed();
                    }).
                    error(function(data, status, headers, config) {
                        if(typeof( data['error']) !== "undefined"){
                            $flash.add('warning',data['error']);
                        }
                    });
            },
            authenticated : function(){
                return !$rootScope.user.authenticated == false;
            },
            logout : function(){
                $http.get('logout').
                    success(function(data, status, headers, config) {
                        $rootScope.user.reset();
                    }).
                    error(function(data, status, headers, config) {
                        console.log(data);
                    });
            }

            
        };
    
    
    }
  ])
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

  /**
   * $http interceptor.
   * On 401 response (without 'ignoreAuthModule' option) stores the request
   * and broadcasts 'event:angular-auth-loginRequired'.
   */
  .config(['$httpProvider', function($httpProvider) {

    var interceptor = ['$rootScope', '$q', 'httpBuffer', function($rootScope, $q, httpBuffer) {
      function success(response) {
        return response;
      }

      function error(response) {
        if (response.status === 401 && !response.config.ignoreAuthModule) {
          var deferred = $q.defer();
          httpBuffer.append(response.config, deferred);
          $rootScope.$broadcast('event:auth-loginRequired', response);
          return deferred.promise;
        }
        // otherwise, default behaviour
        return $q.reject(response);
      }

      return function(promise) {
        return promise.then(success, error);
      };

    }];
    $httpProvider.responseInterceptors.push(interceptor);
  }]);

  /**
   * Private module, a utility, required internally by 'http-auth-interceptor'.
   */
  angular.module('http-auth-interceptor-buffer', [])

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
  }]);
 

function merge_options(obj1,obj2){
    var obj3 = {};
    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
    return obj3;
}
