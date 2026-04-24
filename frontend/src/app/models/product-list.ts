import { Product } from "./product"

export interface ProductList {
    products: Product[];
    page: number;
    total_pages: number;
}
