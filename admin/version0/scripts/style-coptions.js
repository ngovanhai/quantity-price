
(function () {
    'use strict';

    angular.module("customOrderApp").controller("styleOptionController", styleOptionController);
    styleOptionController.$inject = ["$http", "$scope", "NgTableParams", "toaster"];
    function styleOptionController($http, $scope, NgTableParams, toaster) {
        var vm = this;
        vm.saveSettingsDisable = false;
        getSettings();
        vm.saveSettings = function(){
            vm.saveSettingsDisable = true;
            var data = {};
            data.action = "saveSettings";
            data.shop = shop;
            vm.settings.show_heading1 == true ? vm.settings.show_heading = 1 : vm.settings.show_heading = 0;
            vm.settings.use_tag1 == true ? vm.settings.use_tag = 1 : vm.settings.use_tag = 0;
            vm.settings.show_percent1 == true ? vm.settings.show_percent = 1 : vm.settings.show_percent = 0;
            vm.settings.show_total_amount1 == true ? vm.settings.show_total_amount = 1 : vm.settings.show_total_amount = 0;
            vm.settings.limit_on_product1 == true ? vm.settings.limit_on_product = 1 : vm.settings.limit_on_product = 0;
            data.settings = vm.settings;
            $http.get("services.php", {params: data}).success(function(result){
                vm.saveSettingsDisable = false;
                toaster.pop({
                    type: 'success',
                    title: 'Save success',
                    body: 'Save styles success',
                    showCloseButton: true
                });
            }).error(function(){
                vm.saveSettingsDisable = false;
                toaster.pop({
                    type: 'error',
                    title: 'Server error!',
                    body: 'Server error !!!',
                    showCloseButton: true
                });
            });
        };
        function getSettings(){
            var data = {};
            data.shop = shop;
            data.action = "getSettings";
            $http.get("services.php", {params: data}).success(function (result){
                if(typeof result == "string"){
                    result = JSON.parse(result);
                }
                result.table_border_size = parseFloat(result.table_border_size);
                result.table_text_size = parseFloat(result.table_text_size);
                result.limit_text_size = parseFloat(result.limit_text_size);
                result.limit_border_size = parseFloat(result.limit_border_size);
                result.table_heading_size = parseFloat(result.table_heading_size);
                result.input_border_size = parseFloat(result.input_border_size);
                result.show_heading == 1 ? result.show_heading1 = true : result.show_heading1 = false;
                result.use_tag == 1 ? result.use_tag1 = true : result.use_tag1 = false;
                result.show_total_amount == 1 ? result.show_total_amount1 = true : result.show_total_amount1 = false;
                result.show_percent == 1 ? result.show_percent1 = true : result.show_percent1 = false;
                result.limit_on_product == 1 ? result.limit_on_product1 = true : result.limit_on_product1 = false;
                vm.settings = result;
            }).error(function(){
                console.log("server error !!!");
            });
        }
    }
})();