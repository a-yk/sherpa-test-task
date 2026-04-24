import { Component } from '@angular/core';
import { ProductList } from '../../components/product-list/product-list';

@Component({
  selector: 'app-product-list-page',
  imports: [
    ProductList,
  ],
  templateUrl: './product-list.html',
})
export class ProductListPage {}
