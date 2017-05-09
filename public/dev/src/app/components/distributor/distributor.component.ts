import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../services/httpClient/httpClient.service';
import {UtilitiesService} from '../../services/utilities/utilities.service';

@Component({
    selector: 'app-distributor',
    templateUrl: './distributor.component.html',
    styles: []
})
export class DistributorComponent implements OnInit, IComponent {

    public cities: any[] = [];
    public districts: any[] = [];
    public wards: any[] = [];
    public suppliers: any = [];
    public distributors: any = [];
    public distributors_search: any = [];
    public distributor: any;

    constructor(private httpClientService: HttpClientService, private utilitiesService: UtilitiesService) {
    }

    ngOnInit(): void {
        this.title = 'Đại lý';
        this.prefix_url = 'distributors';
        this.range_date = this.utilitiesService.range_date;
        this.refreshData();
        this.datepickerSettings = this.utilitiesService.datepickerSettings;
        this.header = {
            code: {
                title: 'Mã'
            },
            name: {
                title: 'Tên'
            },
            supplier_name: {
                title: 'Khách hàng'
            },
            city: {
                title: 'Thành phố/Tỉnh'
            },
            district: {
                title: 'Quận/Huyện'
            },
            ward: {
                title: 'Phường/Xã'
            },
            address: {
                title: 'Địa chỉ'
            },
            phone: {
                title: 'Điện thoại'
            },
            email: {
                title: 'Email'
            },
            fax: {
                title: 'Fax'
            },
            note: {
                title: 'Ghi chú'
            }
        };

        this.modal = {
            id: 0,
            header: '',
            body: '',
            footer: ''
        };

        this.loadLocation();
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
        this.distributors = [];
        this.distributors_search = arr_data['distributors'];
        this.suppliers = arr_data['suppliers'];

        this.placeholder_code = arr_data['placeholder_code'];
    }

    reloadDataSearch(arr_data: any[]): void {
        this.distributors = arr_data['distributors'];
    }

    refreshData(): void {
        this.changeLoading(false);
        this.clearOne();
        this.clearSearch();
        this.loadData();
    }

    loadOne(id: number): void {
        this.distributor = this.distributors.find(function (o) {
            return o.id == id;
        });

        this.isEdit = true;

        this.utilitiesService.showTab('menu2');
    }

    addOne(): void {
        if (!this.validateOne()) return;

        this.httpClientService.post(this.prefix_url, {"distributor": this.distributor}).subscribe(
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

        this.httpClientService.put(this.prefix_url, {"distributor": this.distributor}).subscribe(
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
        )
    }

    displayColumn(): void {
        let setting = {
            distributor_id: ['code', 'name'],
            supplier_id: ['supplier_name'],
            city_code: ['city_name'],
            district_code: ['district_name'],
            ward_code: ['ward_name'],
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
            distributor_id: 0,
            supplier_id: 0,
            city_code: '',
            district_code: '',
            ward_code: ''
        };
    }

    clearOne(): void {
        this.distributor = {
            code: '',
            name: '',
            address: '',
            phone: '',
            email: '',
            fax: '',
            note: '',
            city_code: '',
            district_code: '',
            ward_code: '',
            sup_id: 0,
            active: true
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
        if (this.distributor.name == '') {
            flag = false;
            this.utilitiesService.showToastr('warning', `Tên ${this.title} không được để trống.`);
        }
        if (this.distributor.sup_id == 0) {
            flag = false;
            this.utilitiesService.showToastr('warning', `Khách hàng không được để trống.`);
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

    /** Function */
    public filteredDistrict: any[] = [];
    public filteredWard: any[] = [];

    public selectedCity(event: any) {
        this.filteredDistrict = this.districts.filter(o => o.city_code == event.code);
    }

    public selectedDistrict(event: any) {
        this.filteredWard = this.wards.filter(o => o.district_code == event.code);
    }
    
    public loadLocation(): void {
        this.httpClientService.get('locations').subscribe(
            (success: any) => {
                this.cities = success['cities'];
                this.districts = success['districts'];
                this.wards = success['wards'];
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }
}
