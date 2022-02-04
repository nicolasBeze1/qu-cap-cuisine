<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\SubCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $this->loadCategories($manager);
        $this->loadSubCategories($manager);
    }

    private function loadCategories(ObjectManager $manager): void
    {
        foreach ($this->getCategoryData() as $name) {
            $category = new Category();
            $category->setTitle($name);

            $manager->persist($category);
            $this->addReference('category-'.$name, $category);
        }

        $manager->flush();
    }

    private function loadSubCategories(ObjectManager $manager): void
    {
        foreach ($this->getSubCategoryData() as [$category, $subCategories]) {
            foreach ($subCategories as $name) {
                $subCategory = new SubCategory();
                $subCategory->setCategory($this->getReference('category-'.$category));
                $subCategory->setTitle($name);

                $manager->persist($subCategory);
            }
        }
        $manager->flush();
    }

    private function getCategoryData(): array
    {
        return [
            'Boucherie',
            'Pêche',
            'Légumerie',
            'Crémerie',
            'Cave',
            'Epicerie',
            'Préparations culinaires',
            'Culture',
            'Personnel',
            'Locaux',
            'Matériel',
            'Cuissons',
            'Préparations de base',
        ];
    }

    private function getSubCategorYData(): array
    {
        return [
            ['Boucherie', [
                'Viandes de boucherie',
                'Porc',
                'Mouton et Agneau',
                'Veau',
                'Boeuf',
                'Abats',
                'Volailles',
                'Gibier',
            ]],
            ['Pêche', [
                'Poissons',
                'Mollusques et crustacés',
            ]],
            ['Légumerie', [
                'Les légumes',
                'Les fruits',
            ]],
            ['Crémerie', [
                'Les corps gras',
                'Les corps gras d\'origine animale',
                'Les corps gras d\'origine végétale',
                'Les produits laitiers',
                'Les oeufs'
            ]],
            ['Cave', [
                "Vins",
                "Alcools"
            ]],
            ['Epicerie', [
                "Les condiments aromates-épices",
                "Les produits semi-élaborés",
                "Les procédés de conservation",
                "L'approvisionnement des services"
            ]],
            ['Préparations culinaires', [
                "Les potages",
                "Les hors-d'oeuvre",
                "Pâtes fraîches, farinages, riz",
                "Desserts, pâtisserie, pâtes de base",
                "Entremets, crèmes, glaces, appareils"
            ]],
            ['Culture', [
                'Histoire de la cuisine',
                'Education du goût et de l\'odorat'
            ]],
            ['Personnel', [
                'Le personnel',
                'La répartition du travail',
                'Tenue et comportement',
                'La sécurité',
            ]],
            ['Locaux', [
                'Les locaux',
                'L\'entretien des locaux',
                'Les déchets',
                'La ventilation',
            ]],
            ['Matériel', [
                'Le matériel fixe de cuisine',
                'Le matériel mobile de cuisine',
                'Les couteaux, le petit outillage',
                'Le matériel électromécanique',
            ]],
            ['Cuissons', [
                'Phénomènes de cuisson',
                'Rôtir - Poêler',
                'Sauter - griller',
                'Frire - cuire sous vide',
                'Pocher - vapeur',
                'Braiser - Ragoût',
            ]],
            ['Préparations de base', [
                'Les fonds de base',
                'Les gelées',
                'Les liaisons',
                'Les sauces de base',
                'Les sauces émulsionnées',
                'Les beurres composés',
                'Les farces et duxelles',
                'Le courts-bouillons et marinades',
            ]],
        ];
    }
}
