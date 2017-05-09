import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../services/httpClient/httpClient.service';
import {UtilitiesService} from '../../services/utilities/utilities.service';

@Component({
    selector: 'app-product',
    templateUrl: './product.component.html'
})
export class ProductComponent implements OnInit, IComponent {

    public product_types: any[] = [];
    public producers: any[] = [];
    public units: any[] = [];
    public suppliers: any[] = [];
    public products: any[] = [];
    public products_search: any[] = [];
    public product: any;
    public dis_or_sup: string;

    constructor(private httpClientService: HttpClientService, private utilitiesService: UtilitiesService) {
    }

    ngOnInit(): void {
        this.title = 'Sản phẩm';
        this.prefix_url = 'products';
        this.range_date = this.utilitiesService.range_date;
        this.refreshData();
        this.datepickerSettings = this.utilitiesService.datepickerSettings;
        this.header = {
            barcode: {
                title: 'Mã vạch'
            },
            name: {
                title: 'Tên'
            },
            fc_price_input: {
                title: 'Giá nhập'
            },
            fc_price_output: {
                title: 'Giá bán'
            },
            producer_name: {
                title: 'Nhà cung cấp sản phẩm'
            },
            unit_name: {
                title: 'ĐVT'
            },
            description: {
                title: 'Mô tả'
            },
            dom_is_allowed: {
                title: 'Trạng thái'
            }
        };

        this.modal = {
            id: 0,
            header: '',
            body: '',
            footer: ''
        };
    }

    title: string;
    placeholder_code: string;
    prefix_url: string;
    isEdit: boolean;
    isLoading: boolean;
    filtering: any;
    range_date: any[];
    header: any;
    modal: any;
    action_data: any;
    datepickerSettings: any;
    datepicker_from: Date;
    datepicker_to: Date;
    datepickerToOpts: any = {};

    handleDateFromChange(dateFrom: Date): void {
        this.datepicker_from = dateFrom;
        this.datepickerToOpts = {
            startDate: dateFrom,
            autoclose: true,
            todayBtn: 'linked',
            todayHighlight: true,
            icon: this.utilitiesService.icon_calendar,
            placeholder: this.utilitiesService.date_placeholder,
            format: 'dd/mm/yyyy'
        };
    }

    clearDate(event: any, field: string): void {
        if (event == null) {
            switch (field) {
                case 'from':
                    this.filtering.from_date = '';
                    break;
                case 'to':
                    this.filtering.from_date = '';
                    break;
                default:
                    break;
            }
        }
    }

    loadData(): void {
        this.httpClientService.get(this.prefix_url).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.changeLoading(true);
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }

    reloadData(arr_data: any[]): void {
        this.products = [];

        this.product_types = arr_data['product_types'];

        this.products_search = arr_data['products'];

        this.producers = arr_data['producers'];

        this.units = arr_data['units'];

        this.suppliers = arr_data['suppliers'];

        this.dis_or_sup = arr_data['dis_or_sup'];
    }

    reloadDataSearch(arr_data: any[]): void {
        this.products = arr_data['products'];
        for (let product of this.products) {
            let check = (product.is_allowed == 1) ? 'checked' : '';
            product['dom_is_allowed'] = `
                <label class="i-switch m-t-xs m-r">
                  <input type="checkbox" ${check} disabled>
                  <i></i>
                </label>
            `;
        }
    }

    refreshData(): void {
        this.changeLoading(false);
        this.clearOne();
        this.clearSearch();
        this.loadData();
    }

    loadOne(id: number): void {
        this.product = this.products.find(function (o) {
            return o.id == id;
        });

        this.isEdit = true;

        this.utilitiesService.showTab('menu2');
    }

