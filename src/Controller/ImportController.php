<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImportController extends AbstractController
{
    /**
     * @Route("/import", name="app_import")
     */
    public function index()
    {

        $user = $this->getUser();

        return $this->render('import/index.html.twig', [
            'title' => 'Importing Player Journal Log',
            'upload_max' => ini_get('upload_max_filesize'),
            'user' => $user
        ]);
    }

    /**
     * @Route("/import/capi/auth", name="app_capi_auth")
     */
    public function capi_auth(Request $request)
    {

    }

}
