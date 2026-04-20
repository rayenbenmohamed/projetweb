<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        // Cloudinary needs the configuration explicitly if not in getenv()
        $this->cloudinary = new Cloudinary($_ENV['CLOUDINARY_URL'] ?? null);
    }

    /**
     * Uploads a file to Cloudinary and returns the secure URL.
     * 
     * @param UploadedFile $file
     * @param string $folder The folder in Cloudinary (e.g. 'cvs', 'avatars')
     * @return string
     */
    public function uploadFile(UploadedFile $file, string $folder = 'general'): string
    {
        $uploaded = $this->uploadFileDetailed($file, $folder);

        return $uploaded['secure_url'];
    }

    /**
     * Upload a file and return Cloudinary metadata.
     *
     * @return array{secure_url: string, public_id: string}
     */
    public function uploadFileDetailed(UploadedFile $file, string $folder = 'general'): array
    {
        $uploadResult = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => 'syfonu/' . $folder,
                'resource_type' => 'auto' // Important for PDFs (CVs)
            ]
        );

        return [
            'secure_url' => (string) ($uploadResult['secure_url'] ?? ''),
            'public_id' => (string) ($uploadResult['public_id'] ?? ''),
        ];
    }

    /**
     * Build a "professional profile photo" transformed URL from a Cloudinary URL.
     */
    public function getProfessionalPhotoUrl(string $secureUrl): string
    {
        $clean = trim($secureUrl);
        if ($clean === '' || !str_contains($clean, '/upload/')) {
            return $secureUrl;
        }

        $transformation = 'c_fill,g_face,w_900,h_900/e_improve/e_sharpen:40/q_auto:good/f_auto';

        return preg_replace('#/upload/#', '/upload/' . $transformation . '/', $clean, 1) ?: $secureUrl;
    }

    /**
     * Deletes a file from Cloudinary given its public ID.
     */
    public function deleteFile(string $publicId): void
    {
        $this->cloudinary->uploadApi()->destroy($publicId);
    }
}
