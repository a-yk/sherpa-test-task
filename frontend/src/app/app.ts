import { Component, signal } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { Header } from "./components/header/header"
import { Footer } from "./components/footer/footer"
import { ProductList } from "./components/product-list/product-list"

@Component({
  selector: 'app-root',
  imports: [
    RouterOutlet,
    Header,
    Footer,
  ],
  templateUrl: './app.html',
  styleUrl: './app.css',
})
export class App {
  protected readonly title = signal('Catalog');
}
