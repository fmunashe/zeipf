[//]: # (<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://selfservice.zetdc.co.zw/logo.png" width="400" alt="Laravel Logo"></a></p>)

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About ZEIPF USSD Middleware

Zesa Middleware is a laravel driven application that was developed to map Econet ussd gateway payload and response to a php driven zesa ussd application developed by Zesa dev team. The choice for language was specifically request by the Zesa dev team to make it easy to maintain for their dev team which is familiar with php. Laravel then naturally became our language of choice to meet this requirement. Task being addressed here are:

- Receive a menu request from the econet ussd gateway.
- convert the XML payload to a json request required by the Zesa php application
- Convert the json response back into XML and push it to the ussd gateway


## Queries and Support
For queries and support, get in touch with EBS dev team
