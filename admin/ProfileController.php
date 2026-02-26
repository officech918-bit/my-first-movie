<?php

namespace App\Http\Controllers;

use App\Models\WebUser;
use App\Services\Validation\Validator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as Capsule;

class ProfileController
{
    public function show(Request $request, Response $response)
    {
        $user = WebUser::find($_SESSION['uid']);
        // This is a placeholder for a proper view rendering engine
        // For now, we'll just pass the data to the old view file
        ob_start();
        include_once __DIR__ . '/../../../../admin/profile.php';
        $pageContent = ob_get_clean();
        $response->getBody()->write($pageContent);
        return $response;
    }

    public function updateInfo(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $user = WebUser::find($_SESSION['uid']);

        $validator = new Validator();
        $validator->validate($data, [
            'first_name' => 'required|max:30',
            'last_name' => 'required|max:30',
            'email' => 'required|email|unique:web_users,email,' . $user->uid . ',uid',
            'contact' => 'required|max:20',
        ]);

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old'] = $data;
            return $response->withHeader('Location', $admin_path . 'profile.php')->withStatus(302);
        }

        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'contact' => $data['contact'],
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        $_SESSION['success'] = 'Profile updated successfully.';
        return $response->withHeader('Location', $admin_path . 'profile.php')->withStatus(302);
    }

    public function updateAvatar(Request $request, Response $response)
    {
        $user = WebUser::find($_SESSION['uid']);
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['avatar'] ?? null;

        if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile(
                'assets/admin/layout/img/avatars/',
                $uploadedFile
            );

            // Delete old avatar if it exists
            if ($user->avatar && file_exists('assets/admin/layout/img/avatars/' . $user->avatar)) {
                unlink('assets/admin/layout/img/avatars/' . $user->avatar);
            }
            if ($user->avatar_thumb && file_exists('assets/admin/layout/img/avatars/' . $user->avatar_thumb)) {
                unlink('assets/admin/layout/img/avatars/' . $user->avatar_thumb);
            }

            $user->update([
                'avatar' => $filename,
                'avatar_thumb' => 'thumb_' . $filename,
                'avatar_path' => 'assets/admin/layout/img/avatars/',
            ]);

            $_SESSION['success'] = 'Avatar updated successfully.';
        } else {
            $_SESSION['errors'] = ['avatar' => ['Failed to upload avatar.']];
        }

        return $response->withHeader('Location', $admin_path . 'profile.php')->withStatus(302);
    }

    public function updatePassword(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $user = WebUser::find($_SESSION['uid']);

        $validator = new Validator();
        $validator->validate($data, [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_new_password' => 'required|same:new_password',
        ]);

        if (!password_verify($data['current_password'], $user->password)) {
            $validator->addError('current_password', 'Current password does not match.');
        }

        if ($validator->fails()) {
            $_SESSION['errors'] = $validator->errors();
            return $response->withHeader('Location', $admin_path . 'profile.php')->withStatus(302);
        }

        $user->update([
            'password' => password_hash($data['new_password'], PASSWORD_DEFAULT),
            'last_update' => date('Y-m-d H:i:s'),
        ]);

        $_SESSION['success'] = 'Password updated successfully.';
        return $response->withHeader('Location', $admin_path . 'profile.php')->withStatus(302);
    }

    /**
     * Moves the uploaded file to the upload directory and assigns it a unique name
     * to avoid overwriting existing files.
     *
     * @param string $directory The directory to move the file to
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile The file to move
     *
     * @return string The filename of the moved file
     */
    private function moveUploadedFile(string $directory, \Psr\Http\Message\UploadedFileInterface $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        // Create a thumbnail
        $this->createThumbnail($directory . $filename, $directory . 'thumb_' . $filename, 150);


        return $filename;
    }

    private function createThumbnail($source_image_path, $thumbnail_image_path, $thumbnail_width)
    {
        list($source_width, $source_height, $source_type) = getimagesize($source_image_path);
        switch ($source_type) {
            case IMAGETYPE_GIF:
                $source_gdim = imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_JPEG:
                $source_gdim = imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $source_gdim = imagecreatefrompng($source_image_path);
                break;
        }

        $source_aspect_ratio = $source_width / $source_height;
        $thumbnail_height = $thumbnail_width / $source_aspect_ratio;

        $thumbnail_gdim = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

        imagecopyresampled(
            $thumbnail_gdim,
            $source_gdim,
            0, 0, 0, 0,
            $thumbnail_width,
            $thumbnail_height,
            $source_width,
            $source_height
        );

        imagejpeg($thumbnail_gdim, $thumbnail_image_path, 90);
        imagedestroy($source_gdim);
        imagedestroy($thumbnail_gdim);
    }
}