    addOne(): void {
        if (!this.validateOne()) return;

        this.httpClientService.post(this.prefix_url, {"product": this.product}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.clearOne();
                this.utilitiesService.showToastr('success', 'Thêm thành công.');
            },
            (error: any) => {
                for (let err of error.json()['msg']) {
                    this.utilitiesService.showToastr('error', err);
                }
            }
        );
    }

    updateOne(): void {
        if (!this.validateOne()) return;

        this.httpClientService.put(this.prefix_url, {"product": this.product}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.clearOne();
                this.displayEditBtn(false);
                this.utilitiesService.showToastr('success', 'Cập nhật thành công.');
            },
            (error: any) => {
                for (let err of error.json()['msg']) {
                    this.utilitiesService.showToastr('error', err);
                }
            }
        );
    }

    deactivateOne(id: number): void {
        this.httpClientService.patch(this.prefix_url, {"id": id}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.utilitiesService.showToastr('success', 'Hủy thành công.');
                this.utilitiesService.toggleModal('modal-confirm');
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }

    deleteOne(id: number): void {
        this.httpClientService.delete(`${this.prefix_url}/${id}`).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.utilitiesService.showToastr('success', 'Xóa thành công.');
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }

    displayColumn(): void {
        let setting = {
            producer_id: ['producer_name'],
            unit_id: ['unit_name'],
            barcode: ['barcode'],
            name: ['name']
        };
        for (let parent in setting) {
            for (let child of setting[parent]) {
                if (!!this.header[child])
                    this.header[child].visible = !(!!this.filtering[parent]);
            }
        }
    }

    search(): void {
        if (this.datepicker_from != null && this.datepicker_to != null) {
            let from_date = this.utilitiesService.getDate(this.datepicker_from);
            let to_date = this.utilitiesService.getDate(this.datepicker_to);
            this.filtering.from_date = from_date;
            this.filtering.to_date = to_date;
        }
        this.changeLoading(false);

        this.httpClientService.get(`${this.prefix_url}/search?query=${JSON.stringify(this.filtering)}`).subscribe(
            (success: any) => {
                this.reloadDataSearch(success);
                this.displayColumn();
                this.changeLoading(true);
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }

    clearSearch(): void {
        this.datepicker_from = null;
        this.datepicker_to = null;
        this.filtering = {
            from_date: '',
            to_date: '',
            range: '',
            producer_id: 0,
            unit_id: 0,
            barcode: '',
            name: ''
        };
    }

    clearOne(): void {
        this.product = {
            barcode: "",
            name: "",
            description: "",
            active: true,
            product_type_id: 0,
            unit_id: 0,
            producer_id: 0,
            is_allowed: false,
            price_input: 0,
            price_output: 0
        };
    }

    displayEditBtn(status: boolean): void {
        this.isEdit = status;
    }

    changeLoading(status: boolean): void {
        this.isLoading = status;
    }

    fillDataModal(id: number): void {
        this.modal.id = id;
        this.modal.header = 'Xác nhận';
        this.modal.body = `Có chắc muốn xóa ${this.title} này?`;
        this.modal.footer = 'OK';
    }

    confirmDeactivateOne(id: number): void {
        this.deactivateOne(id);
    }

    validateOne(): boolean {
        let flag: boolean = true;
        if (this.product.name == '') {
            flag = false;
            this.utilitiesService.showToastr('warning', `Tên ${this.title} không được để trống.`);
        }
        if (this.product.producer_id == 0) {
            flag = false;
            this.utilitiesService.showToastr('warning', 'Nhà cung cấp sản phẩm không được để trống.');
        }
        if (this.product.unit_id == 0) {
            flag = false;
            this.utilitiesService.showToastr('warning', 'Đơn vị tính không được để trống.');
        }
        return flag;
    }

    actionCrud(obj: any): void {
        switch (obj.mode) {
            case 'add':
                this.displayEditBtn(false);
                this.clearOne();
                this.utilitiesService.showTab('menu2');
                break;
            case 'edit':
                this.loadOne(obj.data.id);
                break;
            case 'delete':
                this.fillDataModal(obj.data.id);
                break;
            default:
                break;
        }
    }
}
