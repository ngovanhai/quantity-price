new Vue({
  el: "#quantity_price",
  components: {
    "collection-rule": httpVueLoader(
      `admin/version1/components/collection.vue?v=${window.version}`
    ),
    // "import-rule": httpVueLoader(
    //   `admin/version1/components/importRule.vue?v=${window.version}`
    // ),
    "product-rule": httpVueLoader(
      `admin/version1/components/product.vue?v=${window.version}`
    ),
    "variant-rule": httpVueLoader(
      `admin/version1/components/variant.vue?v=${window.version}`
    ),
    limit: httpVueLoader(
      `admin/version1/components/limit.vue?v=${window.version}`
    ),
    settings: httpVueLoader(
      `admin/version1/components/settings.vue?v=${window.version}`
    ),
    "list-rule": httpVueLoader(
      `admin/version1/components/rule.vue?v=${window.version}`
    ),
    "statistic-rule": httpVueLoader(
      `admin/version1/components/statistic.vue?v=${window.version}`
    ),
  },
  data: function () {
    return {
      tab: 1,
      fillterProduct: null,
      fillterCollection: null,
      variants: null,
      allProducts: [],
      idCollectionEdit: [],
    };
  },
  mounted: function () {},
  methods: {
    changeTab: function (tab) {
      let self = this;
      self.tab = tab;
    },
    // showModal: function () {
    //   let self = this;
    //   self.$refs.importExcel.showModal();
    // },
    clickTab: function () {
      let self = this;
      if (self.tab == 2) {
        // self.$refs.listRule.getAllRule();
      } else if (self.tab == 1) {
        // click tab collection
        self.$refs.collectionRule.getAllCollection();
      } else {
        // click tab product
        self.$refs.productRule.getAllProduct();
      }
    },
    fillterProductByCollection(fillterCollection) {
      let self = this;
      self.fillterCollection = fillterCollection;
    },
    getProducts: function (allproducts) {
      let self = this;
      self.allProducts = self.allProducts.concat(allproducts);
    },
  },
});
