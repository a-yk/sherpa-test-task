import { Component, OnInit } from '@angular/core';
import { Product } from "../product/product"
import { Pagination } from "../pagination/pagination"
import { ProductList as ProductListInterface } from '../../models/product-list';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AsyncPipe } from '@angular/common';
import { environment } from '../../environment/environment';

@Component({
  selector: 'app-product-list',
  imports: [
    Product,
    Pagination,
    AsyncPipe,
],
  templateUrl: './product-list.html',
})
export class ProductList implements OnInit {
  listProducts$: Observable<ProductListInterface>;
  backendUrl = environment.backendUrl;

  constructor(private http: HttpClient) {}

  ngOnInit() {
    this.loadProducts(1);
  }

  onPageChange(page: number) {
    this.loadProducts(page);
  }

  loadProducts(page: number) {
    this.listProducts$ = this.http.get<ProductListInterface>(`${this.backendUrl}api/products?page=${page}`);
  }
}
