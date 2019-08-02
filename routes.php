<?php

Route::get('jobs', 'JobController@index');
Route::post('jobs_retry', 'JobController@retry');
Route::delete('jobs_forget', 'JobController@forget');

Route::get('supervisors', 'SupervisorController@index');
Route::get('supervisors_state', 'SupervisorController@getState');
Route::get('supervisors_pid', 'SupervisorController@getPID');
Route::get('supervisors_processes', 'SupervisorController@getAllProcessInfo');
Route::post('supervisors_restart', 'SupervisorController@restart');
Route::post('supervisors_shutdown', 'SupervisorController@shutdown');
Route::post('supervisors_start_process', 'SupervisorController@startProcess');
Route::post('supervisors_start_process_group', 'SupervisorController@startProcessGroup');
Route::post('supervisors_stop_process', 'SupervisorController@stopProcess');
Route::post('supervisors_stop_process_group', 'SupervisorController@stopProcessGroup');
