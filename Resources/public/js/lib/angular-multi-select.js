var MultiSelectModule = angular.module('multi-select', []);
MultiSelectModule.directive('multiSelect',['$q','$timeout','$repo', function($q,$timeout,$repo) {
  return {
    restrict: 'E',
    require: 'ngModel',
    scope: {
      selectedLabel: "@",
      availableLabel: "@",
      displayAttr: "@",
      available: "=",
      modelEntity: "@",
      model: "=ngModel"
    },
    template: '<div class="row">' + 
                '<div class="col-md-5 form-group">' + 
                  '<label class="control-label" for="multiSelectSelected">{{ selectedLabel }} ' +
                      '({{ model.length }})</label>' +
                  '<select id="multiSelectSelected" ng-model="selected.current" multiple ' + 
                      'class="pull-left" ng-options="e as e[displayAttr] for e in model">' + 
                      '</select>' + 
                '</div>' + 
                '<div class="col-md-2 form-group" style="display: table-cell;vertical-align: middle;">' + 
                  '<label>&nbsp;</label>' +
                  '<button style="width:100%;margin-bottom:10px;" class="btn btn-primary" ng-click="add()" title="Add selected" ' + 
                      'ng-disabled="selected.available.length == 0">' + 
                    '<i class="fa fa-arrow-left"></i>' + 
                  '</button>' + 
                  '<button style="width:100%" class="btn btn-primary" ng-click="remove()" title="Remove selected" ' + 
                      'ng-disabled="selected.current.length == 0">' + 
                    '<i class="fa fa-arrow-right"></i>' + 
                  '</button>' +
                '</div>' + 
                '<div class="col-md-5 form-group">' +
                  '<label class="control-label" for="multiSelectAvailable">{{ availableLabel }} ' +
                      '({{ available.length }})</label>' +
                  '<select id="multiSelectAvailable" ng-model="selected.available" multiple ' +
                      'ng-options="e as e[displayAttr] for e in available"></select>' +
                '</div>' +
              '</div>',
    link: function(scope, elm, attrs) {
      scope.selected = {
        available: [],
        current: []
      };
      $repo.model(scope.modelEntity).query(function(data){
        var arr = [];

        angular.forEach(data,function(value){
          arr.push(value);
        });
        scope.available = arr;
      });
      
      /* Handles cases where scope data hasn't been initialized yet */
      var dataLoading = function(scopeAttr) {
        var loading = $q.defer();
        if(scope[scopeAttr]) {
          loading.resolve(scope[scopeAttr]);
        } else {
          scope.$watch(scopeAttr, function(newValue, oldValue) {
            if(newValue !== undefined)
              loading.resolve(newValue);
          });  
        }
        return loading.promise;
      };

      /* Filters out items in original that are also in toFilter. Compares by reference. */
      var filterOut = function(original, toFilter) {
        var filtered = [];
        angular.forEach(original, function(entity) {
          var match = false;
          for(var i = 0; i < toFilter.length; i++) {
            if(toFilter[i][attrs.displayAttr] == entity[attrs.displayAttr]) {
              match = true;
              break;
            }
          }
          if(!match) {
            filtered.push(entity);
          }
        });
        return filtered;
      };

      scope.refreshAvailable = function() {
        scope.available = filterOut(scope.available, scope.model);
        scope.selected.available = [];
        scope.selected.current = [];
      }; 

      scope.add = function() {
        scope.model = scope.model.concat(scope.selected.available);
        scope.refreshAvailable();
      };
      scope.remove = function() {
        scope.available = scope.available.concat(scope.selected.current);
        scope.model = filterOut(scope.model, scope.selected.current);
        scope.refreshAvailable();
      };

      $q.all([dataLoading("model"), dataLoading("available")]).then(function(results) {
        $timeout(function(){scope.refreshAvailable()},200);
        $timeout(function(){scope.refreshAvailable()},500);
      });
    }
  };
}]);