<?php
namespace LegacyApp\Core\Symfony\Controller;

use LegacyApp\Jadro;
use LegacyApp\Model;
use Symfony\Component\Routing\Annotation\Route;
final class CeninyNewController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * @Route(path="ceniny_new", name="ceniny_new")
     */
    public function __invoke(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('controller/ceniny_new.twig', ['content' => $this->createContent()]);
    }
    private function createContent(): string
    {
        ob_start();
        echo 1;
        $content = (string) ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
