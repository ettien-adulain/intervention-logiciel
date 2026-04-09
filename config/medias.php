<?php

/**
 * Phase 6 — pièces jointes requêtes (CDC §4.4, §6).
 */
return [

    'max_upload_ko' => (int) env('MEDIA_MAX_UPLOAD_KO', 20480),

    'mimes' => [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/quicktime', 'video/webm',
    ],

    /** Extensions autorisées (alignées sur `mimes`, renommage UUID côté stockage). */
    'extensions_autorisees' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'webm'],

    /**
     * Cohérence extension déclarée ↔ MIME détecté (contre les fichiers déguisés).
     *
     * @var array<string, list<string>>
     */
    'mime_par_extension' => [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'mp4' => ['video/mp4'],
        'mov' => ['video/quicktime'],
        'webm' => ['video/webm'],
    ],

    /** Largeur max après compression optionnelle (GD). */
    'image_max_width' => 1920,

    'jpeg_quality' => 82,

];
