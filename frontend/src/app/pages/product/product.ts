import { Component, OnInit, ViewChild, ElementRef, } from '@angular/core';
import { Observable } from 'rxjs';
import { Product } from '../../models/product';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { AsyncPipe, DecimalPipe } from '@angular/common';
import { environment } from '../../environment/environment';

@Component({
  selector: 'app-product-page',
  imports: [
    AsyncPipe,
    RouterLink,
    DecimalPipe,
],
  templateUrl: './product.html',
})
export class ProductPage implements OnInit {
  product$: Observable<Product>;
  resourceUrl = environment.resourcesUrl;
  backendUrl = environment.backendUrl;

  @ViewChild('mainImage') mainImage!: ElementRef<HTMLImageElement>;

  constructor(private http: HttpClient, private route: ActivatedRoute) {}

  ngOnInit() {
    const productId = this.route.snapshot.paramMap.get('id');
    this.product$ = this.http.get<Product>(`${this.backendUrl}api/products/${productId}`);
  }

  changeImage(path: string) {
    this.mainImage.nativeElement.src = path;
  }
}
