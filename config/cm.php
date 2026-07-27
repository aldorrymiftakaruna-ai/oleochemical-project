<?php

/*
|--------------------------------------------------------------------------
| Condition Monitoring (CM) Configuration
|--------------------------------------------------------------------------
|
| File ini berisi konfigurasi untuk modul Condition Monitoring, termasuk
| threshold untuk insight otomatis dari trend slope vibrasi dan parameter
| lainnya yang perlu mudah diubah tanpa mengubah kode.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Vibrasi — Threshold Trending & Insight
    |--------------------------------------------------------------------------
    |
    | Konfigurasi deteksi slope kenaikan signifikan untuk rekomendasi
    | PdM action berdasarkan data trending vibrasi motor (NDEV, NDEH, NDEA).
    |
    */
    'vibration_insight' => [

        // Jumlah minimal titik data terakhir untuk perhitungan slope
        'min_data_points' => 3,

        // Persentase kenaikan dianggap "signifikan" dibanding rata-rata
        // N titik sebelumnya (default: kenaikan > 20%)
        'significant_rise_pct' => 20,

        // Jumlah titik data untuk rata-rata baseline perbandingan
        'baseline_count' => 3,

        // Proyeksi: berapa hari ke depan untuk cek apakah slope akan
        // menyentuh batas Danger (default: 30 hari)
        'projection_days' => 30,

        // Threshold vibrasi (mm/s) untuk level Alarm & Danger
        // Berdasarkan ISO 10816-3 untuk pompa/screw (Group 2)
        'thresholds' => [
            'alarm'  => 7.1,  // mm/s — batas Alarm
            'danger' => 11.2, // mm/s — batas Danger
        ],
    ],

];
