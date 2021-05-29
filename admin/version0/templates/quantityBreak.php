<div class="col-xs-12" style="padding:0 0 10px 0;">
    <div class="col-md-4">
        <h3 class="page-heading">Product List</h3>
        <div class="app-content">
            <div class="product-list-filter">
                <b-form-fieldset horizontal label="Rows per page" class="col-12">
                    <b-form-select :options="[{text:5,value:5},{text:10,value:10},{text:15,value:15}]" v-model="perPage">
                    </b-form-select>
                </b-form-fieldset>
                <b-form-fieldset horizontal label="Filter" class="col-12">
                    <b-form-input v-model="filter" placeholder="Type to Search"></b-form-input>
                </b-form-fieldset>
            </div>
            <b-table striped hover responsive
                :items="products" 
                :fields="fields" 
                :current-page="currentPage" 
                :per-page="perPage" 
                :filter="filter">
                <template slot="title" class="col-8" slot-scope="row" @click.stop="info(row.item, row.index, $event.target)">
                    <img v-if="row.item.imageUrl" class="productImg" :src="row.item.imageUrl">{{row.value}}
                </template>
                <template slot="id" slot-scope="row" class="col-4">
                    <b-button size="sm" @click.stop="chooseProductForGetVariants(row.item, $event.target)" class="mr-1">
                        Choose product
                    </b-button>
                </template>
            </b-table>

            <div class="product-list-pagination">
                <b-pagination size="md" :total-rows="products.length" :per-page="perPage" v-model="currentPage" />
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <h3 class="page-heading">Variant</h3>
        <div class="app-content">
            <div>
                <div v-for="(item, index) of variantList">
                    <input type="radio" :id="item.id" :value="item.id" v-model="selectedVariant" v-on:change="selectVariantById(item.id,index)">&nbsp;<label :for="item.id">{{item.title}}&nbsp;-&nbsp;{{item.price}}&nbsp;{{currency}}</label>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <h3 class="page-heading">Quantity Price</h3>
        <div class="app-content">
            <table style="width: 100%">
                <tr>
                    <th style="width: 30%">Quantity</th>
                    <th style="width: 30%">Price({{currency}})</th>
                    <th style="width: 30%">
                        Percent
                    </th>
                    <th style="width: 5%"></th>
                    <th style="width: 5%"></th>
                </tr>
                <tr v-for="(item, index) of groups">
                    <td style="width: 30%">
                        <label></label>
                        <input type="number" v-model="item.number" class="customInputPriceGroup form-control">&nbsp;<span class="andAbove">and above</span>
                    </td>
                    <td style="width: 30%">
                        <input type="number" v-model="item.price" class="customInputPrice form-control" v-on:change="calculatePercent(selectedVariant,index)">
                    </td>
                    <td style="width: 30%">
                        <input type="number" v-model="item.percent" class="customInputPrice form-control" v-on:change="calculatePrice(selectedVariant,index)">
                    </td>
                    <td style="width: 5%"></td>
                    <td style="width: 5%">
                        <button style="padding:5px;" class="btn btn-danger" v-on:click="deleteGroup(index,groups)"><i class="material-icons">delete</i></button>
                    </td>
                </tr>
                <tr>
                    <td><button style="padding:5px;" v-on:click="addGroupPrice(groups)" class="btn btn-success"><i class="material-icons ng-scope">add</i></button></td>
                </tr>
                <tr>
                    <td>
                        <label>Product Group</label>
                        <select v-model="productGroup" class="form-control" style="margin: 0;">
                            <option v-for="(item, index) of productGroups" :value="item.id">{{ item.title }}</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><button class="btn btn-primary" v-on:click="saveGroupPrice">Save</button></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="col-xs-12" style="padding:0;">
    <div class="col-md-4">
        <h3 class="page-heading">Product Group</h3>
        <div class="app-content">
            <input v-model="newProductGroup" placeholder="Group Title" class="form-control" type="text" style="width: auto;float: left;">&nbsp;&nbsp;
            <button class="btn btn-primary" v-on:click="addProductGroup">Save</button>
        </div>
    </div>
</div>