<?php

if (!function_exists('is_local_access')) {
    /**
     * Memeriksa apakah request berasal dari jaringan lokal (WiFi Sekolah / Tailscale / Localhost)
     * atau dari internet (via Cloudflare Tunnel).
     *
     * @return bool
     */
    function is_local_access()
    {
        $request = \Config\Services::request();

        // Jika ada header CF-Connecting-IP, dipastikan masuk dari internet via Cloudflare
        if ($request->getHeader('CF-Connecting-IP') !== null) {
            return false;
        }

        $ip = $request->getIPAddress();

        // Localhost
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }

        // Subnet WiFi Sekolah: 10.10.10.0/23 (10.10.10.0 s.d 10.10.11.255)
        if (ip_in_range($ip, '10.10.10.0/23')) {
            return true;
        }

        // Subnet Tailscale: 100.64.0.0/10 (100.64.0.0 s.d 100.127.255.255)
        if (ip_in_range($ip, '100.64.0.0/10')) {
            return true;
        }

        return false;
    }
}

if (!function_exists('ip_in_range')) {
    /**
     * Memeriksa apakah IP address berada di dalam range CIDR tertentu.
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    function ip_in_range($ip, $range)
    {
        if (strpos($range, '/') === false) {
            $range .= '/32';
        }
        list($subnet, $bits) = explode('/', $range);

        $ip_dec = ip2long($ip);
        $subnet_dec = ip2long($subnet);
        if ($ip_dec === false || $subnet_dec === false) {
            return false;
        }

        $wildcard_dec = pow(2, (32 - $bits)) - 1;
        $netmask_dec = ~$wildcard_dec;

        return ($ip_dec & $netmask_dec) == ($subnet_dec & $netmask_dec);
    }
}

if (!function_exists('get_photo_display_url')) {
    /**
     * Mendapatkan URL tampilan foto PKL berdasarkan tipe akses (lokal vs internet).
     * Jika akses lokal, arahkan ke file lokal di server Debian.
     * Jika akses internet, arahkan ke CDN Supabase.
     *
     * @param string|null $url URL foto yang tersimpan di database (bisa local path atau Supabase URL)
     * @return string URL publik yang valid untuk tag <img>
     */
    function get_photo_display_url($url)
    {
        if (empty($url)) {
            return '';
        }

        // Jika URL berupa path lokal (e.g. /uploads/pkl/file.jpg)
        if (str_starts_with($url, '/')) {
            return base_url($url);
        }

        // Jika URL dari Supabase (e.g. https://.../pkl/filename.jpg)
        // Ekstrak nama filenya saja
        $filename = basename($url);

        // Jika diakses secara lokal dan file fisik ada di server Debian, gunakan local path
        if (is_local_access()) {
            $localPath = ROOTPATH . '../dashboard/public/uploads/pkl/' . $filename;
            if (file_exists($localPath)) {
                return base_url('uploads/pkl/' . $filename);
            }
        }

        // Fallback gunakan URL yang tertera di database (Supabase URL)
        return $url;
    }
}
