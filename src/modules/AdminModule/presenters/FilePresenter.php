<?php

namespace ContrastCms\Application\AdminModule;

use Contrast\FileRepository;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\FileUpload;
use Nette\Image;

final class FilePresenter extends SecuredPresenter
{

    public function actionImagesJson() {

        // Fetch images

        $images = $this->context->getService("fileRepository")->getImages();

        $imagesArray = array();
        foreach($images as $image) {
            $img = array();
            $img['thumb'] = FileRepository::PUBLIC_PATH . $image->filename;
            $img['image'] = FileRepository::PUBLIC_PATH . $image->filename;
            $img['title'] = FileRepository::PUBLIC_PATH . $image->filename;

            $imagesArray[] = $img;
        }

        $this->sendResponse(new JsonResponse($imagesArray));
    }

    public function actionImageUploadAction() {

        $fileUpload = new FileUpload($_FILES['upload']);
        if($fileUpload->isImage()) {

            // Store image
            $file = $this->context->getService("fileRepository")->storeFile($fileUpload, "image");

            /*
            $image = Image::fromFile(FileRepository::PATH . $file->filename);
            $image->resize(592, null, Image::FIT);
            $image->save(FileRepository::PATH . "592_" . $file->filename);
            */

            // displaying file
            $array = array(
                'filelink' => FileRepository::PUBLIC_PATH . $file->filename
            );

            //$this->sendResponse(new JsonResponse($array));
			$CKEditorFuncNum = $this->getParameter("CKEditorFuncNum");
            $output = '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('. $CKEditorFuncNum .', "'.$array['filelink'].'","Uspesne nahrano");</script></body></html>';
            echo $output;die;
        }

        $this->sendResponse(new JsonResponse(array()));
    }

    public function actionFileUploadAction() {

        $fileUpload = new FileUpload($_FILES['upload']);

        if($fileUpload->isOk()) {
            // Store image
            $file = $this->context->getService("fileRepository")->storeFile($fileUpload, "file");

            // displaying file
            $array = array(
                'filelink' => FileRepository::PUBLIC_PATH . $file->filename
            );

            $output = '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('. $_GET['CKEditorFuncNum'] .', "'.$array['filelink'].'","Uspesne nahrano");</script></body></html>';
            echo $output;die;
        }
    }
}