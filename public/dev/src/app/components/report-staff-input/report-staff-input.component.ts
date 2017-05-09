import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../services/httpClient/httpClient.service';
import {UtilitiesService} from "../../services/utilities/utilities.service";

@Component({
    selector: 'app-report-staff-input',
    templateUrl: './report-staff-input.component.html'
})
export class ReportStaffInputComponent implements OnInit, IComponent {

    public header_input: any;
    public header_stock: any;

    public report_inputs:  any[]    = [];
    public report_stocks:  any[]    = [];
    public supplier:       any      = null;
    public suppliers:      any[]    = [];
    public distributors:   any[]    = [];
    public distributor:    any      = null;
    public staffs:         any[]    = [];
    public products:       any[]    = [];
    public producers:      any[]    = [];
    public product_types:  any[]    = [];
    public units:          any[]    = [];
    public cabinets:       any[]    = [];
    public first_day:      string   = '';
    public last_day:       string   = '';
    public today:          string   = '';
    public filter_ReportInput : any;
    public filter_ReportStock : any;
    public isLoading_Input: boolean = true;
    public isLoading_Stock: boolean = true;

    public datepicker_from_input: Date;
    public datepicker_to_input: Date;
    public datepickerToOpts_input: any = {};

    constructor(private httpClientService: HttpClientService, public utilitiesService: UtilitiesService) {
    }

