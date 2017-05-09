import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../services/httpClient.service';
import {DateHelperService} from '../../services/helpers/date.helper';
import {ToastrHelperService} from '../../services/helpers/toastr.helper';
import {DomHelperService} from '../../services/helpers/dom.helper';

@Component({
    selector: 'app-device',
    templateUrl: './device.component.html'
})
export class DeviceComponent implements OnInit, IComponent {

    public collections: any[] = [];
    public io_centers: any[] = [];
    public devices: any[] = [];
    public devices_search: any[] = [];
    public belong_cabinets: any[] = [];
    public belong_RFIDs: any[] = [];
    public device: any;
    public parent_devices: any[] = [];
    public isDisplayQuickTray: boolean = false;

    constructor(private httpClientService: HttpClientService, private utilitiesService: UtilitiesService) {
    }

    ngOnInit(): void {
        this.title = 'Thiết bị';
        this.prefix_url = 'devices';
        this.range_date = this.utilitiesService.range_date;
        this.refreshData();
        this.datepickerSettings = this.utilitiesService.datepickerSettings;
        this.action_data = {
            ADD: false,
            EDIT: true,
            DELETE: true
        };
        this.header = {
            code: {
                title: 'Mã'
            },
            name: {
                title: 'Tên'
            },
            quantum_product: {
                title: 'Lượng hàng'
            },
            collect_name: {
                title: 'Loại'
            },
            io_center_name: {
                title: 'Bộ trung tâm'
            },
            parent_name: {
                title: 'Thuộc'
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
                    this.filtering.to_date = '';
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
        this.devices = [];

        this.collections = arr_data['collections'];

        this.io_centers = arr_data['io_centers'];

        this.devices_search = arr_data['devices'];

        this.belong_cabinets = this.devices_search.filter(function (o) {
            return ['Cabinet'].includes(o['collect_code']);
        });
        this.belong_RFIDs = this.devices_search.filter(function (o) {
            return ['RFID'].includes(o['collect_code']);
        });

        this.parent_devices = this.devices_search.filter(function (o) {
           return ['Cabinet', 'RFID'].includes(o['collect_code']);
        });
    }

    reloadDataSearch(arr_data: any[]): void {
        this.devices = arr_data['devices'];
        this.devices_search = arr_data['devices'];
    }

    refreshData(): void {
        this.changeLoading(false);
        this.clearOne();
        this.clearSearch();
        this.loadData();
    }

    loadOne(id: number): void {
        this.device = this.devices.find(function (o) {
            return o.id == id;
        });
    }

    addOne(): void {
    }

    updateOne(): void {
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
            io_center_id: ['io_center_code, io_center_name'],
            collect_code: ['collection_name'],
            device_id: ['device_code, device_name'],
            parent_id: ['parent_name']
        };
        for (let parent in setting) {
            for (let child of setting[parent]) {
                if (!!this.header[child])
                    this.header[child].visible = !(!!this.filtering[parent]);
            }
        }

        this.header.quantum_product.visible = this.filtering.collect_code == 'Tray' || this.filtering.collect_code == '';
        if(!(!!this.filtering.parent_id))
            this.header.parent_name.visible = this.filtering.collect_code == 'Tray' || this.filtering.collect_code == 'Card' || this.filtering.collect_code == '';
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
        this.filtering = {
            from_date: '',
            to_date: '',
            range: '',
            io_center_id: 0,
            device_id: 0,
            collect_code: '',
            parent_id: 0
        };
    }

    clearOne(): void {
        this.device = {
            code: "",
            name: "",
            quantum_product: 0,
            active: true,
            collect_code: "",
            io_center_id: 0,
            parent_id: 0,
            quantum_tray: 0
        };
    }

    displayEditBtn(status: boolean): void {
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
        if (this.device.name == '') {
            flag = false;
            this.utilitiesService.showToastr('warning', `Tên ${this.title} không được để trống.`);
        }
        if (this.device.collect_code == '') {
            flag = false;
            this.utilitiesService.showToastr('warning', `Loại ${this.title} không được để trống.`);
        }
        if (this.device.io_center_id == 0) {
            flag = false;
            this.utilitiesService.showToastr('warning', `Bộ trung tâm của ${this.title} không được để trống.`);
        }

        // Exception
        switch (this.device.collect_code) {
            case 'RFID':
            case 'Cabinet':
            case 'CDM':
                if (this.device.parent_id != 0) {
                    flag = false;
                    this.utilitiesService.showToastr('warning', `Loại này không thuộc ${this.title} nào.`);
                }
                break;
            case 'Tray':
            case 'Card':
                if (this.device.parent_id == 0) {
                    flag = false;
                    this.utilitiesService.showToastr('warning', `Loại này phải thuộc một ${this.title} khác.`);
                }
                if (this.device.code == '') {
                    flag = false;
                    this.utilitiesService.showToastr('warning', `Mã ${this.title} không được để trống.`);
                }
                break;
            default:
                break;
        }

        return flag;
    }

    actionCrud(obj: any): void {
        switch (obj.mode) {
            case 'add':
                break;
            case 'edit':
                this.loadOne(obj.data.id);
                this.utilitiesService.showTab(obj.data.collect_code);
                break;
            case 'delete':
                this.fillDataModal(obj.data.id);
                break;
            default:
                break;
        }
    }

    /** Funciton **/
    public myAddOne(collect_code: string): void {
        if (!!collect_code)
            this.customData(collect_code);

        if (!this.validateOne()) return;

        this.httpClientService.post(this.prefix_url, {"device": this.device}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.clearOne();
                this.utilitiesService.showToastr('success', 'Thêm thành công!');
            },
            (error: any) => {
                for (let err of error.json()['msg']) {
                    this.utilitiesService.showToastr('error', err);
                }
            }
        );
    }

