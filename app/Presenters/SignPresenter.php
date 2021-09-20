<?php


namespace App\Presenters;


use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

class SignPresenter extends Presenter
{
    private string $storeRequestId = '';

    public function actionIn(string $storeRequestId = '')
    {
        $this->storeRequestId = $storeRequestId;
    }

    public function actionOut() {
        $this->getUser()->logout(true);
        $this->flashMessage('Odhlásenie prebehlo úspešne!', 'success');
        $this->redirect('Homepage:');
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživateľské meno:')
            ->setRequired('Prosím vyplňte svoje uživateľské meno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte svoje heslo.');

        $form->addSubmit('send', 'Prihlásiť');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->flashMessage('Úspešné prihlásenie!', 'success');
            $this->restoreRequest($this->storeRequestId);
            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné prihlasovacie meno alebo heslo.');
        }
    }
}