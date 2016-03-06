<?php namespace App;
/**
 * Application route registry
 */

$routes = array();
$routes['/'] = 'Home/index';

$routes['/admin'] = 'Admin/index';

$routes['/admin/routes'] = 'AdminPage/routes';
$routes['get:/admin/route/create'] = 'AdminPage/route_create';
$routes['post:/admin/route/create'] = 'AdminPage/process_route_create';
$routes['post:/admin/route/delete'] = 'AdminPage/process_route_delete';
$routes['get:/admin/route/edit/(:any)'] = 'AdminPage/route_edit';
$routes['post:/admin/route/edit/(:any)'] = 'AdminPage/process_route_edit';
$routes['get:/admin/settings'] = 'AdminPage/settings';
$routes['get:/admin/setting/create'] = 'AdminPage/setting_create';
$routes['post:/admin/setting/create'] = 'AdminPage/process_setting_create';
$routes['get:/projects'] = 'ProjectsPage/index';
$routes['get:/admin/models'] = 'Model/index';
$routes['get:/admin/model/create'] = 'Model/create';
$routes['post:/admin/model/create'] = 'Model/process_create';
$routes['post:/admin/model/delete/(:str)'] = 'Model/delete';
$routes['post:/admin/model/table/create'] = 'Model/table_create';
$routes['get:/admin/model/fields/edit/(:str)'] = 'Model/edit_fields';
$routes['post:/admin/model/fields/edit/(:str)'] = 'Model/process_edit_fields';
$routes['post:/admin'] = 'Admin/index';
$routes['post:/admin/model/migration/create'] = 'Model/migration_create';
$routes['post:/admin/model/table/drop'] = 'Model/drop_table';
$routes['get:/admin/model/records/(:str)'] = 'ModelRecord/records';
$routes['get:/admin/model/record/create/(:str)'] = 'ModelRecord/create';
$routes['post:/admin/model/record/create/(:str)'] = 'ModelRecord/process_create';
$routes['post:/admin/model/record/delete/(:str)'] = 'ModelRecord/delete';
$routes['get:/admin/model/record/edit/(:str)/(:num)'] = 'ModelRecord/edit';
$routes['post:/admin/model/record/edit/(:str)/(:num)'] = 'ModelRecord/process_edit';


return $routes;