<?php

namespace App\Controller;

use App\Model\User;
use App\Repository\UserRepository;
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
     */
    public function register(array $userdata): void
    {
        $user = new User(0, $userdata['firstname'], $userdata['lastname'], $userdata['email'], $userdata['username'], $userdata['password'], false, $this->generateToken(), false, false, '');
        $user->setPassword(hash('sha256', $userdata['password']));
        $sameEmail = $this->userRepository->findOneBy('email', $userdata['email']);
        $sameUsername = $this->userRepository->findOneBy('username', $userdata['username']);
        $usernameLength = strlen($userdata['username']);
        $passwordLength = strlen($userdata['password']);
        if(empty($sameEmail) && empty($sameUsername) && $usernameLength > 3 && $passwordLength > 5) {
            $this->userRepository->save($user);
            $this->userRepository->closeDB();
            $this->sendMail($user);
            header('Location: ' . $this->url . '/register/thanks');
        }
        elseif($passwordLength <= 5){
            $this->render($this->template, ['register_error' => true, 'password_error' => true, 'firstname' => $userdata['firstname'], 'lastname' => $userdata['lastname'], 'email' => $userdata['email'], 'username' => $userdata['username']]);
        }
        else{
            $this->render($this->template, ['register_error' => true, 'password_error' => false, 'firstname' => $userdata['firstname'], 'lastname' => $userdata['lastname'], 'email' => $userdata['email'], 'username' => $userdata['username']]);
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
        $this->userRepository->closeDB();
    }
    public function test(): void
    {
        $this->userRepository->findOneBy("username", 'gamerkater');
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