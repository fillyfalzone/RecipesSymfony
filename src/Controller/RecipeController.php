<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{

    #[Route('/recettes', name: 'recipe.index')]
    public function index(RecipeRepository $repository, EntityManagerInterface $em): Response
    {
        $recipes = $repository->findAll();

        // Utiliser une method perso du Repo
        // $recipes = $repository->findWithDurationLowerThan(20);

        //setter le title d'une entité
        // $recipes[0]->setTitle('pâtes bolognaise');
        // $em->flush();


        return $this->render('recipe/index.html.twig', [
            "recipes" => $recipes,
        ]);
        
    }
    // Les requirements permet de spécifier le format attendu pour les paramettres de url
    #[Route('/recettes/{slug}-{id}', name: 'recipe.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(string $slug, int $id, RecipeRepository $repository): Response
    {
        // dd($request->attributes->get('slug'), $request->attributes->get('id'));

        // Si on rajoute des paramettres dans la method on peu directement ecrire 
        // dd($slug, $id); 

        $recipe = $repository->find($id);
        // or
        // $recipe = $repository->findOneBy(["slug" => $slug]);

        // si l'utilisateur se trompe dans le slug de l'url je souhaite quand même qu'il soit rediriger sur la bonne page
        if ($recipe->getSlug() != $slug) {
            return $this->redirectToRoute('recipe.show', ['slug' => $recipe->getSlug(), 'id' => $recipe->getId()]);
        }
        

        return $this->render("recipe/show.html.twig",[
            "recipe" => $recipe,
        ]);
        
    }

    //Ajouter une recette

    #[Route('/recettes/add', name: 'recipe.add', methods: ['POST', 'GET'])]
    public function add(EntityManagerInterface $em, Request $request): Response 
    {
        $recipe = new Recipe();

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe->setCreatedAt(new \DateTimeImmutable());
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'La recette a été ajoutée avec success');

            return $this->redirectToRoute('recipe.index');
        }


        return $this->render('recipe/add.html.twig', [
            "form" => $form,
        ]);
    }

    // Editer les recettes 
    #[Route('/recettes/{id}/edit', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em): Response
    {   
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe->setUpdatedAt(new \DateTimeImmutable);
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'La recette a été modifiée avec succès');
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('recipe/edit.html.twig', [
            "recipe" => $recipe,
            "form" => $form,
        ]);
    }

    // Supprimer une recette
    #[Route('/recettes/{id}/delete', name: 'recipe.delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em, Recipe $recipe): Response
    {
        $em->remove($recipe);
        $em->flush();
        $this->addFlash('success', 'La recette a été supprimée avec succès');
        return $this->redirectToRoute('recipe.index');
    }
}