    ngOnInit(): void {
        this.title = 'Báo cáo nhân viên nhập';
        this.prefix_url = 'report-staff-inputs';
        this.range_date = this.utilitiesService.range_date;
        this.refreshData();
        this.datepickerSettings = this.utilitiesService.datepickerSettings;
        this.action_data = {
            ADD: false,
            EDIT: false,
            DELETE: false
        };
        this.header_input = {
            distributor_name: {
                title: 'Đại lý'
            },
            date_input: {
                title: 'Ngày'
            },
            time_input: {
                title: 'Giờ'
            },
            staff_input_fullname: {
                title: 'Nhân viên nhập'
            },
            cabinet_name: {
                title: 'Tủ'
            },
            tray_name: {
                title: 'Mâm'
            },
            product_barcode: {
                title: 'Mã vạch SP'
            },
            product_name: {
                title: 'Tên sản phẩm'
            },
            unit_name: {
                title: 'ĐVT'
            },
            quantum_in: {
                title: 'SL nhập'
            },
            fc_product_price: {
                title: 'Đơn giá'
            },
            fc_total_pay: {
                title: 'Thành tiền'
            }
        };
        this.header_stock = {
            distributor_name: {
                title: 'Đại lý'
            },
            cabinet_name: {
                title: 'Tủ'
            },
            tray_name: {
                title: 'Mâm'
            },
            product_barcode: {
                title: 'Mã vạch SP'
            },
            product_name: {
                title: 'Tên sản phẩm'
            },
            unit_name: {
                title: 'ĐVT'
            },
            TonDauKy: {
                title: 'Tồn đầu kỳ'
            },
            sum_in: {
                title: 'Nhập trong kỳ'
            },
            sum_out: {
                title: 'Xuất trong kỳ'
            },
            quantum_remain: {
                title: 'Tồn cuối kỳ'
            }
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
    datepickerToOpts: any;

    handleDateFromChange(dateFrom: Date): void {
    }

    clearDate(event: any, field: string): void {
    }

    loadData(): void {
        this.httpClientService.get(this.prefix_url).subscribe(
            (success: any) => {
                this.reloadData(success);
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }

    reloadData(arr_data: any[]): void {
        this.first_day = arr_data['first_day'];
        this.last_day = arr_data['last_day'];
        this.today = arr_data['today'];

        this.supplier = arr_data['supplier'];

        this.suppliers = arr_data['suppliers'];

        this.distributors = arr_data['distributors'];

        this.staffs = arr_data['staffs'];

        this.products = arr_data['products'];

        this.producers = arr_data['producers'];

        this.product_types = arr_data['product_types'];

        this.units = arr_data['units'];

        this.cabinets = arr_data['cabinets'];
    }

    reloadDataSearch(arr_data: any[]): void {
    }

    refreshData(): void {
        this.clearSearchReportInput();
        this.clearSearchReportStock();
        this.loadData()
    }

    loadOne(id: number): void {
    }

    addOne(): void {
    }

    updateOne(): void {
    }

    deactivateOne(id: number): void {
    }

    deleteOne(id: number): void {
    }

    displayColumn(): void {
    }

    search(): void {
    }

    clearSearch(): void {
    }

    clearOne(): void {
    }

    displayEditBtn(status: boolean): void {
    }

    changeLoading(status: boolean): void {
    }

    fillDataModal(id: number): void {
    }

    confirmDeactivateOne(id: number): void {
    }

    validateOne(): boolean {
        return null;
    }

    actionCrud(obj: any): void {
    }

    /** My Function */
    public myHandleDateFromChange(dateFrom: Date, mode) {
        switch (mode) {
            case 'input':
                this.datepicker_from_input = dateFrom;
                this.datepickerToOpts_input = {
                    startDate: dateFrom,
                    autoclose: true,
                    todayBtn: 'linked',
                    todayHighlight: true,
                    icon: this.utilitiesService.icon_calendar,
                    placeholder: this.utilitiesService.date_placeholder,
                    format: 'dd/mm/yyyy'
                };
                break;
            default:
                break;
        }
    }

    /** Search Report Input */
    public search_ReportInput() {
        if(this.datepicker_from_input != null && this.datepicker_to_input != null) {
            let from_date = this.utilitiesService.getDate(this.datepicker_from_input);
            let to_date = this.utilitiesService.getDate(this.datepicker_to_input);
            this.filter_ReportInput.from_date = from_date;
            this.filter_ReportInput.to_date = to_date;
        }
        this.myChangeLoading('input', false);

        this.httpClientService.post(`${this.prefix_url}/report-inputs/search`, {"filter": this.filter_ReportInput}).subscribe(
            (success: any) => {
                this.reloadDataReportInput(success);
                this.myDisplayColumn('input');
                this.myChangeLoading('input', true);
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }

    /** Search Report Stock */
    public search_ReportStock() {
        this.myChangeLoading('stock', false);
        this.httpClientService.post(`${this.prefix_url}/report-stocks/search`, {"filter": this.filter_ReportStock}).subscribe(
            (success: any) => {
                this.reloadDataReportStock(success);
                this.myDisplayColumn('stock');
                this.myChangeLoading('stock', true);
            },
            (error: any) => {
                this.utilitiesService.showToastr('error');
            }
        );
    }

    private reloadDataReportInput(arr_datas): void {
        this.report_inputs = arr_datas['report_inputs'];
    }

    private reloadDataReportStock(arr_datas): void {
        this.report_stocks = arr_datas['report_stocks'];
    }

    public clearSearchReportInput(): void {
        this.datepicker_from_input = null;
        this.datepicker_to_input = null;
        this.filter_ReportInput = {
            from_date: '',
            to_date: '',
            range: '',
            product_id: 0,
            unit_id: 0,
            staff_input_id: 0,
            cabinet_id: 0
        };
    }

    public clearSearchReportStock(): void {
        this.filter_ReportStock = {
            month: new Date().getMonth() + 1,
            year: new Date().getFullYear(),
            product_id: 0,
            unit_id: 0,
            cabinet_id: 0
        };
    }

    public myChangeLoading(type: string, status: boolean): void {
        switch (type) {
            case 'input':
                this.isLoading_Input = status;
                break;
            case 'stock':
                this.isLoading_Stock = status;
                break;
            default:
                break;
        }
    }

    public onMonthChange(event: any) {
        let str_month: string = event.target.value;
        let month: number = Number(str_month.replace('Tháng ', ''));
        this.filter_ReportStock.month = month;
    }

    public onYearChange(event: any) {
        let year = event.target.value;
        this.filter_ReportStock.year = year;
    }

    public myClearDate(event: any, mode: string, field: string) {
        if (event == null) {
            switch (mode) {
                case 'input':
                    switch (field) {
                        case 'from':
                            this.filter_ReportInput.from_date = '';
                            break;
                        case 'to':
                            this.filter_ReportInput.to_date = '';
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    public myDisplayColumn(mode: string) {
        let setting = {
            product_id: ['product_barcode', 'product_name'],
            unit_id: ['unit_name'],
            distributor_id: ['distributor_name'],
            staff_input_id: ['staff_input_name'],
            staff_output_id: ['staff_output_name'],
            cabinet_id: ['cabinet_name']
        };
        switch (mode) {
            case 'input':
                for (let parent in setting) {
                    for (let child of setting[parent]) {
                        if (!!this.header_input[child])
                            this.header_input[child].visible = !(!!this.filter_ReportInput[parent]);
                    }
                }
                break;
            case 'stock':
                for (let parent in setting) {
                    for (let child of setting[parent]) {
                        if (!!this.header_stock[child])
                            this.header_stock[child].visible = !(!!this.filter_ReportStock[parent]);
                    }
                }
                break;
            default:
                break;
        }
    }

    public downloadFile(mode: string) {
        let url: string = '';
        switch (mode) {
            case 'input':
                if (this.datepicker_from_input != null && this.datepicker_to_input != null) {
                    let from_date = this.utilitiesService.getDate(this.datepicker_from_input);
                    let to_date = this.utilitiesService.getDate(this.datepicker_to_input);
                    this.filter_ReportInput.from_date = from_date;
                    this.filter_ReportInput.to_date = to_date;
                }
                url = `report-inputs/search?query=${JSON.stringify(this.filter_ReportInput)}`;
                break;
            case 'stock':
                url = `report-stocks/search?query=${JSON.stringify(this.filter_ReportStock)}`;
                break;
            default: break;
        }
        this.httpClientService.get(`${this.prefix_url}/${url}`, 'text')
            .subscribe(
                (success: any) => {
                    this.utilitiesService.downloadFile(success, 'abc.csv', 'text/csv');
                },
                (error: any) => {
                    this.utilitiesService.showToastr('error');
                }
            );
    }
}
