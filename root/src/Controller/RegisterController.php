<?php

namespace App\Controller;

use App\Model\User;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use Random\RandomException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegisterController extends Controller
{
    private string $template = 'register.twig';

    public function __construct(private readonly UserRepository $userRepository = new UserRepository())
    {

    }
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(): void
    {
        $this->render($this->template);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError|RandomException
     * @throws Exception
     */
    public function register(array $userData): void
    {
        $user = new User(0, new DateTime(), $userData['firstname'], $userData['lastname'], $userData['email'], $userData['username'], $userData['password'], false, $this->generateToken(), false, false, '');
        $user->setPassword(hash('sha256', $userData['password']));
        $sameEmail = $this->userRepository->findOneBy('email', $userData['email']);
        $sameUsername = $this->userRepository->findOneBy('username', $userData['username']);
        $usernameLength = strlen($userData['username']);
        $passwordLength = strlen($userData['password']);
        if(empty($sameEmail) && empty($sameUsername) && $usernameLength > 3 && $passwordLength > 5) {
            $this->userRepository->save($user);
            $whiteListFile = fopen(__DIR__ . "/../../inc/whitelist.json", "r");
            $whiteList = fread($whiteListFile, filesize(__DIR__ . "/../../inc/whitelist.json"));
            $whiteList = json_decode($whiteList, false);
            if(in_array($userData['email'], $whiteList)){
                $this->sendMail($user);
            }
            header('Location: ' . $this->url . '/register/thanks');
        }
        elseif($passwordLength <= 5){
            $this->render($this->template, ['registerError' => true, 'passwordError' => true, 'firstname' => $userData['firstname'], 'lastname' => $userData['lastname'], 'email' => $userData['email'], 'username' => $userData['username']]);
        }
        else{
            $this->render($this->template, ['registerError' => true, 'passwordError' => false, 'firstname' => $userData['firstname'], 'lastname' => $userData['lastname'], 'email' => $userData['email'], 'username' => $userData['username']]);
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function thanks(): void{
        $this->render('thanks.twig', ['register' => true]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function verify(array $token): void
    {
        $token = $token['token'];
        $user = $this->userRepository->findOneBy('token', $token);
        if(!empty($user)) {
            $user->setVerified(1);
            $this->userRepository->save($user);
            $this->render('verified.twig', ['verified' => true]);
        }
        else{
            $this->render('verified.twig', ['verified' => false]);
        }
    }
    /**
     * @throws RandomException
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
    private function sendMail(User $user): void
    {
        $mailText = "<h1>Vielen Dank für Ihre Registrierung</h1>
        <p>Klicken Sie auf den folgenden Link, um ihren Account zu verifizieren:</p>
        <a href='https://wiki.verplant-durch-aventurien.de/register/verify?" . http_build_query(['token'=>$user->getToken()]) . "'>Hier verifizieren</a>
        ";
        $email = $user->getEmail();
        $subject = "Verifizierung Ihres Accounts für Pen and Paper Wiki";
        $header[] = 'MIME-Version: 1.0';
        $header[] = 'Content-type: text/html; charset=utf-8';
        $header[] = "From: PnP Wiki <wiki@verplant-durch-aventurien.de>";
        $header[] = "Reply-To: wiki@verplant-durch-aventurien.de";
        $header[] = "Return-Path: wiki@verplant-durch-aventurien.de";
        mail($email, $subject, $mailText, implode("\r\n", $header));
    }
}