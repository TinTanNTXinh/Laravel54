import { Component, OnInit } from '@angular/core';

import { ProductType } from '../../models/product_type.model';

@Component({
    selector: 'app-productType',
    templateUrl: './productType.component.html'
})
export class ProductTypeComponent implements OnInit {
    
    /**
     *
     */
    constructor() {
        //called first time before the ngOnInit()

    }

    ngOnInit() {
        //called after the constructor and called  after the first ngOnChanges() 
    }
}
