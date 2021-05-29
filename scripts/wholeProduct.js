(function () {
    'use strict';
    angular.module("customOrderApp").controller("wholeProductController", wholeProductController);
    wholeProductController.$inject = ["$http", "$scope", "NgTableParams", "$mdDialog", "toaster"];
    function wholeProductController($http, $scope, NgTableParams, $mdDialog, toaster) {
        var vm = this;
        vm.curreny = curency;
        vm.groupTitle = "";
        vm.typeWhole = 0;
        vm.showImagesLoading = false;
        vm.productChoosen = [];
        vm.groups = [
            {
                price: 0,
                number: 0,
            }
        ];
        vm.disabledSubmit = true;
        vm.tableParams = new NgTableParams(
                {page: 1, count: 5},
                {
                    getData: function ($defer, params) {
                        getProductList($defer, params);
                    },
                    counts: [5, 10, 15],
                    paginationMaxBlocks: 3,
                    paginationMinBlocks: 2
                }
        );
        vm.tableParamsCollection = new NgTableParams(
                {page: 1, count: 5},
                {
                    getData: function ($defer, params) {
                        getCollectionList($defer, params);
                    },
                    counts: [5, 10, 15],
                    paginationMaxBlocks: 3,
                    paginationMinBlocks: 2
                }
        );
        vm.calculatePercent = function (variantId, index) {
            for (var i = 0; i < vm.variantList.length; i++) {
                if (vm.variantList[i].id == variantId) {
                    var currentVariant = vm.variantList[i];
                    var price = parseFloat(currentVariant.price);
                    if (price == 0) {
                        vm.groups[index].percent = 0;
                    } else {
                        var newPrice = vm.groups[index].price;
                        vm.groups[index].percent = parseFloat((100 - (newPrice / price) * 100).toFixed(2));
                        break;
                    }
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
                            {price: 0, number: 0}
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
        vm.selectProduct = function (id, product) {
            if (id == '') {
                vm.productChoosen = [];
            } else {
                var listProductID = [];
                var checkExistProduct = in_array(id, vm.productChoosen);
                if (checkExistProduct == false) {
                    vm.productChoosen.push(product);
                    vm.disabledSubmit = false;
                }
            }
        };
        vm.removeProductChoosen = function (id) {
            for (var i = 0; i < vm.productChoosen.length; i++) {
                if (vm.productChoosen[i].id == id) {
                    vm.productChoosen.splice(i, 1);
                }
            }
        }
        vm.saveWholeProduct = function () {
            vm.disabledSubmit = true;
            var data = {};
            data.action = "saveWholeProduct";
            data.shop = shop;
            data.typeWhole = vm.typeWhole
            data.groups = vm.groups
            data.productChoosen = vm.productChoosen;
            $.ajax( {
                url: 'services.php',
                type: 'POST',
                data: data,
                dataType: 'json'
            }).success(function (result) {
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                vm.productChoosen = [];
                vm.disabledSubmit = false;
                toaster.pop({
                        type: 'success',
                        title: 'save success',
                        body: 'save complete',
                        showCloseButton: true
                    });
            }).error(function () {

            });
        };
        vm.addGroupPrice = function () {
            vm.groups.push({price: 0, number: 0, percent: 0, productGroup: 0});
        }
        $scope.showDivAdd = function () {
            $scope.showDivWrapper = !$scope.showDivWrapper;
        }
        $scope.reset = function () {
            vm.productChoosen = [];
        }
        function in_array(id, data) {
            for (var i = 0; i < data.length; i++) {
                if (data[i].id === id) {
                    return true;
                }
            }
            return false;
        }
        function getCollectionList($defer, params) {
            var data = {};
            data.action = "getCollectionList";
            data.shop = shop;
            data.count = params.count();
            data.page = params.page();
            data.filter = params.filter();
            $http.get("services.php", {params: data}).success(function (result) {
                if (typeof result == "string") {
                    result = JSON.parse(result);
                }
                params.total(result.count);
                $defer.resolve(result.collects);
            }).error(function () {
                showError("Error when get collection list", "Server Error");
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

                });
                vm.productList = result;
                console.log(vm.productList);
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
                if (typeof vm.groupsList.groups != 'undefined') {
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