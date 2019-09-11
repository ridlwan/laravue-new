<template>
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div v-for="product in products" class="card card-body mb-2 mr-2 product-item">
                    <h4>{{ product.name }}</h4>
                    <p>{{ product.description }}</p>
                    <div class="row">
                        <div class="col-md-6">Stock {{ product.number }}</div>
                        <div class="col-md-6 text-right">Rp {{ product.price }}</div>
                        <p>
                            <button @click="editProduct(product)" class="btn btn-warning">Edit</button>
                            <button @click="deleteProduct(product.id)" class="btn btn-danger">Delete</button>
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <nav>
                        <ul class="pagination">
                            <li v-bind:class="{disabled: pagination.prev_link == 'null' || pagination.prev_link == 'null' + params}" class="page-item"><a href="#" @click="viewProduct(pagination.first_link)" class="page-link">&laquo;</a></li>
                            <li v-bind:class="{disabled: pagination.prev_link == 'null' || pagination.prev_link == 'null' + params}" class="page-item"><a href="#" @click="viewProduct(pagination.prev_link)" class="page-link">&lt;</a></li>
                            <li v-for="n in pagination.pagesArray" v-bind:key="n" v-bind:class="{active: pagination.current_page == n}" class="page-item"><a href="#" @click="viewProduct(pagination.path_page + n + params)" class="page-link">{{ n }}</a></li>
                            <li v-bind:class="{disabled: pagination.next_link == 'null' || pagination.next_link == 'null' + params}" class="page-item"><a href="#" @click="viewProduct(pagination.next_link)" class="page-link">&gt;</a></li>
                            <li v-bind:class="{disabled: pagination.next_link == 'null' || pagination.next_link == 'null' + params}" class="page-item"><a href="#" @click="viewProduct(pagination.last_link)" class="page-link">&raquo;</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-md-4">
                    Page: {{ pagination.from_page }} - {{ pagination.to_page }}
                    Total: {{ pagination.total_page }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" class="form-control" v-model="keyword" v-on:keyup="searchProduct">
            </div>
            <br>
            <form>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" v-model="product.name">
                    <span v-if="errors.name" class="error">{{ errors.name[0] }}</span>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" class="form-control" v-model="product.description">
                    <span v-if="errors.description" class="error">{{ errors.description[0] }}</span>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" class="form-control" v-model="product.price">
                    <span v-if="errors.price" class="error">{{ errors.price[0] }}</span>
                </div>
                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" class="form-control" v-model="product.amount">
                    <span v-if="errors.amount" class="error">{{ errors.amount[0] }}</span>
                </div>
                <button v-if="add" @click.prevent="addProduct" class="btn btn-success">Add Product</button>
                <button v-if="edit" @click.prevent="updateProduct(product.id)" class="btn btn-warning">Update Product</button>
                <button @click.prevent="clearProduct" class="btn btn-info">Clear</button>
            </form>
        </div>
    </div>
</template>


<style>
    .product-item{
        width: 350px;
        float: left;
    }
</style>


<script>
    export default{
        data(){
            return {
                products:[],
                product: {
                    id: '',
                    name: '',
                    description: '',
                    price: '',
                    amount: '',
                },
                add: true,
                edit: false,
                errors: [],
                pagination: {},
                offset: 2,
                keyword: '',
                search: false,
                params: ''
            }
        },
        created(){
            this.viewProduct();
        },
        methods: {
            searchProduct(){
                this.search = true;
                this.viewProduct();
            },            
            viewProduct(paginate){
                if (this.search == true) {
                    paginate = paginate || 'api/products?q=' + this.keyword;
                    this.params = '&q=' + this.keyword;
                } else {
                    paginate = paginate || 'api/products';
                    this.params = '';
                }
                    
                axios.get(paginate)
                .then(response => {
                    this.products = response.data.data;

                    if (!response.data.meta.to) {
                        return [];
                    }
                    
                    var from = response.data.meta.current_page - this.offset;
                    if (from < 1) {
                        from = 1;
                    }
                    
                    var to = from + (this.offset * 2);
                    if (to >= response.data.meta.last_page) {
                        to = response.data.meta.last_page;
                    }
                    
                    var pagesArray = [];
                    while (from <= to) {
                        pagesArray.push(from);
                        from++;
                    }

                    this.pagination = {
                        pagesArray: pagesArray,
                        current_page: response.data.meta.current_page,
                        last_page: response.data.meta.last_page,
                        from_page: response.data.meta.from,
                        to_page: response.data.meta.to,
                        total_page: response.data.meta.total,
                        path_page: response.data.meta.path + '?page=',
                        first_link: response.data.links.first + this.params,
                        last_link: response.data.links.last + this.params,
                        prev_link: response.data.links.prev + this.params,
                        next_link: response.data.links.next + this.params,
                    }
                })
                .catch(error => console.log(error))
            },
            addProduct(){
                axios.post('api/products', {
                    name: this.product.name,
                    description: this.product.description,
                    price: this.product.price,
                    amount: this.product.amount,
                })
                .then(data => {
                    swal.fire({
                        type: 'success',
                        title: 'Successful',
                        text: 'Product has been added',
                        showConfirmButton: false,
                        timer: 1000
                    });
                    this.clearProduct();
                    this.viewProduct();
                })
                .catch(error => {
                    if (error.response.status == 422) {
                        this.errors = error.response.data.errors
                    }
                })
            },
            editProduct(product){
                this.errors = []
                this.add = false;
                this.edit = true;
                this.product.id = product.id;
                this.product.name = product.name;
                this.product.description = product.description;
                this.product.price = product.price;
                this.product.amount = product.amount;
            },
            updateProduct(id){
                axios.put('/api/products/' + id, {
                    name: this.product.name,
                    description: this.product.description,
                    price: this.product.price,
                    amount: this.product.amount,
                })
                .then(data => {
                    swal.fire({
                        type: 'success',
                        title: 'Successful',
                        text: 'Product has been updated',
                        showConfirmButton: false,
                        timer: 1000
                    });
                    this.clearProduct();
                    this.viewProduct();
                })
                .catch(error => {
                    if (error.response.status == 422) {
                        this.errors = error.response.data.errors
                    }
                })
            },
            deleteProduct(id){
                swal.fire({
                    title: 'Are you sure?',
                    text: 'Product will be deleted',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        axios.delete('/api/products/' + id)
                        .then(data => {
                            swal.fire({
                                type: 'success',
                                title: 'Successful',
                                text: 'Product has been deleted',
                                showConfirmButton: false,
                                timer: 1000
                            });
                            this.clearProduct();
                            this.viewProduct();
                        })
                        .catch(error => {
                            swal.fire({
                                type: 'error',
                                title: 'Failed',
                                text: 'Product failed to delete',
                                showConfirmButton: false,
                                timer: 1000
                            });
                        });
                    }
                })
            },
            clearProduct(){
                this.errors = []
                this.add = true;
                this.edit = false;
                this.product.id = '';
                this.product.name = '';
                this.product.description = '';
                this.product.price = '';
                this.product.amount = '';
            }
        }
    }
</script>