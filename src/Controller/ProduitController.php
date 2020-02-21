<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="produits")
     */
    public function index(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $imageUpload = $form->get('image')->getData();

            if ($imageUpload) {

                $nomFichier = uniqid() . '.' . $imageUpload->guessExtension();

                try {
                    $imageUpload->move(
                        $this->getParameter('upload_dir'),
                        $nomFichier
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible de déplacer le fichier');
                    echo "Impossible de déplacer le fichier";
                }

                $produit->setPhoto($nomFichier);

            }

            $em->persist($produit);
            $em->flush();

            //$this->addFlash(‘success’, $translator->trans(‘produit . added’));
        }

        $produits = $em->getRepository(Produit::class)->findAll();

        return $this->render('produit/index.html.twig', [
           "produits"=> $produits,
            "addProduits" => $form->createView()
        ]);


    }



    /**
     * @Route("/produit/{id}", name="produit")
     */
    public function produit(Produit $produit=null){

        if($produit != null){

            return $this->render('produit/produit.html.twig', [
                'produit' => $produit,
            ]);
        }
        else{
            $this->addFlash('danger', 'Produit introuvable');
            return $this->redirectToRoute('produits');
        }
    }


    /**
     * @Route("/produit/delete/{id}", name="produit_delete")
     */
    public function delete(Produit $produit=null){

        if($produit != null){

            $em = $this->getDoctrine()->getManager();
            $em->remove($produit);
            $em->flush();

            $this->addFlash('warning', 'Produit supprimée');

        }
        else{
            $this->addFlash('danger', 'Produit introuvable');
        }

        return $this->redirectToRoute('produit');
    }

}


