<?php

Route::baseStatic("ViewController@index",[LoginMiddleware::class]);
Route::postBase("/admin/","AdminViewController@index");
Route::postBase("/admin/clientes","AdminViewController@clientes");
