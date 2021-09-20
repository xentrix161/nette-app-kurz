<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use App\Model\PostManager;
use App\Model\CommentManager;

class PostPresenter extends Presenter
{
    private PostManager $postManager;
    private CommentManager $commentManager;

    public function __construct(PostManager $pm, CommentManager $cm)
    {
        $this->postManager = $pm;
        $this->commentManager = $cm;
    }

    public function loginAuth()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Pre túto akciu je nutné sa prihlásiť!', 'error');
            $this->redirect('Sign:in', $this->storeRequest());
        }
    }

    public function handleDelete(int $postId)
    {
        $this->loginAuth();
        $isAuthor = $this->postManager->isAuthor($postId, $this->getUser()->getId());
        if (!$isAuthor) {
            $this->flashMessage('Nie ste autorom, nemôžete vymazať príspevok!', 'error');
            $this->redirect('Post:show', $postId);
        }

        $this->postManager->getByIdAndDelete($postId);
        $this->flashMessage("Príspevok bol zmazaný");
        $this->redirect('Homepage:default');
    }

    public function actionManipulate(int $postId = 0): void
    {
        $this->canonicalize('Post:manipulate', ['postId' => $postId]);
        $this->loginAuth();

        if ($postId == 0) {
            return;
        }

        $isAuthor = $this->postManager->isAuthor($postId, $this->getUser()->getId());
        if (!$isAuthor) {
            $this->flashMessage('Nie ste autorom, nemôžete upraviť príspevok!', 'error');
            $this->redirect('Post:show', $postId);
        }

        $post = $this->postManager->getById($postId);
        if (!$post) {
            $this->error('Príspevok nebol nájdený');
        }
        $this['postForm']->setDefaults($post->toArray());
    }

    public function renderManipulate(int $postId = 0)
    {
        $this->template->postId = $postId;
    }

    public function renderShow(int $postId): void
    {
        $post = $this->postManager->getById($postId);
        if (!$post) {
            $this->error('Post s týmto ID nebol nájdený!', 404);
        }
        $this->template->post = $post;
        $this->template->comments = $this->commentManager->getCommentsByPostId($postId);
    }

    //COMMENT FORM
    protected function createComponentCommentForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Meno:')
            ->setRequired();
        $form->addEmail('email', 'E-mail:');
        $form->addTextArea('content', 'Komentár:')
            ->setRequired();
        $form->addSubmit('send', 'Publikovať komentár');
        $form->onSuccess[] = [$this, 'commentFormSucceeded'];
        return $form;
    }

    //POST FORM
    protected function createComponentPostForm(): Form
    {
        $form = new Form;
        $form->addText('title', 'Titulok:')
            ->setRequired();
        $form->addTextArea('content', 'Obsah:')
            ->setRequired();
        $form->addSubmit('send', 'Uložit a publikovať');
        $form->onSuccess[] = [$this, 'postFormSucceeded'];
        return $form;
    }

    public function commentFormSucceeded(\stdClass $values): void
    {
        $postId = $this->getParameter('postId');

        $this->commentManager->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ]);

        $this->flashMessage('Ďakujem za komentár', 'success');
        $this->redirect('this');
    }

    public function postFormSucceeded(array $values): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pre vytvorenie alebo editáciu príspevku sa musíte prihlásiť.');
        }

        $postId = $this->getParameter('postId');

        if ($postId) {
            $post = $this->postManager->getById((int)$postId);
            $post->update($values);
            $this->flashMessage('Príspevok bol úspešne upravený.', 'success');
        } else {
            $values['author'] = $this->getUser()->getId();
            $post = $this->postManager->insert($values);
            $this->flashMessage('Príspevok bol úspešne publikovaný.', 'success');
        }
        $this->redirect('Post:show', $post->id);
    }
}