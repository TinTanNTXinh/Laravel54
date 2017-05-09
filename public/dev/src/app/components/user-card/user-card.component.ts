import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../services/httpClient/httpClient.service';
import {UtilitiesService} from '../../services/utilities/utilities.service';

@Component({
    selector: 'app-user-card',
    templateUrl: './user-card.component.html'
})
export class UserCardComponent implements OnInit, IComponent {

    public user_cards: any = [];
    public staffs: any = [];
    public cards: any = [];
    public user_card: any;

    public users: any = [];
    public suppliers: any = [];
    public distributors: any = [];
    public positions: any[] = [];
    public io_centers: any[] = [];
    public rfids: any[] = [];

    constructor(private httpClientService: HttpClientService, private utilitiesService: UtilitiesService) {
    }

    ngOnInit(): void {
        this.title = 'Cấp thẻ cho nhân viên';
        this.prefix_url = 'user-cards';
        this.range_date = this.utilitiesService.range_date;
        this.refreshData();
        this.datepickerSettings = this.utilitiesService.datepickerSettings;
        this.action_data = {
            ADD: true,
            EDIT: false,
            DELETE: true
        };
        this.header = {
            io_center_code: {
                title: 'Mã bộ trung tâm'
            },
            io_center_name: {
                title: 'Bộ trung tâm'
            },
            parent_name: {
                title: 'RFID'
            },
            card_name: {
                title: 'Thẻ'
            },
            card_code: {
                title: 'Mã thẻ'
            },
            card_description: {
                title: 'Mô tả thẻ'
            },
            user_fullname: {
                title: 'Nhân viên'
            },
            user_phone: {
                title: 'SĐT'
            },
            position_name: {
                title: 'Chức vụ'
            },
            supplier_name: {
                title: 'Khách hàng'
            },
            distributor_name: {
                title: 'Đại lý'
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
        this.user_cards = [];

        this.staffs = arr_data['staffs'];

        this.cards = arr_data['cards'];

        this.users = arr_data['users'];
        this.suppliers = arr_data['suppliers'];
        this.distributors = arr_data['distributors'];
        this.positions = arr_data['positions'];
        this.io_centers = arr_data['io_centers'];
        this.rfids = arr_data['rfids'];
    }

    reloadDataSearch(arr_data: any[]): void {
        this.user_cards = arr_data['user_cards'];
    }

    refreshData(): void {
        this.changeLoading(false);
        this.clearOne();
        this.clearSearch();
        this.loadData();
    }

    loadOne(id: number): void {
        this.user_card = this.user_cards.find(function (o) {
            return o.id == id;
        });

        this.utilitiesService.showTab('menu2');
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
    }

    displayColumn(): void {
        let setting = {
            supplier_id: ['supplier_name'],
            distributor_id: ['distributor_name'],
            position_id: ['position_name'],
            io_center_id: ['io_center_code', 'io_center_name'],
            rfid_id: ['parent_name'],
            fullname: ['user_fullname'],
            phone: ['user_phone']
        };
        for (let parent in setting) {
            for (let child of setting[parent]) {
                if (!!this.header[child])
                    this.header[child].visible = !(!!this.filtering[parent]);
            }
        }

        this.header.supplier_name.visible = this.filtering.dis_or_sup == 'sup';
        this.header.distributor_name.visible = this.filtering.dis_or_sup == 'dis';
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
            dis_or_sup: 'sup',
            supplier_id: 0,
            distributor_id: 0,
            position_id: 0,
            io_center_id: 0,
            rfid_id: 0,
            fullname: 0,
            phone: ''
        };
    }

    clearOne(): void {
        this.user_card = {
            user_id: 0,
            card_id: 0,
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
        this.modal.body = 'Có chắc muốn xóa người dùng ra khỏi thẻ này?';
        this.modal.footer = 'OK';
    }

    confirmDeactivateOne(id: number): void {
        this.deactivateOne(id);
    }

    validateOne(): boolean {
        let flag: boolean = true;

        if (this.user_card.card_id == 0) {
            this.utilitiesService.showToastr('warning', 'Vui lòng chọn 1 thẻ.');
            flag = false;
        }

        if (this.user_card.user_id == 0) {
            this.utilitiesService.showToastr('warning', 'Vui lòng chọn 1 người dùng.');
            flag = false;
        }
        return flag;
    }

    actionCrud(obj: any): void {
    }

    /** My Function */
    public saveOne(): void {
        if (!this.validateOne()) return;

        this.httpClientService.post(this.prefix_url, {"user_card": this.user_card}).subscribe(
            (success: any) => {
                this.reloadData(success);
                this.utilitiesService.showToastr('success', 'Tác vụ thành công.');
            },
            (error: any) => {
                for (let err of error.json()['msg']) {
                    this.utilitiesService.showToastr('error', err);
                }
            }
        );
    }
}
