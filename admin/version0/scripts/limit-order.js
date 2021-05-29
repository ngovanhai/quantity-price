
(function () {
    'use strict';

    angular.module("customOrderApp").controller("limitOrderController", limitOrderController);
    limitOrderController.$inject = ["$http", "$scope", "NgTableParams", "toaster"];
    function limitOrderController($http, $scope, NgTableParams, toaster) {
        var vm = this;
        vm.wholeLimit = {
            min: 0,
            max: 0
        };
        vm.disableSaveVariantLimit = false;
//        getProductList();
        vm.tableParams = new NgTableParams(
            {page: 1, count: 5},
            {
                getData: function ($defer, params) {
                  getProductList($defer, params);
                }, 
                counts: [5,15,50],
                paginationMaxBlocks: 3,
                paginationMinBlocks: 2,
            }
        );
        vm.selectProduct = function (product) {
            vm.variantList = [];
            vm.variantList = product.variants;
            var removeList = [];
            for (var i = 0; i < vm.variantList.length; i++) {
                if (vm.variantList[i].title.indexOf("and above") > -1) {
                    removeList.push(i);
                }
            }
            for (var i = removeList.length -1; i >= 0; i--){
                vm.variantList.splice(removeList[i],1);
            }
        }
        vm.selectVariant = function () {
            getLimitList(vm.selectedVariant);
        };
        vm.saveVariantLimit = function () {
            var data = {};
            data.shop = shop;
            data.variant = vm.selectedVariant;
            data.action = "saveVariantLimit";
            if (vm.max > 0 && vm.max <= vm.min || vm.min < 0 || vm.max < 0) {
                toaster.pop({
                    type: 'error',
                    title: 'Data invalid',
                    body: 'Min - Max is not valid !',
                    showCloseButton: true
                });
            } else {
                vm.showError = false;
                if (vm.min) {
                    data.min = vm.min;
                }
                if (vm.max) {
                    data.max = vm.max;
                }
                if (vm.min || vm.max) {
                    vm.disableSaveVariantLimit = true;
                    $http.get("services.php", {params: data}).success(function (result) {
                        vm.disableSaveVariantLimit = false;
                        if (typeof result == "string") {
                            result = JSON.parse(result);
                        }
                        toaster.pop({
                            type: 'success',
                            title: 'Save success',
                            body: 'Save limit product success',
                            showCloseButton: true
                        });
                    });
                } else {
                    toaster.pop({
                        type: 'error',
                        title: 'Data invalid',
                        body: 'Min - Max is not valid !',
                        showCloseButton: true
                    });
                }
            }
        };
        vm.deleteLimit = function(){
            var data = {};
            data.id = vm.selectedVariant;
            data.action = "deleteLimit";
            data.shop = shop;
            $http.get("services.php", {params: data}).success(function (result) {
                toaster.pop({
                    type: 'success',
                    title: 'Delete success',
                    body: 'Delete limit product success',
                    showCloseButton: true
                });
                vm.min = vm.max = null;
                vm.limitList = [];
            }).error(function () {

            });
        }
        vm.saveWholeLimit = function(){
            var data = {};
            data.shop = shop;
            data.action = "saveWholeLimit";
            data.min = vm.wholeLimit.min;
            data.max = vm.wholeLimit.max;
            vm.disableSaveWholeVariantLimit = true;
            $http.get("services.php", {params: data}).success(function (result) {
                vm.disableSaveWholeVariantLimit = false;
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                toaster.pop({
                    type: 'success',
                    title: 'Save success',
                    body: 'Save whole store limit success',
                    showCloseButton: true
                });
            });
        };
        function getProductList($defer, params) {
            var data = {};
            data.action = "getProductList";
            data.shop = shop;
            data.count = params.count();
            data.page = params.page();
            data.filter = params.filter();
            $http.get("services.php", {params: data}).success(function (result) {
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                result.products.forEach(function (item) {
                    item.variants.forEach(function(item2){
                        if(item2.title.indexOf("and above") > -1){
                            item.hasBreak = true;
                        }
                    });
                });
                vm.productList = result;
                vm.product = result[0];
                params.total(result.count);
                $defer.resolve(result.products);
            }).error(function () {
                showError("Error when get product list", "Server Error");
            });
        }
        function getLimitList(variantId) {
            var data = {};
            data.action = "getLimitList";
            data.shop = shop;
            data.id = variantId;
            $http.get("services.php", {params: data}).success(function (result) {
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                vm.limitList = result;
                var temp = true;
                vm.limitList.forEach(function (item) {
                    if (item.min) {
                        vm.min = parseInt(item.min);
                    } else {
                        vm.min = null;
                    }
                    if (item.max) {
                        vm.max = parseInt(item.max);
                    } else {
                        vm.max = null
                    }
                    temp = false;
                });
                if (temp) {
                    vm.min = null;
                    vm.max = null;
                }
            }).error(function () {
                alert("Server Error !!!");
            });
        }
        function showError(msg) {
            vm.invalidMsg = msg;
            vm.showError = true;
        }
    }
})();