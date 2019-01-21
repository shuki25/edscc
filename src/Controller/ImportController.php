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
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/import", name="app_import")
     */
    public function index()
    {

        return $this->render('import/index.html.twig', [
            'title' => 'Importing Player Journal Log',
        ]);
    }

    /**
     * @Route("/import/upload", name="app_import_upload")
     */
    public function upload($token, Request $request, UploadedFile $uploadedFile)
    {
        $user = $this->getUser();

        if($request->getMethod() == 'POST') {

        }

        return $this->redirectToRoute('app_import');

    }

}
