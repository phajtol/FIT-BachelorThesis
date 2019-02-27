<?php

namespace App\Components\Publication;

use Nette\Application\UI\Control;
use Nette\Database\IRow;


class PublicationControl extends Control
{
    /**
     * PublicationControl constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param IRow $pub - row from db table `publication` containing all necessary columns
     * @param array $authors
     */
    public function render(IRow $pub, array $authors): void
    {
        $this->template->setFile(__DIR__ . '/PublicationControl.latte');

        $this->template->publication = $pub;
        $this->template->authors = $authors;

        $this->template->render();
    }
}
