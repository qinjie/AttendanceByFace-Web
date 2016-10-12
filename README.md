# Attendance Taking server
**Attendance Taking** provides an innovative way to take attendance based on **Facial Recognition** and **Bluetooth Low Energy**. The system contains 3 components:
- Android App for Student
- Android App for Lecturer
- PHP Server
**This repository** is **PHP Server** of the system.

## Server Description
* **Attendance Taking Server** uses [Yii2 framework](http://www.yiiframework.com/). It provides API services for Mobile Apps.
* **API URL**: ```128.199.209.227/atk-ble/api/web/index.php/v1/```

## Resources
* [API Reference](https://github.com/qinjie/AttendanceByFace-Web/blob/develop/API.md)
* [Helpful commands when working with server code](COMMANDS.md)
* [Database Reference](https://github.com/qinjie/HopOn-Web/blob/develop/Database.md)
* [Yii2 documentation](http://www.yiiframework.com/doc-2.0/guide-index.html)

## Set up server in Ubuntu
Instructions for setting up server

1. Install ```LAMP```. [Guide](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04)
2. Install ```Git```. [Guide](https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-14-04)
3. Run ```git clone https://github.com/qinjie/HopOn-Web.git```
4. Install ```Composer```. [Guide](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-14-04)
5. Run ```composer install```.
6. Go to repository directory. Run ```php init```
7. Run this sql file ```docs/atk_ble.sql``` in repository directory.
8. Edit database configuration in ```common/config/main-local.php```
9. Change permissions of these folders to be writable. Run ```chmod -R 777 api/runtime api/web/assets backend/runtime backend/web/assets frontend/runtime frontend/web/assets console/runtime``` in repository directory.

