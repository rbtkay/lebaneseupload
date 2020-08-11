<?php

namespace App\Controller;

use App\Service\AwsS3;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class DocumentController extends AbstractController
{
    /**
     * @Route("/document", name="getDocuments", methods={"GET"})
     */
    public function getAllDocument()
    {
        return $this->render('document/index.html.twig', [
            'controller_name' => 'Get All Documents',
            'status' => null
        ]);
    }

    /**
     * @Route("/read/{page}", name="read", methods={"GET"})
     * @param int $page
     * @return Response
     */
    public function readDocuments($page = 0)
    {
        $s3 = new AwsS3();
        $result = $s3->getResources($page);

        return $this->render('document/read.html.twig', [
            'controller_name' => 'Get All Documents',
            'urls' => $result["itemInPage"],
            'numberOfPages'=> $result["totalNumberOfPages"]
        ]);
    }

    /**
     * @Route("/document", name="addDocument", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function addDocument(Request $request)
    {
        $s3 = new AwsS3();

        $file = $request->files->get("upload_file");

        if (is_null($file)) {
            $status = [
                "msg" => "Where is your file ?",
                "style" => "alert alert-danger"
            ];
        } else {
            $filename = $s3->addOneResource($file);
            if (is_null($filename)) {
                $status = [
                    "msg" => "Incorrect extension (only images are allowed for now)",
                    "style" => "alert alert-danger"
                ];
            } else {
                $status = [
                    "msg" => "File $filename successfully uploaded",
                    "style" => "alert alert-success"
                ];
            }
        }

        return $this->render('document/index.html.twig', [
            'status' => $status,
        ]);
    }
}
