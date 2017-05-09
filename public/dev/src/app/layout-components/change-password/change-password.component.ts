import { Component, OnInit } from '@angular/core';

import {HttpClientService} from '../../services/httpClient/httpClient.service';
import {UtilitiesService} from '../../services/utilities/utilities.service';
import {AuthenticationService} from '../../services/authentication/authentication.service';

@Component({
    selector: 'app-change-password',
    templateUrl: './change-password.component.html'
})
export class ChangePasswordComponent implements OnInit {

    public data: any;

    constructor(private httpClientService: HttpClientService, private utilitiesService: UtilitiesService, private authenticationService: AuthenticationService) {
    }

    ngOnInit() {
        this.clear();
    }

    public changePassword() {
        this.httpClientService.post('users/change-password', {"data": this.data}).subscribe(
            (success: any) => {
                this.clear();
                this.utilitiesService.showToastr('success', 'Thay đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
                this.authenticationService.clearAuthLocalStorage();
                this.authenticationService.notifyAuthenticate(false);
            },
            (error: any) => {
                this.utilitiesService.showToastr('error', error['error']);
            }
        );
    }

    public clear() {
        this.data = {
            password: '',
            new_password: ''
        };
    }

}
