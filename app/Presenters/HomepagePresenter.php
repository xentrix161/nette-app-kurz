<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use App\Model\PostManager;
use Nette\Caching\Storage;
use Nette\Caching\Cache;

final class HomepagePresenter extends Presenter
{
    private PostManager $postManager;
    private Cache $cache;
    private Storage $storage;

    public function __construct(PostManager $pm, Storage $storage)
    {
        $this->postManager = $pm;
        $this->storage = $storage;
        $this->cache = new Cache($storage, 'HomepagePresenter');
    }

    public function renderDefault(): void
    {
//        $value = $this->cache->load('nazdar', function () {
//            bdump('insinde');
//            return 'čau';
//        });
//        bdump($value);
        $this->template->posts = $this->postManager->getPublicPosts(5);
    }

    public function renderTranslate(): void
    {

    }
}
