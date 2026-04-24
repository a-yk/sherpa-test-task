import { Component, Input } from '@angular/core';
import { Product as ProductInterface } from "../../models/product"
import { RouterLink } from "@angular/router";
import { environment } from '../../environment/environment';

@Component({
  selector: 'app-product',
  imports: [RouterLink],
  templateUrl: './product.html',
})
export class Product {
  @Input() product: ProductInterface;
  resourcesUrl = environment.resourcesUrl;
}