    public myUpdateOne(collect_code: string): void {
        this.customData(collect_code);

        if (!this.validateOne()) return;

        this.httpClientService.put(this.prefix_url, {"device": this.device}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.clearOne();
                this.utilitiesService.showToastr('success', 'Cập nhật thành công.');
            },
            (error: any) => {
                for (let err of error.json()['msg']) {
                    this.utilitiesService.showToastr('error', err);
                }
            }
        );
    }

    public customData(collect_code): void {
        switch (collect_code) {
            case 'Cabinet':
                this.device = {
                    ...this.device,
                    active: true,
                    collect_code: collect_code,
                    collect_id: 0,
                    parent_name: "",
                    parent_id: 0
                };
                break;
            case 'RFID':
                this.device = {
                    ...this.device,
                    code: "",
                    quantum_product: 0,
                    active: true,
                    collect_code: collect_code,
                    collect_id: 0,
                    parent_name: "",
                    parent_id: 0,
                    quantum_tray: 0
                };
                break;
            case 'CDM':
                this.device = {
                    ...this.device,
                    quantum_product: 0,
                    active: true,
                    collect_code: collect_code,
                    collect_id: 0,
                    parent_name: "",
                    parent_id: 0,
                    quantum_tray: 0
                };
                break;
            case 'Tray':
                this.device = {
                    ...this.device,
                    active: true,
                    collect_code: collect_code,
                    collect_id: 0,
                    quantum_tray: 0
                };
                break;
            case 'Card':
                this.device = {
                    ...this.device,
                    quantum_product: 0,
                    active: true,
                    collect_code: collect_code,
                    collect_id: 0,
                    quantum_tray: 0
                };
                break;
            default:
                break;
        }
    }

    public myDisplayEditBtn(collect_code: string): boolean {
        if(this.device.hasOwnProperty('id') && this.device.id != 0)
            return this.device.collect_code == collect_code;
        return false;
    }

    public displayQuickTray(): void {
        this.isDisplayQuickTray = !this.isDisplayQuickTray;
        this.device.quantum_tray = 0;
        this.device.quantum_product = 0;
    }

}
