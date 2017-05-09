import { Component, OnInit } from '@angular/core';
import { Router, NavigationStart } from '@angular/router';

@Component({
  moduleId: module.id,
  selector: 'app-root',
  templateUrl: './app.component.html'
})
export class AppComponent implements OnInit {



  constructor(private router: Router) {
    
  }

  ngOnInit() {
  }

  public displayLayout(): boolean {
    if (this.router.url == '/login'){
      return false;
    }
    return true;
  }

    public setBodyClass(): string {
        if (this.router.url == '/login'){
            return 'login-container';
        }
        return 'xinh-xinh';
    }

}