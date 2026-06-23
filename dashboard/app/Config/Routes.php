<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// Public routes (Auth)
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');
$routes->get('bantuan', 'Bantuan::index');


// Protected routes (Admin / BK / Guru / Siswa via AuthFilter)
$routes->group('', ['filter' => 'auth'], function ($routes) {
    // Dashboard page
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('profile', 'Auth::profile');
    $routes->post('profile/change-password', 'Auth::changePassword');

    // Dashboard Analytics
    $routes->get('analytics', 'Analytics::index');

    // Tahun Pelajaran (Admin-only CRUD)
    $routes->get('tahun-pelajaran', 'TahunPelajaran::index');
    $routes->post('tahun-pelajaran/create', 'TahunPelajaran::create');
    $routes->post('tahun-pelajaran/update/(:num)', 'TahunPelajaran::update/$1');
    $routes->post('tahun-pelajaran/set-active/(:num)', 'TahunPelajaran::setActive/$1');
    $routes->post('tahun-pelajaran/delete/(:num)', 'TahunPelajaran::delete/$1');

    // Kalender Akademik
    $routes->get('kalender-akademik', 'KalenderAkademik::index');
    $routes->post('kalender-akademik/save', 'KalenderAkademik::save');
    $routes->post('kalender-akademik/delete/(:num)', 'KalenderAkademik::deleteRow/$1');

    // Students CRUD
    $routes->get('students', 'Student::index');
    $routes->get('students/export', 'Student::exportExcel');
    $routes->post('students/create', 'Student::create');
    $routes->post('students/update/(:num)', 'Student::update/$1');
    $routes->post('students/delete/(:num)', 'Student::delete/$1');
    $routes->get('students/details/(:num)', 'Student::details/$1');
    $routes->post('students/reset-password/(:num)', 'Student::resetPassword/$1');
    $routes->post('students/import', 'Student::import');

    // Allowed Numbers (Whitelist Guru & Staf)
    $routes->get('allowed-numbers', 'AllowedNumber::index');
    $routes->post('allowed-numbers/create', 'AllowedNumber::create');
    $routes->post('allowed-numbers/update/(:num)', 'AllowedNumber::update/$1');
    $routes->post('allowed-numbers/delete/(:num)', 'AllowedNumber::delete/$1');
    $routes->post('allowed-numbers/import', 'AllowedNumber::import');

    // Timetables/Schedules (Jadwal Pelajaran)
    $routes->get('schedules', 'Schedule::index');
    $routes->post('schedules/create', 'Schedule::create');
    $routes->post('schedules/update/(:num)', 'Schedule::update/$1');
    $routes->post('schedules/delete/(:num)', 'Schedule::delete/$1');

    // Message Logs (Audit Chat WA)
    $routes->get('logs', 'MessageLog::index');

    // WhatsApp Bot Settings
    $routes->get('whatsapp-settings', 'WhatsappSettings::index');
    $routes->post('whatsapp-settings/logout', 'WhatsappSettings::logout');
    $routes->post('whatsapp-settings/update-templates', 'WhatsappSettings::updateTemplates');
    $routes->post('whatsapp-settings/update-cron', 'WhatsappSettings::updateCron');
    $routes->post('whatsapp-settings/update-groups', 'WhatsappSettings::updateGroups');
    $routes->post('whatsapp-settings/update-targets', 'WhatsappSettings::updateBroadcastTargets');
    $routes->post('whatsapp-settings/create-cron', 'WhatsappSettings::createCron');
    $routes->post('whatsapp-settings/delete-cron/(:segment)', 'WhatsappSettings::deleteCron/$1');
    $routes->post('whatsapp-settings/join-group', 'WhatsappSettings::joinGroup');

    // Attendance
    $routes->get('attendance', 'Attendance::index');
    $routes->get('attendance/class/(:any)', 'Attendance::class/$1');
    $routes->post('attendance/save-class', 'Attendance::saveClass');
    $routes->post('attendance/set-wali-kelas', 'Attendance::setWaliKelas');
    $routes->post('attendance/delete/(:num)', 'Attendance::deleteAttendanceHistory/$1');
    $routes->get('attendance/print-html/(:any)', 'Attendance::printHtml/$1');

    // PKL
    $routes->get('pkl', 'Pkl::index');
    $routes->get('pkl/takeover', 'Pkl::takeoverReport');
    $routes->post('pkl/submit', 'Pkl::submitReport');
    $routes->get('pkl/groups', 'Pkl::groups');
    $routes->post('pkl/groups/create', 'Pkl::createGroup');
    $routes->post('pkl/groups/import', 'Pkl::importGroups');
    $routes->post('pkl/groups/update/(:num)', 'Pkl::updateGroup/$1');
    $routes->post('pkl/groups/delete/(:num)', 'Pkl::deleteGroup/$1');
    $routes->get('pkl/reports', 'Pkl::reports');
    $routes->post('pkl/delete-report/(:num)', 'Pkl::deleteReport/$1');
    $routes->get('pkl/riwayat/(:any)', 'Pkl::riwayat/$1');
    $routes->get('pkl/rekap-siswa/(:any)', 'Pkl::rekapSiswa/$1');
    $routes->get('pkl/print-weekly-pdf/(:any)/(:any)', 'Pkl::printWeeklyPdf/$1/$2');
    $routes->post('pkl/update-settings', 'Pkl::updateSettings');
    $routes->post('pkl/resend-broadcast', 'Pkl::resendBroadcast');
    $routes->post('pkl/delete-all-reports', 'Pkl::deleteAllReports');
    $routes->post('pkl/reset-all-ketua-passwords', 'Pkl::resetAllKetuaPasswords');

    // Violations (Poin Pelanggaran)
    $routes->get('violations', 'Violation::index');
    $routes->post('violations/create', 'Violation::create');
    $routes->post('violations/delete/(:num)', 'Violation::delete/$1');

    // Counseling (Catatan BK)
    $routes->get('counseling', 'Counseling::index');
    $routes->post('counseling/create', 'Counseling::create');
    $routes->post('counseling/delete/(:num)', 'Counseling::delete/$1');

    // Achievements (Prestasi)
    $routes->get('prestasi', 'Achievement::index');
    $routes->post('prestasi/create', 'Achievement::create');
    $routes->post('prestasi/delete/(:num)', 'Achievement::delete/$1');

    // Feedbacks (Kotak Suara)
    $routes->get('feedbacks', 'Feedback::index');
    $routes->post('feedbacks/create', 'Feedback::create');
    $routes->post('feedbacks/toggle/(:num)', 'Feedback::toggle/$1');
    $routes->post('feedbacks/delete/(:num)', 'Feedback::delete/$1');

    // Hari Libur (Admin only) — handled by KalenderAkademik controller
    $routes->get('hari-libur', 'KalenderAkademik::index');
    $routes->post('hari-libur/add', 'KalenderAkademik::addHoliday');
    $routes->post('hari-libur/delete/(:num)', 'KalenderAkademik::deleteHoliday/$1');

    // API: Holiday status today (for JS dashboard label)
    $routes->get('api/holiday-status', 'KalenderAkademik::todayStatus');

    // --- BKK & TRACER STUDY MODULE ---
    $routes->group('bkk', function($routes) {
        $routes->get('dashboard', 'Bkk::index');
        $routes->get('data_alumni', 'Bkk::data_alumni');
        $routes->get('mitra_industri', 'Bkk::mitra_industri');
        $routes->get('mou_iduka', 'Bkk::mou_iduka');
        $routes->get('kunjungan_industri', 'Bkk::kunjungan_industri');

        // CRUD Routes (Form POST)
        $routes->post('store_alumni', 'Bkk::store_alumni');
        $routes->post('delete_alumni', 'Bkk::delete_alumni');

        $routes->post('store_mou', 'Bkk::store_mou');
        $routes->post('delete_mou', 'Bkk::delete_mou');

        $routes->post('store_kunjungan', 'Bkk::store_kunjungan');
        $routes->post('delete_kunjungan', 'Bkk::delete_kunjungan');
    });
});
