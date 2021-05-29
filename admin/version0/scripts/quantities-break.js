
(function () {
    'use strict';

    angular.module("customOrderApp").controller("quantitiesBreakController", quantitiesBreakController);
    quantitiesBreakController.$inject = ["$http", "$scope", "NgTableParams", "$mdDialog", "toaster"];
    function quantitiesBreakController($http, $scope, NgTableParams, $mdDialog, toaster) {
        var vm = this;
        vm.curreny = curency;
        vm.groupTitle = "";
        vm.groups = [
            {
                price: 0,
                number: 0,
                percent: 0,
                productGroup: 0
            }
        ];
        vm.disabledSubmit = true;
//        getProductList();
        vm.tableParams = new NgTableParams(
                {page: 1, count: 10},
        {
            getData: function ($defer, params) {
                getProductList($defer, params);
            },
            counts: [10, 15, 50],
            paginationMaxBlocks: 3,
            paginationMinBlocks: 2
        }
        );
        getProductGroup();

        vm.addProductGroup = function () {
            if (vm.groupTitle) {
                var data = {};
                data.shop = shop;
                data.action = "addProductGroup";
                data.groupTitle = vm.groupTitle;
                $http.get("services.php", {params: data}).success(function (result) {
                    toaster.pop({
                        type: 'success',
                        title: 'Add Product Success',
                        body: 'A product group has added, now you can use it on setting quantity break',
                        showCloseButton: true
                    });
                    
                    getProductGroup();
                }).error(function () {

                });
            }
        }

        vm.calculatePercent = function (variantId, index) {
            for (var i = 0; i < vm.variantList.length; i++) {
                if (vm.variantList[i].id == variantId) {
                    var currentVariant = vm.variantList[i];
                    var price = parseFloat(currentVariant.price);
                    if(price == 0){
                        vm.groups[index].percent = 0;
                    } else {
                        var newPrice = vm.groups[index].price;
                        vm.groups[index].percent = parseFloat((100 - (newPrice / price) * 100).toFixed(2));
                        break;
                    }
                }
            }
        };
        vm.calculatePrice = function (variantId, index) {
            for (var i = 0; i < vm.variantList.length; i++) {
                if (vm.variantList[i].id == variantId) {
                    var currentVariant = vm.variantList[i];
                    var price = parseFloat(currentVariant.price);
                    vm.groups[index].price = parseFloat((price - vm.groups[index].percent * price / 100).toFixed(2));
                    break;
                }
            }
        };
        vm.deleteGroup = function (groupId, $index) {
            if (groupId) {
                var data = {};
                var variant = getVariant(vm.variantList, vm.selectedVariant);
                data.shop = shop;
                data.action = "deleteGroup";
                data.id = groupId;
                data.variantId = variant.id;
                data.productId = variant.product_id;

                $http.get("services.php", {params: data}).success(function (result) {
                    vm.groups.splice($index, 1);
                    if (vm.groups.length < 1) {
                        vm.groups = [
                            {
                                price: 0,
                                number: 0
                            }
                        ];
                    }
                    toaster.pop({
                        type: 'success',
                        title: 'Delete success',
                        body: 'delete complete',
                        showCloseButton: true
                    });
                }).error(function () {
                    vm.groups.splice($index, 1);
                    if (vm.groups.length < 1) {
                        vm.groups = [
                            {
                                price: 0,
                                number: 0
                            }
                        ];
                    }
                    toaster.pop({
                        type: 'success',
                        title: 'Delete success',
                        body: 'delete complete',
                        showCloseButton: true
                    });
                });
            } else {
                vm.groups.splice($index, 1);
                if (vm.groups.length < 1) {
                    vm.groups = [
                        {
                            price: 0,
                            number: 0
                        }
                    ];
                }
            }
        };
        vm.selectProduct = function (product) {
            vm.variantList = [];
            vm.variantList = product.variants;
            for (var i = 0; i < vm.variantList.length; i++) {
                if (vm.variantList[i].title.indexOf("and above") > -1) {
                    vm.variantList[i].customVariant = true;
                }
            }
            vm.variantList.sort(compare);
        };
        vm.selectVariant = function () {
            vm.disabledSubmit = false;
            getGroupsList(vm.selectedVariant);
        };
        vm.saveGroupPrice = function () {
            vm.disabledSubmit = true;
            var data = {};
            var variant = getVariant(vm.variantList, vm.selectedVariant);
            data.productId = variant.product_id;
            data.variantTitle = variant.title;
            data.action = "addGroupPrice";
            data.shop = shop;
            for(var i=0;i<vm.groups.length; i++){
                vm.groups[i].productGroup = vm.productGroup;
            }
            data["groups[]"] = vm.groups;
            data.variantId = variant.id;
            data.options = variant.options;
            $http.get("services.php", {params: data}).success(function (result) {
                vm.disabledSubmit = false;
                if (result.variants) {
                    result.variants.forEach(function (item) {
                        vm.variantList.push(item);
                    });
                }
                for (var i = 0; i < vm.variantList.length; i++) {
                    if (vm.variantList[i].title.indexOf("and above") > -1) {
                        vm.variantList[i].customVariant = true;
                    }
                }
                vm.variantList.sort(compare);
                if (result.success == 0) {
                    showError("Error when save price group", result.msg);
                }
                result.groups = JSON.parse(result.groups);
                console.log(result);
                result.groups.forEach(function (item) {
                    item.price = parseFloat(item.price);
                    item.percent = parseFloat(item.percent);
                    item.number = parseFloat(item.number);
                    item.productGroup = parseInt(item.product_group);
                });
                vm.groups = result.groups;
                toaster.pop({
                    type: 'success',
                    title: 'Save success',
                    body: 'Save quantity breaks success',
                    showCloseButton: true
                });
            }).error(function (error) {
                vm.disabledSubmit = false;
                toaster.pop({
                    type: 'error',
                    title: 'Server Error',
                    body: 'Error when save price group',
                    showCloseButton: true
                });
            });
        };
        vm.addGroupPrice = function () {
            vm.groups.push({price: 0, number: 0, percent: 0, productGroup: 0});
        }

        function getProductGroup() {
            var data = {};
            data.shop = shop;
            data.action = "getProductGroup";
            $http.get("services.php", {params: data}).success(function (result) {
                if(result){
                    vm.productGroups = result;
                }
                vm.productGroups.push({id: 0, title: "None"});
            }).error(function () {

            });
        }

        function getProductList($defer, params) {
            var data = {};
            data.action = "getProductList";
            data.shop = shop;
            data.count = params.count();
            data.page = params.page();
            data.filter = params.filter();
            $http.get("services.php", {params: data}).success(function (result) {
                console.log(result);
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                result.products.forEach(function (item) {
                    var tempVariant = item.variants;
                    tempVariant.forEach(function (item1) {
                        item1.options = {};
                        item1.options.option1 = item1.option1;
                        item1.options.option2 = item1.option2;
                        item1.options.option3 = item1.option3;
                    });
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
        function getGroupsList(variantId) {
            var data = {};
            data.action = "getGroupsList";
            data.shop = shop;
            data.id = variantId;
            $http.get("services.php", {params: data}).success(function (result) {
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                var groupList = [];
                vm.groupsList = result;
                if(typeof vm.groupsList.groups != 'undefined'){
                    for (var i = 0; i < vm.groupsList.groups.length; i++) {
                        var temp = {
                            id: parseInt(vm.groupsList.groups[i].id),
                            number: parseInt(vm.groupsList.groups[i].number),
                            price: parseFloat(vm.groupsList.groups[i].price),
                            percent: parseFloat(vm.groupsList.groups[i].percent),
                            productGroup: parseInt(vm.groupsList.groups[i].product_group)
                        };
                        groupList.push(temp);
                        vm.productGroup = parseInt(vm.groupsList.groups[i].product_group);
                    }
                }
                vm.groups = groupList;
                if (vm.groups.length < 1) {
                    vm.groups = [
                        {
                            price: 0,
                            number: 0,
                            percent: 0,
                            productGroup: 0
                        }
                    ];
                    vm.productGroup = 0;
                }
            }).error(function () {
                showError("Error when get groups list", "Server Error");
            });
        }
        function getVariant(variantList, id) {
            if (variantList) {
                for (var i = 0; i < variantList.length; i++) {
                    if (id == variantList[i].id) {
                        return variantList[i];
                        break;
                    }
                }
            }
        }
        function showError(title, msg) {
            $mdDialog.show(
                    $mdDialog.alert()
                    .parent(angular.element(document.querySelector('#popupContainer')))
                    .clickOutsideToClose(true)
                    .title(title)
                    .content(msg)
                    .ariaLabel('Alert Dialog Demo')
                    .ok('Got it!')
                    .targetEvent(msg)
                    );
        }
    }
})();