// ============================================================
// INITIALIZATION SUPABASE CLIENT
// ============================================================
const SUPABASE_URL = "https://ilnfzebpoczlwocpzlop.supabase.co";
const SUPABASE_KEY = "sb_publishable_75Y_xJl_9ntQt3R2TnkSUw_pktf_FbV";
// Menggunakan var (bukan const) untuk menghindari SyntaxError tabrakan deklarasi dengan global var di js/supabase.js
var supabase = (window.supabase && typeof window.supabase.createClient === 'function') 
    ? window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY) 
    : window.supabase;

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // AUTH GUARD — Redirect ke login jika belum login
    // ============================================================
    var userRole    = sessionStorage.getItem('role');
    var currentPage = window.location.pathname.split('/').pop();
    var publicPages = ['index.html', '', '/'];

    if (!userRole && publicPages.indexOf(currentPage) === -1) {
        window.location.href = 'index.html';
        return;
    }

    // ============================================================
    // NAMA ADMIN — Tampilkan dari localStorage (diset saat login)
    // ============================================================
    var adminName   = localStorage.getItem('adminName') || 'Admin BKK SMK NU Darussalam';
    var displayObj  = document.getElementById('displayAdminName');
    var initialObj  = document.getElementById('displayAdminInitials');
    if (displayObj)  displayObj.innerText = adminName;
    if (initialObj)  initialObj.innerText = adminName.charAt(0).toUpperCase();

    // ============================================================
    // LOGOUT
    // ============================================================
    var logoutBtns = document.querySelectorAll('a[href="index.html"]');
    logoutBtns.forEach(function(btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            sessionStorage.removeItem('role');
            localStorage.removeItem('adminName');
            window.location.href = 'index.html';
        });
    });
});
