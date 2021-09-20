<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Application\LinkGenerator;
use Nette\Application\UI\TemplateFactory;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;
use Nette\Utils\DateTime;
use Nette\Database\Explorer;

class PostManager
{
    use SmartObject;
    /**
     * @var Explorer
     */
    private Explorer $db;
    /**
     * @var TemplateFactory
     */
    private TemplateFactory $templateFactory;
    /**
     * @var LinkGenerator
     */
    private LinkGenerator $linkGenerator;
    /**
     * @var MailSender
     */
    private MailSender $mailSender;

    /**
     * PostManager constructor.
     * @param Explorer $db
     * @param TemplateFactory $templateFactory
     * @param LinkGenerator $linkGenerator
     * @param MailSender $mailSender
     */
    public function __construct(Explorer $db, TemplateFactory $templateFactory, LinkGenerator $linkGenerator, MailSender $mailSender)
    {
        $this->db = $db;
        $this->templateFactory = $templateFactory;
        $this->linkGenerator = $linkGenerator;
        $this->mailSender = $mailSender;
    }

    public function getAll(): Selection
    {
        return $this->db->table('post');
    }

    public function getById(int $id): ?ActiveRow
    {
        return $this->getAll()->get($id);
    }

    public function getByIdAndDelete(int $id): bool
    {
        $p = $this->getAll()->get($id)->delete();
        if ($p > 0) {
            return true;
        }
        return false;
    }

    public function isAuthor(int $id, string $author): bool
    {
        $post = $this->getById($id);
        if ($post->author == $author) {
            return true;
        }
        return false;
    }

    public function insert(array $values): ActiveRow
    {
        $retVal = $this->getAll()->insert($values);
            $latte = $this->templateFactory->createTemplate();
            $latte->getLatte()->addProvider('uiControl', $this->linkGenerator);
            $message = $this->mailSender->getMessage();
            $message->setFrom('xentrix@local.sk'); //upravit z databazy
            $message->addTo('filipkosmel@gmail.com'); //upravit z databazy
            $message->setHtmlBody($latte->renderToString(__DIR__ . '/addPostMail.latte', $retVal->toArray()));

            $mailer = $this->mailSender->getSender();
            $mailer->send($message);
        return $retVal;
    }

    public function getPublicPosts(int $limit = null): Selection
    {
        $retVal = $this->getAll()
            ->where('created_at < ', new DateTime)
            ->order('created_at DESC');

        if ($limit) {
            $retVal->limit($limit);
        }
        return $retVal;
    }

}