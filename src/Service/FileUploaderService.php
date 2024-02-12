<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileUploaderService
{
    private string $imageUploader;

    private ValidatorInterface $validator;

    public function __construct(
        string                 $imageUploader,
        ValidatorInterface     $validator
    )
    {
        $this->imageUploader = $imageUploader;
        $this->validator = $validator;
    }

    public function uploadImage(UploadedFile $uploadedFile): string
    {
        try {
            $errors = $this->validator->validate(
                $uploadedFile,
                new File([
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/jpg',
                        'image/png'
                    ],
                    'maxSize' => '2M'
                ])
            );
            if (count($errors) > 0){
                throw new ValidatorException($errors);
            }
            $fileName = md5(uniqid()) . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move($this->getImageFolder(), $fileName);
            return $fileName;
        } catch (FileException $fileException) {
            throw new Exception($fileException->getMessage());
        }
    }

    public function getImageFolder(): ?string
    {
        return $this->imageUploader;
    }
}