var shop = $(".shopName").html();
var curency = $(".currency").html();
function compare(a,b) {
  if (a.title < b.title)
    return -1;
  if (a.title > b.title)
    return 1;
  return 0;
}
(function () {
    'use strict';

    angular.module("customOrderApp", ["ngMaterial", "ngRoute", "ngTable", "toaster"]);
//    angular.module("customOrderApp").config(function ($routeProvider, $locationProvider) {
//        $routeProvider
//                .when('/quantities-break', {
//                    templateUrl: './templates/quantitiesBreak.html',
//                    controller: 'quantitiesBreakController as main'
//                })
//                .when('/limit-order', {
//                    templateUrl: './templates/limitOrder.html',
//                    controller: 'limitOrderController as limit'
//                }).
//                otherwise({
//                    redirectTo: '/quantities-break'
//                });
//    });
})();