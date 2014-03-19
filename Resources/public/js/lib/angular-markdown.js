angular.module('angular-markdown', ['ngSanitize']).
  directive('markdown', function ($sanitize) {
    var converter = new Showdown.converter({ extensions: ['twitter','github','prettify']});
    return {
      restrict: 'AE',
      link: function (scope, element, attrs) {
        if (attrs.markdown) {
          scope.$watch(attrs.markdown, function (newVal) {
            var html = newVal ? $sanitize(converter.makeHtml(newVal)) : '';
            element.html(html);
            prettyPrint();
          });
        } else {
          var html = $sanitize(converter.makeHtml(element.text()));
          element.html(html);
          prettyPrint();
        }
      }
    };
  });