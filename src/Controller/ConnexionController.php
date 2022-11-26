<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RequestStack;

class ConnexionController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    /**
     * @Route("/inscription", name="inscription_user", methods={"GET", "POST"})
     */
    public function inscriptionUser(Request $request, ManagerRegistry $doctrine): Response
    {

        $errorState = false;
        $errorMessage = "";
        $form = $this->createFormBuilder()
            ->add('email', TextType::class, ['label' => 'Email*',])
            ->add('password', PasswordType::class, [
                'label' => 'Password*',
                'constraints' => new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
                    'message' => 'Le mot de passe doit avoir au minimum 8 charactère et au moins une lettre et un nombre'
                ]),
            ])
            ->add('password2', PasswordType::class, [
                'label' => 'Confirm password*',
                'constraints' => new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
                    'message' => 'Le mot de passe doit avoir au minimum 8 charactère et au moins une lettre et un nombre'
                ]),
            ])
            ->add('pseudo', TextType::class, ['label' => 'Pseudo*',])
            ->add('nom', TextType::class, [
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('prenom', TextType::class, [
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('age', TextType::class, [
                'required'   => false,
                'empty_data' => null,
                'constraints' => new Assert\Regex([
                    'pattern' => '/^[0-9]*$/',
                    'message' => 'Age doit être un nombre'
                ]),
            ])
            ->add('ville', TextType::class, [
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('tel', TextType::class, [
                'required'   => false,
                'empty_data' => null,
            ])

            ->add('save', SubmitType::class, ['label' => 'Inscription'])
            ->getForm();


        $form->handleRequest($request);

        //todo send un email, l'email renvoie vers une page qui créer le user pitet dans url params dans l'email 
        //todo rajouter un capcha
        //todo autocomplession ville

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form["password"]->getData() === $form["password2"]->getData()) {
                $user = new User();
                $user->setEmail($form["email"]->getData())
                    ->setPassword($form["password"]->getData())
                    ->setPseudo($form["pseudo"]->getData())
                    ->setNom($form["nom"]->getData())
                    ->setAge($form["age"]->getData())
                    ->setPrenom($form["prenom"]->getData())
                    ->setVille($form["ville"]->getData())
                    ->setTel($form["tel"]->getData());

                //Add user data to localstorage
                $session = $this->requestStack->getSession();
                $session->set('newUser', $user);

                return $this->redirectToRoute('email_send');
            } else {
                $errorState = true;
                $errorMessage = "Attention les mots de passes renseignés ne sont pas identiques !";
            }
        }

        return $this->render('/connexion/inscription.html.twig', [
            'loginForm' => $form->createView(),
            'erreur' => $errorState,
            'errorMessage' => $errorMessage
        ]);
    }

    /**
     * @Route("/emailsend", name="email_send")
     */
    public function emailSend(): Response
    {
        $session = $this->requestStack->getSession();
        $newUser = $session->get('newUser');
        var_dump($session->get('newUser')->getEmail());
        //faire attention que l'on ai pas de problème si on créer plusieurs user ( faut que ca écrase le précédent ) mais ca le fait peut etre de base en vrai jsp
        return $this->render('/connexion/emailsend.html.twig', [
            'userEmail' => $newUser->getEmail(),
        ]);
    }

    // /**
    //  * @Route("/creation-user", name="email_send")
    //  */
    // public function createUser(): Response
    // {
    //     // $user = new User();
    //     // $user->setEmail("toto@gmail.com")
    //     // ->setPassword("123")
    //     // ->setPseudo("elToto")
    //     // ->setNom("toto")
    //     // ->setAge(22)
    //     // ->setPrenom("tee")
    //     // ->setVille("eee")
    //     // ->setTel("099");

    //     // $entityManager->persist($user);
    //     // $entityManager->flush();

    //     // return new Response('créer pour l'id : '.$user->getId());
    //     return $this->render('/connexion/success.html.twig', [
    //         'userEmail' => 'yes',
    //     ]);
    // }


    // /**
    //  * @Route("/login", name="login_user", methods={"GET", "POST"})
    //  */
    // public function loginUser(ManagerRegistry $doctrine): Response
    // {
    //     $user = $doctrine->getRepository(User::class)->find(1);
    //     if (!$user) {
    //         throw $this->createNotFoundException(
    //             'Pas de user pour id 1'
    //         );
    //     }
    //     return new Response('bien connecter '.$user->getEmail());
    // }

    /**
     * @Route("/discussions", name="discussion")
     */
    public function discussion(): Response
    {
        $discussion = [
            'time' => '2h30',
            'user' => 'Benois',
            'firstLetter' => substr('Benois', 0, 1)
        ];

        return $this->render('/discussion/discussions.html.twig', ['discussion' => $discussion]);
    }
}
