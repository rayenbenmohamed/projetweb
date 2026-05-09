<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Crop;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Format;
use Cloudinary\Transformation\Quality;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryUploader
{
    private ?Cloudinary $cloudinary;

    public function __construct(?string $cloudinaryUrl = null)
    {
        $this->cloudinary = ($cloudinaryUrl && trim($cloudinaryUrl) !== '')
            ? new Cloudinary($cloudinaryUrl)
            : null;
    }

    /**
     * Upload un logo d'entreprise sur Cloudinary.
     * Redimensionnement auto 400×400 crop fill, format WebP, qualité auto.
     *
     * @return array{url: string, publicId: string}
     * @throws \RuntimeException si le fichier n'est pas une image ou dépasse 2 Mo
     */
    public function uploadLogo(UploadedFile $file): array
    {
        // If Cloudinary is not configured, return empty array
        if (!$this->cloudinary) {
            return [
                'url' => '',
                'publicId' => '',
            ];
        }

        // Validation type MIME (via le type déclaré par le client + extension)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $clientMime   = $file->getClientMimeType();
        if (!in_array($clientMime, $allowedMimes, true)) {
            throw new \RuntimeException('Le fichier doit être une image (JPEG, PNG, GIF, WebP ou SVG).');
        }

        // Validation taille (2 Mo max)
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \RuntimeException('Le logo ne doit pas dépasser 2 Mo.');
        }

        $result = $this->cloudinary->uploadApi()->upload(
            $file->getPathname(),
            [
                'folder'          => 'job_offres/logos',
                'transformation'  => [
                    ['width' => 400, 'height' => 400, 'crop' => 'fill', 'gravity' => 'auto'],
                    ['fetch_format' => 'auto', 'quality' => 'auto'],
                ],
                'resource_type'   => 'image',
            ]
        );

        return [
            'url'      => (string) ($result['secure_url'] ?? ''),
            'publicId' => (string) ($result['public_id']  ?? ''),
        ];
    }

    /**
     * Supprime un logo sur Cloudinary via son public_id.
     */
    public function deleteLogo(string $publicId): void
    {
        if (!$this->cloudinary || $publicId === '') {
            return;
        }
        try {
            $this->cloudinary->uploadApi()->destroy($publicId, ['resource_type' => 'image']);
        } catch (\Throwable) {
            // Suppression non bloquante
        }
    }
}
