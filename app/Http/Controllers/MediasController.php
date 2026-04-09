<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Models\Medias;
use App\Models\Requetes;
use App\Support\Journalisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Phase 6 — CDC §4.4 : upload, stockage privé, diffusion contrôlée.
 */
class MediasController extends Controller
{
    public function store(StoreMediaRequest $request, Requetes $requete): RedirectResponse
    {
        $file = $request->file('fichier');
        $mime = $file->getMimeType();
        $isImage = str_starts_with((string) $mime, 'image/');
        $isVideo = str_starts_with((string) $mime, 'video/');
        $type = $isVideo ? 'video' : 'image';

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: 'bin'));
        $nomStockage = Str::uuid()->toString().'.'.$extension;

        $dossier = $requete->client_id.'/'.$requete->id;
        $cheminRelatif = $dossier.'/'.$nomStockage;

        $disk = Storage::disk('medias_interventions');
        $disk->putFileAs($dossier, $file, $nomStockage);

        $absolu = $disk->path($cheminRelatif);
        if ($isImage && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->compresserImageSiPossible($absolu, $extension);
        }

        $taille = $disk->exists($cheminRelatif) ? $disk->size($cheminRelatif) : 0;

        $media = Medias::query()->create([
            'requete_id' => $requete->id,
            'type' => $type,
            'chemin' => $cheminRelatif,
            'taille' => $taille,
        ]);

        Journalisation::trace(
            $request,
            'media_upload',
            sprintf('Requête #%d (%s), média #%d, %s', $requete->id, $requete->numeroTicket(), $media->id, $type)
        );

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', 'media_ajoute');
    }

    public function fichier(Requetes $requete, Medias $media): BinaryFileResponse
    {
        $this->authorize('view', $requete);
        abort_unless((int) $media->requete_id === (int) $requete->id, 404);

        $disk = Storage::disk('medias_interventions');
        abort_unless($disk->exists($media->chemin), 404);

        $absolu = $disk->path($media->chemin);
        $mime = mime_content_type($absolu) ?: 'application/octet-stream';

        return response()->file($absolu, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($media->chemin).'"',
        ]);
    }

    public function destroy(Request $request, Requetes $requete, Medias $media): RedirectResponse
    {
        $this->authorize('update', $requete);
        abort_unless((int) $media->requete_id === (int) $requete->id, 404);

        $disk = Storage::disk('medias_interventions');
        if ($disk->exists($media->chemin)) {
            $disk->delete($media->chemin);
        }

        $mediaId = $media->id;
        $media->delete();

        Journalisation::trace(
            $request,
            'media_supprime',
            sprintf('Requête #%d (%s), média #%d', $requete->id, $requete->numeroTicket(), $mediaId)
        );

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', 'media_supprime');
    }

    /**
     * Compression optionnelle (GD) — CDC §4.4 ; ignorée si extension non prise en charge ou GD absent.
     */
    private function compresserImageSiPossible(string $cheminAbsolu, string $extension): void
    {
        if (! extension_loaded('gd') || ! is_file($cheminAbsolu)) {
            return;
        }

        $data = @file_get_contents($cheminAbsolu);
        if ($data === false) {
            return;
        }

        $src = @imagecreatefromstring($data);
        if ($src === false) {
            return;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $maxW = (int) config('medias.image_max_width', 1920);
        if ($w <= $maxW) {
            imagedestroy($src);

            return;
        }

        $newH = (int) round($h * ($maxW / $w));
        $dst = imagescale($src, $maxW, $newH);
        imagedestroy($src);
        if ($dst === false) {
            return;
        }

        $quality = (int) config('medias.jpeg_quality', 82);
        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($dst, $cheminAbsolu, $quality),
            'png' => imagepng($dst, $cheminAbsolu, 6),
            'webp' => function_exists('imagewebp') ? imagewebp($dst, $cheminAbsolu, $quality) : false,
            default => false,
        };
        imagedestroy($dst);
    }
}
