<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    protected $session ;
    protected $productRepository ;

    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session ;
        $this->productRepository = $productRepository ;
    }

    public function add(int $id) 
    {
        // On regarde si le panier existe sinon on le crée
        $cart = $this->session->get('cart', []);

        // On verifie si la key est presente. Si c'est le cas on incrémente la value sinon
        // On ajoute le produit key = id et value = quantité
        if(!empty($cart[$id]))
        {
            $cart[$id]++;
        }
        else
        {
            $cart[$id] = 1;
        }

        // On enregistre ce panier dans la session
        $this->session->set('cart', $cart);
    }

    public function remove(int $id) 
    {
        // On recupere le panier
        $cart = $this->session->get('cart', []) ;

        // On verifie si le produit est bien present et on le supprime
        if(!empty($cart[$id])) 
        {
            // Si le produit à une value supérieur à 1 on la décrémente
            if($cart[$id] > 1)
            {
                $cart[$id]-- ;
            }
            else
            {
                unset($cart[$id]) ;
            }
        }
        $this->session->set('cart', $cart) ;
    }

    public function getCartDetails() : array 
    {
        // On regarde recupere le panier
        $cart = $this->session->get('cart', []);

        // On crée un nouveau panier qui contiendra les details du produit
        $cartDetails = [];

        // On recupere les infos du produit avec son id
        foreach($cart as $id => $quantity)
        {
            $cartDetails[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];
        };

        return $cartDetails ;
    }

    public function getTotal() : float 
    {
        // On creer la variable total
        $totalCart = 0 ;

        // On récupère le resultat de getFullCart
        foreach($this->getCartDetails() as $item)
        {
            // Pour chaque produit on le multiplie par la quantité et on l'ajoute a $totalCart
            $totalCart += $item['product']->getPrice() * $item['quantity'] ;
        }

        return $totalCart ;
    }
}