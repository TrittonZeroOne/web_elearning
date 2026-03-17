<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */


// ── AUTH ─────────────────────────────────────────────────────
$routes->get('/',       'AuthController::login');
$routes->get('login',   'AuthController::login');
$routes->post('login',  'AuthController::doLogin');
$routes->get('logout',  'AuthController::logout');

// ── DASHBOARD ─────────────────────────────────────────────────
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);

// ── ADMIN ─────────────────────────────────────────────────────
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
    $routes->get('users',                         'Admin\UsersController::index');
    $routes->get('users/create',                  'Admin\UsersController::create');
    $routes->post('users/store',                  'Admin\UsersController::store');
    $routes->get('users/edit/(:segment)',          'Admin\UsersController::edit/$1');
    $routes->post('users/update/(:segment)',       'Admin\UsersController::update/$1');
    $routes->post('users/delete/(:segment)',       'Admin\UsersController::delete/$1');
    $routes->post('users/change-password/(:segment)',  'Admin\UsersController::changePassword/$1');
    $routes->get('users/debug-auth/(:segment)',    'Admin\UsersController::debugAuth/$1');
    $routes->get('classes',                        'Admin\ClassesController::index');
    $routes->post('classes/store',                 'Admin\ClassesController::store');
    $routes->post('classes/delete/(:segment)',     'Admin\ClassesController::delete/$1');
    $routes->get('subjects',                       'Admin\SubjectsController::index');
    $routes->post('subjects/store',                'Admin\SubjectsController::store');
    $routes->get('subjects/edit/(:num)',           'Admin\SubjectsController::edit/$1');
    $routes->post('subjects/update/(:num)',        'Admin\SubjectsController::update/$1');
    $routes->post('subjects/delete/(:num)',        'Admin\SubjectsController::delete/$1');
    $routes->get('announcements',                  'Admin\AnnouncementsController::index');
    $routes->post('announcements/store',           'Admin\AnnouncementsController::store');
    $routes->post('announcements/update/(:num)',   'Admin\AnnouncementsController::update/$1');
    $routes->post('announcements/delete/(:num)',   'Admin\AnnouncementsController::delete/$1');
    $routes->get('statistics',                     'Admin\StatisticsController::index');
    // Chat (spesifik sebelum (:segment))
    $routes->get('chat',                           'Admin\ChatController::index');
    $routes->post('chat/send',                     'Admin\ChatController::send');
    $routes->get('chat/poll/(:segment)/(:num)',    'Admin\ChatController::poll/$1/$2');
    $routes->post('chat/read/(:segment)',          'Admin\ChatController::read/$1');
    $routes->get('chat/debug',                     'Admin\ChatController::debug');
    $routes->get('chat/(:segment)',                'Admin\ChatController::conversation/$1');
});

// ── TEACHER ───────────────────────────────────────────────────
$routes->group('teacher', ['filter' => 'role:teacher'], function ($routes) {
    $routes->get('subjects',                              'Teacher\SubjectsController::index');
    $routes->get('subjects/(:num)/materi',               'Teacher\SubjectsController::materi/$1');
    $routes->get('subjects/(:num)/tugas',                'Teacher\SubjectsController::tugas/$1');
    $routes->get('subjects/(:num)/absensi',              'Teacher\SubjectsController::absensi/$1');
    $routes->get('subjects/(:num)/diskusi',              'Teacher\SubjectsController::diskusi/$1');
    $routes->get('subjects/(:num)',                      'Teacher\SubjectsController::materi/$1');
    $routes->post('materials/store',                     'Teacher\MaterialsController::store');
    $routes->post('materials/delete/(:num)',             'Teacher\MaterialsController::delete/$1');
    $routes->post('assignments/store',                   'Teacher\AssignmentsController::store');
    $routes->post('assignments/delete/(:num)',           'Teacher\AssignmentsController::delete/$1');
    $routes->post('submissions/grade/(:num)',            'Teacher\AssignmentsController::grade/$1');
    $routes->post('attendance/save',                     'Teacher\AttendanceController::save');
    $routes->get('attendance/(:num)/export',             'Teacher\AttendanceController::export/$1');
    $routes->post('discussion/send',                     'Teacher\DiscussionController::send');
    $routes->get('discussion/(:segment)/poll/(:num)',    'Teacher\DiscussionController::poll/$1/$2');
    // Chat: admin + siswa (spesifik sebelum (:segment))
    $routes->get('chat',                                 'Teacher\ChatController::index');
    $routes->post('chat/send',                           'Teacher\ChatController::send');
    $routes->get('chat/poll/(:segment)/(:num)',          'Teacher\ChatController::poll/$1/$2');
    $routes->get('chat/(:segment)',                      'Teacher\ChatController::conversation/$1');
});

// ── STUDENT ───────────────────────────────────────────────────
$routes->group('student', ['filter' => 'role:student'], function ($routes) {
    $routes->get('subjects',                              'Student\SubjectsController::index');
    $routes->get('subjects/(:num)/materi',               'Student\SubjectsController::materi/$1');
    $routes->get('subjects/(:num)/tugas',                'Student\SubjectsController::tugas/$1');
    $routes->get('subjects/(:num)/absensi',              'Student\SubjectsController::absensi/$1');
    $routes->get('subjects/(:num)/diskusi',              'Student\SubjectsController::diskusi/$1');
    $routes->get('subjects/(:num)',                      'Student\SubjectsController::materi/$1');
    $routes->post('assignments/submit',                  'Student\AssignmentsController::submit');
    $routes->post('discussion/send',                     'Student\DiscussionController::send');
    $routes->get('discussion/(:segment)/poll/(:num)',    'Student\DiscussionController::poll/$1/$2');
    // Chat dengan guru (spesifik sebelum (:segment))
    $routes->get('chat',                                 'Student\ChatController::index');
    $routes->post('chat/send',                           'Student\ChatController::send');
    $routes->get('chat/poll/(:segment)/(:num)',          'Student\ChatController::poll/$1/$2');
    $routes->get('chat/(:segment)',                      'Student\ChatController::conversation/$1');
});