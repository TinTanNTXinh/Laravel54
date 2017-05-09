import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../services/httpClient/httpClient.service';
import {UtilitiesService} from '../../services/utilities/utilities.service';

@Component({
    selector: 'app-io-center',
    templateUrl: './io-center.component.html',
    styles: []
})
export class IOCenterComponent implements OnInit, IComponent {

    public suppliers: any[];
    public distributors: any[];
    public io_centers: any[] = [];
    public io_centers_search: any[];
    public io_center: any;

    constructor(private httpClientService: HttpClientService, private utilitiesService: UtilitiesService) {
    }

    ngOnInit(): void {
        this.title = 'Bộ trung tâm';
        this.prefix_url = 'io-centers';
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
            distributor_name: {
                title: 'Đại lý'
            },
            supplier_name: {
                title: 'Khách hàng'
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
        this.io_centers = [];

        this.io_centers_search = arr_data['io_centers'];

        this.distributors = arr_data['distributors'];

        this.suppliers = arr_data['suppliers'];
    }

    reloadDataSearch(arr_data: any[]): void {
        this.io_centers = arr_data['io_centers'];
        this.io_centers_search = arr_data['io_centers'];
    }

    refreshData(): void {
        this.changeLoading(false);
        this.clearOne();
        this.clearSearch();
        this.loadData();
    }

    loadOne(id: number): void {
        this.io_center = this.io_centers.find(function (o) {
            return o['id'] == id;
        });
        this.isEdit = true;
        this.utilitiesService.showTab('menu2');
    }

    addOne(): void {
        if (!this.validateOne()) return;

        this.httpClientService.post(this.prefix_url, {"io_center": this.io_center}).subscribe(
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

    updateOne(): void {
        if (!this.validateOne()) return;

        this.httpClientService.put(this.prefix_url, {"io_center": this.io_center}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.clearOne();
                this.displayEditBtn(false);
                this.utilitiesService.showToastr('success', 'Cập nhật thành công!');
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
                this.utilitiesService.showToastr('success', 'Hủy thành công!');
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
                this.utilitiesService.showToastr('success', 'Xóa thành công!');
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }

    displayColumn(): void {
        let setting = {
            io_center_id: ['io_center_code'],
            supplier_id: ['supplier_name'],
            distributor_id: ['distributor_name'],
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
            io_center_id: 0,
            distributor_id: 0,
            supplier_id: 0
        };
    }

    clearOne(): void {
        this.io_center = {
            code: '',
            name: '',
            active: true,
            dis_id: 0
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
        if (this.io_center.code == '') {
            flag = false;
            this.utilitiesService.showToastr('warning', `Mã ${this.title} không được để trống!`);
        }
        if (this.io_center.name == '') {
            flag = false;
            this.utilitiesService.showToastr('warning', `Tên  ${this.title} không được để trống!`);
        }
        if (this.io_center.dis_id == 0) {
            flag = false;
            this.utilitiesService.showToastr('warning', 'Đại lý không được để trống!');
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

    /* Pagination
     totalRecords: number = 3;
     pageSize: number = 1;

     pageChanged(page: number): void {
     this.getCustomersPage(page);
     }

     getCustomersPage(page: number): void {
     this.httpClientService.getDatasPage('io-centers', (page - 1) * this.pageSize, this.pageSize).subscribe(
     (success: Response) => {
     this.reloadDataWithPage(success);
     },
     (error: Response) => {
     this.utilitiesService.showToastr('error');
     },
     () => console.log()
     );
     }

     reloadDataWithPage(arr_datas: any): void {
     this.io_centers.splice(0, this.io_centers.length);
     for (let io_center of arr_datas['io_centers']) {
     this.io_centers.push(io_center);
     }

     this.totalRecords = arr_datas['total_records'];
     }
     */

}
