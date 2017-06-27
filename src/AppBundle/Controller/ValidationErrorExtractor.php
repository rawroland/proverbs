<?php
/**
 * Created by PhpStorm.
 * User: awemo
 * Date: 14.02.17
 * Time: 22:47
 */

namespace AppBundle\Controller;


use Symfony\Component\Form\FormInterface;

trait ValidationErrorExtractor
{
    public function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

}