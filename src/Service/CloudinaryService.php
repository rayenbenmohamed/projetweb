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
        $uploadResult = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => 'syfonu/' . $folder,
                'resource_type' => 'auto' // Important for PDFs (CVs)
            ]
        );

        return $uploadResult['secure_url'];
    }

    /**
     * Deletes a file from Cloudinary given its public ID.
     */
    public function deleteFile(string $publicId): void
    {
        $this->cloudinary->uploadApi()->destroy($publicId);
    }
}
