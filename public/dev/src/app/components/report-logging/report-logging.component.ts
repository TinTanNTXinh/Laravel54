import {Component, OnInit} from '@angular/core';

import {HttpClientService} from '../../services/httpClient/httpClient.service';
import {UtilitiesService} from "../../services/utilities/utilities.service";

@Component({
    selector: 'app-report-logging',
    templateUrl: './report-logging.component.html',
    styles: []
})
export class ReportLoggingComponent implements OnInit, IComponent {

    public report_loggings: any[] = [];

    constructor(private httpClientService: HttpClientService, private utilitiesService: UtilitiesService) {

    }

    ngOnInit() {
        this.title = 'Báo cáo lỗi';
        this.prefix_url = 'report-loggings';
        this.range_date = this.utilitiesService.range_date;
        this.refreshData();
        this.datepickerSettings = this.utilitiesService.datepickerSettings;
        this.action_data = {
            ADD: false,
            EDIT: false,
            DELETE: false
        };
        this.header = {
            name: {
                title: 'Tên'
            },
            description: {
                title: 'Mô tả'
            },
            count: {
                title: 'Biến đếm'
            },
            created_by: {
                title: 'Lỗi do'
            },
            error_type: {
                title: 'Loại'
            },
            created_date: {
                title: 'Ngày'
            },
            created_time: {
                title: 'Giờ'
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
    }

    reloadDataSearch(arr_data: any[]): void {
        this.report_loggings = arr_data['report_loggings'];
    }

    refreshData(): void {
        this.changeLoading(false);
        this.clearSearch();
        this.loadData();
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
            range: ''
        };
    }

    clearOne(): void {
    }

    displayEditBtn(status: boolean): void {
    }

    changeLoading(status: boolean): void {
        this.isLoading = status;
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
}