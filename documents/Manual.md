TÀI LIỆU MÔ TẢ ĐỊNH DẠNG DỮ LIỆU (v3.2.0)
===================

Ứng dụng web quản lý ***tủ ký gửi, máy bán nước, kho *** tự động

----------


Xóa tất cả dữ liệu
-------------

Gõ địa chỉ bên dưới trên trình duyệt và đợi thông báo tác vụ thành công.
http://showroom.app-demo.info/artisan/reset

Nạp tiền 
-------------

Server: http://showroom.app-demo.info/api/v1/cdm

> **Cấu trúc dữ liệu gửi lên server:**

> - c1: Ngày xảy ra hành động (dd/mm/yy hh:ii:ss)
> - c2: Mã thẻ
> - c3: Mã máy nạp tiền
> - c4: Trạng thái (“DPS”)
> - c5: Số tiền của thẻ
> - c6: Số tiền nạp


Ví dụ minh họa:
```
http://showroom.app-demo.info/api/v1/cdm?param={"u":"","p":"","id":"IO53A096","cnt":"1","t":"2017-01-20 10:16:38","c1":"15/03/17 10:16:38","c2":"75EC5428E5","c3":"CDM1","c4":"DPS","c5":"500000","c6":"500000"}
```

Nạp hàng
-------------

Server: http://showroom.app-demo.info/api/v1/prod-inout

> **Cấu trúc dữ liệu gửi lên server:**

> - c1: Ngày xảy ra hành động (dd/mm/yy hh:ii:ss)
> - c2: Mã thẻ
> - c3: Mã box
> - c4: Trạng thái (“IN”)
> - c5: Số tiền của thẻ
> - c6: Số lượng nạp
> - c7: Giá sản phẩm
> - c8: Mã tủ

Ví dụ minh họa:
```
http://showroom.app-demo.info/api/v1/prod-inout?param={"u":"","p":"","id":"IO53A096","cnt":"2","t":"2017-01-20 10:16:38","c1":"15/03/17 10:16:38","c2":"252858287D","c3":"1","c4":"IN","c5":"510000","c6":"1","c7":"10000","c8":"TU1"}
```

Bán hàng
-------------

Server: http://showroom.app-demo.info/api/v1/prod-inout

> **Cấu trúc dữ liệu gửi lên server:**

> - c1: Ngày xảy ra hành động (dd/mm/yy hh:ii:ss)
> - c2: Mã thẻ
> - c3: Mã box
> - c4: Trạng thái (“OUT”)
> - c5: Số tiền của thẻ
> - c6: Số lượng bán
> - c7: Giá sản phẩm
> - c8: Mã tủ

Ví dụ minh họa:
```
http://showroom.app-demo.info/api/v1/prod-inout?param={"u":"demo","p":"","id":"IO53A096","cnt":"3","t":"2017-01-20 10:16:38","c1":"20/01/17 10:16:38","c2":"75EC5428E5","c3":"1","c4":"OUT","c5":"490000","c6":"1","c7":"10000","c8":"TU1"}
```

Đăng ký thẻ cho khách vãng lai
-------------

Server: http://showroom.app-demo.info/api/v1/reg-visitor

> **Cấu trúc dữ liệu gửi lên server:**

> - c1: Ngày xảy ra hành động (dd/mm/yy hh:ii:ss)
> - c2: Mã thẻ
> - c3: Mã thiết bị đăng ký thẻ
> - c4: ""
> - c5: Số tiền của thẻ
> - c6: Số điện thoại của Khách vãng lai

Ví dụ minh họa:
```
http://showroom.app-demo.info/api/v1/reg-visitor?param={"u":"demo","p":"","id":"IO53A096","cnt":"3","t":"2017-01-20 10:16:38","c1":"20/01/17 10:16:38","c2":"75EC5428E5","c3":"CDM1","c4":"","c5":"0","c6":"0987654321"}
```

Kiểm tra số lượng hàng tồn trên box
-------------
Server sẽ trả về số lượng hàng trên box mà ta gửi. 

Server: http://showroom.app-demo.info/api/v1/check-stock

> **Cấu trúc dữ liệu gửi lên server:**

> - c1: Ngày xảy ra hành động (dd/mm/yy hh:ii:ss)
> - c2: Mã tủ
> - c3: Mã box

Ví dụ minh họa:
```
http://showroom.app-demo.info/api/v1/check-stock?param={"u":"demo","p":"","id":"IO53A096","cnt":"3","t":"2017-01-20 10:16:38","c1":"20/01/17 10:16:38","c2":"TU1","c3":"1"}
```

Debug
-------------

> **Tip:** Khi muốn xem dữ liệu gửi lên server đã đúng hay chưa thì thêm trường ```{"debug":"1"}``` vào trong chuỗi json. Khi đó server sẽ trả về chuỗi json mà ta gửi đến trong request.

Ví dụ debug chức năng nạp hàng:
```
http://showroom.app-demo.info/api/v1/prod-inout?param={"u":"","p":"","id":"IO53A096","cnt":"2","t":"2017-01-20 10:16:38","c1":"15/03/17 10:16:38","c2":"252858287D","c3":"1","c4":"IN","c5":"510000","c6":"1","c7":"10000","c8":"TU1","debug":"1"}
```

Contact
-----------------------------------
Contact with us via [Skype](skype:ntxinh.tintansoft), [Gmail](mailto:ntxinh@tintansoft.com).
