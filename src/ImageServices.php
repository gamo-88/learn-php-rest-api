<?php

class ImageServices
{
    public function __construct(private string $uploadDir = "uploads")
    {

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public static function base_url(): string
    {
        $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https://" : "http://";

        $host = $_SERVER["HTTP_HOST"];
        $scriptDir = rtrim(str_replace(basename($_SERVER["SCRIPT_NAME"]), "", $_SERVER["SCRIPT_NAME"]), DIRECTORY_SEPARATOR);

        return $protocol . $host . $scriptDir . "/";
    }



    public function uploadImage(array $file): ?string
    {
        //Annuler les fichiers qui ne sont pas image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            error_log("UnAuthorised file");
            return null;
        }

        //Annuler les fichier qui sont plus lourds que 2mega
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            error_log("Your file is too big");
            return null;
        }

        if (!empty($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uniqName = time() . "_" . basename($file["name"]);
            $forUrl = $this->uploadDir . "/" . $uniqName;
            $destination = $this->uploadDir . DIRECTORY_SEPARATOR . $uniqName;


            $hasMoved = move_uploaded_file($file["tmp_name"], $destination);
            if ($hasMoved) {

                $url = self::base_url() . $forUrl;
                return $url;
            } else {
                error_log("error on uploading image");
                return null;
            }
        } else {
            return null;
        }
    }


    public function deleteImage(string $imageUrl): bool
    {
        if (empty($imageUrl) || !is_string($imageUrl)) {
            return false;
        }

        $path = __DIR__ . '/../uploads/' . basename($imageUrl);

        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }
}
