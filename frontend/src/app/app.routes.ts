import { Routes } from '@angular/router';
import { ProductPage } from './pages/product/product';
import { ProductListPage } from './pages/product-list/product-list';
import { ImportPage } from './pages/import/import'

export const routes: Routes = [
    {
        path: '',
        component: ProductListPage,
    },
    {
        path: 'product/:id',
        component: ProductPage,
    },
    {
        path: 'import',
        component: ImportPage,
    }
];
