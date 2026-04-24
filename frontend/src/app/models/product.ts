import { Feature } from "./feature"
import { Image } from "./image"

export interface Product {
    id: number;
    externalCode: string;
    name: string;
    description: string;
    price: number;
    discount: number;
    features: Feature[];
    images: Image[]
}
